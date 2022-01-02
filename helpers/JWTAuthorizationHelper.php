<?php

require_once $_SERVER['DOCUMENT_ROOT']."/employee_board/config/jwt.php";
require_once $_SERVER['DOCUMENT_ROOT']."/employee_board/libraries/firebase-php-jwt/BeforeValidException.php";
require_once $_SERVER['DOCUMENT_ROOT']."/employee_board/libraries/firebase-php-jwt/ExpiredException.php";
require_once $_SERVER['DOCUMENT_ROOT']."/employee_board/libraries/firebase-php-jwt/JWK.php";
require_once $_SERVER['DOCUMENT_ROOT']."/employee_board/libraries/firebase-php-jwt/JWT.php";
require_once $_SERVER['DOCUMENT_ROOT']."/employee_board/libraries/firebase-php-jwt/Key.php";
require_once $_SERVER['DOCUMENT_ROOT']."/employee_board/libraries/firebase-php-jwt/SignatureInvalidException.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTAuthorizationHelper
{

    public function generateToken($payload)
    {

        if (!empty($payload) && is_array($payload)) {
            $payload['API_TIME'] = time();
            try {
                $jwt = JWT::encode($payload, JWT_KEY, JWT_ALGORITHM);
                return $jwt;
            } catch (Exception $e) {
                return 'Message: ' . $e->getMessage();
            }
        } else {
            return "error params must be an array";
        }
    }

    public function validateToken()
    {
        $headers = getallheaders();
        $token_data = $this->isTokenExist($headers);
        if ($token_data['status']) {
            try {
                try {
                    $token_decode = JWT::decode($token_data['token'], new Key(JWT_KEY, 'HS256'));
                } catch (Exception $e) {
                    return ['status' => false, 'message' => $e->getMessage()];
                }
                if (!empty($token_decode) && is_object($token_decode)) {
                    if (empty($token_decode->API_TIME || !is_numeric($token_decode->API_TIME))) {
                        return ['status' => false, 'message' => 'Token Time Not Define'];
                    } else {
                        $time_difference = strtotime('now') - $token_decode->API_TIME;
                        if ($time_difference >= JWT_TOKEN_EXPIRE_TIME) {
                            return ['status' => false, 'message' => 'Token Time Expire'];
                        } else {
                            return ['status' => true, 'data' => $token_decode];
                        }
                    }
                } else {
                    return ['status' => false, 'message' => 'Forbidden'];
                }
            } catch (Exception $e) {
                return ['status' => false, 'message' => $e->getMessage()];
            }
        } else {
            return ['status' => false, 'message' => $token_data['message']];
        }
    }

    public function isTokenExist($headers)
    {
        if (!empty($headers) && is_array($headers)) {
            foreach ($headers as $header_name => $header_value) {
                if (strtolower(trim($header_name)) == strtolower(trim(JWT_TOKEN_HEADER)))
                    return ['status' => true, 'token' => $header_value];
            }
        }
        return ['status' => false, 'message' => 'Token is not defined'];
    }
}
