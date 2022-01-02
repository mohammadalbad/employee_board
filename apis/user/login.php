<?php

header('Content-Type: application/json; charset=utf-8');

require_once $_SERVER['DOCUMENT_ROOT'] . "/employee_board/helpers/BasicAuthenticationHelper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/employee_board/helpers/JWTAuthorizationHelper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/employee_board/helpers/DatabaseHelper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/employee_board/models/UserModel.php";

$response = array();

// check auth
$basicAuthenticationHelper = new BasicAuthenticationHelper();
$basicAuthenticationHelper->isAuthenticated();

// check required params is set and not empty
if (
    isset($_POST['email_address'])
    && isset($_POST['password'])
    && !empty($_POST['email_address'])
    && !empty($_POST['password'])
) {

    $email_address = $_POST['email_address'];
    $password = $_POST['password'];

    // create database connection and user object 
    $databaseHelper = new DatabaseHelper();
    $db_connection = $databaseHelper->connect();
    $user = new UserModel($db_connection);

    $isAccountRegistered = $user->isAccountRegistered($email_address);
    if ($isAccountRegistered) {

        $userPassword = $user->getUserPassword($email_address);

        // prepare hashed password
        $passwordHashHelper = new PasswordHashHelper();
        $salt = $passwordHashHelper->getHashedPasswordSalt($userPassword['password']);

        $hashedPassword = $passwordHashHelper->hashPassword($salt, $password);
        $concatedSaltAndHashedPassword = $passwordHashHelper->concateSaltAndPassword($salt, $hashedPassword);

        if ($userPassword['password'] == $concatedSaltAndHashedPassword) {

            $jWTAuthorizationHelper = new JWTAuthorizationHelper();

            $userDetails = $user->getUserDetails($email_address, $userPassword['password']);
            $id = $userDetails['id'];
            $role_id = $userDetails['role_id'];

            $payload = array(
                'id' => $id,
                'role_id' => $role_id,
            );
            $generatedToken = $jWTAuthorizationHelper->generateToken($payload);

            $user->setEmailAddress($email_address);
            $user->setjwtToken($generatedToken);

            $updateTokenResult = $user->updateToken($user);
            if ($updateTokenResult == 200) {

                $userDetails = $user->getUserDetails($email_address, $userPassword['password']);

                $response['message'] = 'user updated successfully';
                $response['date'] = $userDetails;
            } else if ($updateTokenResult == 400) {
                $response['message'] = 'failed, bad request';
            } else if ($updateTokenResult == 404) {
                $response['message'] = 'failed, account is not registered';
            }

            http_response_code($updateTokenResult);
            echo json_encode($response);
        } else {

            $response['message'] = 'failed, wrong password';
            http_response_code(401);
            echo json_encode($response);
        }
    } else {
        $response['message'] = 'failed, account is not registered';
        http_response_code(404);
        echo json_encode($response);
    }
} else {
    $response['message'] = 'failed, bad request missing required parameters';
    http_response_code(400);
    echo json_encode($response);
}
