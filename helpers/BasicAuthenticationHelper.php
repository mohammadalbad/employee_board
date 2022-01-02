<?php

require_once $_SERVER['DOCUMENT_ROOT']."/employee_board/config/auth.php";

class BasicAuthenticationHelper
{

	public function isAuthenticated()
	{
		$has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
		$is_not_authenticated = (!$has_supplied_credentials
			|| $_SERVER['PHP_AUTH_USER'] != BASIC_AUTH_USERNAME
			|| $_SERVER['PHP_AUTH_PW'] != BASIC_AUTH_PASSWORD
		);

		if ($is_not_authenticated) {
			$response['message'] = "Unauthorized";
			http_response_code(401);
			echo json_encode($response);
			exit;
		}

	}
}
