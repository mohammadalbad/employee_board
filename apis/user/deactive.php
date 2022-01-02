<?php

header('Content-Type: application/json; charset=utf-8');

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
    isset($_POST['email_address'])
    && !empty($_POST['email_address'])
) {

    // validate user jwt token
    $jWTAuthorizationHelper = new JWTAuthorizationHelper();
    $validateToken = $jWTAuthorizationHelper->validateToken();

    if (isset($validateToken['status']) && $validateToken['status']) {

        // token validated successfully and get the required user role id from it to check user permissions
        $tokenData = $validateToken['data'];
        $user_role_id = $tokenData->role_id;

        // create database connection and user object 
        $databaseHelper = new DatabaseHelper();
        $db_connection = $databaseHelper->connect();
        $user = new UserModel($db_connection);

        // check user permissions can deactivate an employee
        $permissionsHelper = new PermissionsHelper();
        // get user permisions
        $userPermissions = $user->getUserPermissions($user_role_id);
        // check if user have the permsision or not 
        $isUserDeactivateAnEmployeePermissionsGranted = $permissionsHelper->isUserDeactivateAnEmployeePermissionsGranted($userPermissions);
        if ($isUserDeactivateAnEmployeePermissionsGranted) {

            $email_address = $_POST['email_address'];
            $user->setEmailAddress($email_address);
            $deactivateUserAccountResult = $user->deactivateUserAccount($user);

            if ($deactivateUserAccountResult == 200) {
                $response['message'] = 'user deactivated successfully';
            } else if ($deactivateUserAccountResult == 400) {
                $response['message'] = 'failed, bad request';
            }
            http_response_code($deactivateUserAccountResult);
            echo json_encode($response);
        } else {
            $response['message'] = "forbidden, You don't have deactivate an employee permission";
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
