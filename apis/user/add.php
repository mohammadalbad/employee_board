<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/employee_board/helpers/BasicAuthenticationHelper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/employee_board/helpers/JWTAuthorizationHelper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/employee_board/helpers/DatabaseHelper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/employee_board/helpers/PermissionsHelper.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/employee_board/models/UserModel.php";

$response = array();

// check auth
$basicAuthenticationHelper = new BasicAuthenticationHelper();
$basicAuthenticationHelper->isAuthenticated();

// check required params is set and not empty
if (
    isset($_POST['first_name'])
    && isset($_POST['middle_name'])
    && isset($_POST['family_name'])
    && isset($_POST['birthdate'])
    && isset($_POST['gender'])
    && isset($_POST['email_address'])
    && isset($_POST['country_code'])
    && isset($_POST['phone_number'])
    && isset($_POST['password'])
    && isset($_POST['role_id'])
    && isset($_POST['status'])
    && !empty($_POST['first_name'])
    && !empty($_POST['middle_name'])
    && !empty($_POST['family_name'])
    && !empty($_POST['birthdate'])
    && !empty($_POST['gender'])
    && !empty($_POST['email_address'])
    && !empty($_POST['country_code'])
    && !empty($_POST['phone_number'])
    && !empty($_POST['password'])
    && !empty($_POST['role_id'])
    && !empty($_POST['status'])
) {

    // validate user jwt token
    $jWTAuthorizationHelper = new JWTAuthorizationHelper();
    $validateToken = $jWTAuthorizationHelper->validateToken();

    if (isset($validateToken['status']) && $validateToken['status']) {

        // token validated successfully and get the required user role id from it to check user permissions
        $tokenData = $validateToken['data'];
        $role_id = $userDetails['role_id'];

        // create database connection and user object 
        $databaseHelper = new DatabaseHelper();
        $db_connection = $databaseHelper->connect();
        $user = new UserModel($db_connection);

        // check user permissions can add new employee 
        $permissionsHelper = new PermissionsHelper();
        // get user permisions
        $userPermissions = $user->getUserPermissions($user_role_id);

        // check if user have the permsision or not 
        $isUserAddEmployeesPermissionsGranted = $permissionsHelper->isUserAddEmployeesPermissionsGranted($userPermissions);
        if ($isUserAddEmployeesPermissionsGranted) {

            $first_name = $_POST['first_name'];
            $middle_name = $_POST['middle_name'];
            $family_name = $_POST['family_name'];
            $birthdate = $_POST['birthdate'];
            $gender = $_POST['gender'];
            $email_address = $_POST['email_address'];
            $country_code = $_POST['country_code'];
            $phone_number = $_POST['phone_number'];
            $password = $_POST['password'];
            $roleId = $_POST['role_id'];
            $status = $_POST['status'];

            $user->setFirstName($first_name);
            $user->setMiddleName($middle_name);
            $user->setFamilyName($family_name);
            $user->setBirthdate($birthdate);
            $user->setGender($gender);
            $user->setEmailAddress($email_address);
            $user->setCountryCode($country_code);
            $user->setPhoneNumber($phone_number);
            $user->setPassword($password);
            $user->setRoleId($roleId);
            $user->setStatus($status);

            $createUserResult = $user->createUser($user);

            if ($createUserResult == 201) {
                $response['message'] = 'user created successfully';
            } else if ($createUserResult == 400) {
                $response['message'] = 'failed, bad request';
            } else if ($createUserResult == 409) {
                $response['message'] = 'failed, account is already register';
            }
            http_response_code($createUserResult);
            echo json_encode($response);
        } else {
            $response['message'] = "forbidden, You don't have add employees permission";
            http_response_code(403);
            echo json_encode($response);
        }
    } else {
        $response['message'] = $validateToken['message'];
        http_response_code(400);
        echo json_encode($response);
    }
} else {
    $response['message'] = 'failed, bad request missing required parameters';
    http_response_code(400);
    echo json_encode($response);
}
