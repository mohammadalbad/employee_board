<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/employee_board/helpers/PasswordHashHelper.php";

class UserModel
{

    // database connection
    private $dbConnection;

    // user information
    private $id;
    private $firstName;
    private $middleName;
    private $familyName;
    private $birthdate;
    private $gender;
    private $emailAddress;
    private $countryCode;
    private $phoneNumber;
    private $password;
    private $jwt_token;
    private $roleId;
    private $status;

    // user table
    public $table_name = "user";
    // TODO(declare the table cols name here to use in the functions);


    // construct to init database connection
    public function __construct($db)
    {
        $this->dbConnection = $db;
    }

    // setter and getter functions
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;
    }

    public function getMiddleName()
    {
        return $this->middleName;
    }

    public function setFamilyName($familyName)
    {
        $this->familyName = $familyName;
    }

    public function getFamilyName()
    {
        return $this->familyName;
    }

    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;
    }

    public function getBirthdate()
    {
        return $this->birthdate;
    }

    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    public function getGender()
    {
        return $this->gender;
    }

    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    public function getCountryCode()
    {
        return $this->countryCode;
    }

    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setjwtToken($jwt_token)
    {
        $this->jwt_token = $jwt_token;
    }

    public function getjwtToken()
    {
        return $this->jwt_token;
    }

    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;
    }

    public function getRoleId()
    {
        return $this->roleId;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    // check if the account is already exist or not using email address
    function isAccountRegistered($emailAddress)
    {
        $sql = "SELECT email_address FROM " . $this->table_name . " WHERE email_address = ?";
        $prep_state = $this->dbConnection->prepare($sql);
        if (!$prep_state) {
            echo "\nPDO::errorInfo():\n" . $this->dbConnection->errorInfo();
        }
        $prep_state->bindParam(1, $emailAddress);
        $prep_state->execute();
        if ($prep_state->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    // return array of user role permissions
    public function getUserPermissions($user_role_id)
    {
        $sql = "SELECT permission 
        FROM role_permission
        JOIN permission ON permission.id = role_permission.permission_id
        WHERE role_permission.role_id = ?";
        $prep_state = $this->dbConnection->prepare($sql);
        if (!$prep_state) {
            echo "\nPDO::errorInfo():\n" . $this->dbConnection->errorInfo();
        }
        $prep_state->bindParam(1, $user_role_id);
        $prep_state->execute();
        $result = $prep_state->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    // create new user 
    public function createUser($user)
    {

        // check user if exist
        $emailAddress = $user->getEmailAddress();
        $isAccountRegistered = $this->isAccountRegistered($emailAddress);

        if (!$isAccountRegistered) {
            $sql = "INSERT INTO " . $this->table_name . " SET first_name = ?, middle_name = ?, family_name = ?, birthdate = ?, gender = ?, email_address = ?, country_code = ?, phone_number = ?, password = ?, role_id = ?, status = ?, created_at = ?, updated_at = ?";
            $prep_state = $this->dbConnection->prepare($sql);
            if (!$prep_state) {
                echo "\nPDO::errorInfo():\n" . $this->dbConnection->errorInfo();
            }

            // prepare the user data 

            $date = date("Y-m-d H:i:s");

            $first_name = $user->getFirstName();
            $middle_name = $user->getMiddleName();
            $family_name = $user->getFamilyName();
            $birthdate = $user->getBirthdate();
            $gender = $user->getGender();
            $email_address = $user->getEmailAddress();
            $country_code = $user->getCountryCode();
            $phone_number = $user->getPhoneNumber();
            $password = $user->getPassword();
            $role_id = $user->getRoleId();
            $status = $user->getStatus();

            // prepare hashed password
            $passwordHashHelper = new PasswordHashHelper();
            $salt = $passwordHashHelper->generateSalt();
            $hashedPassword = $passwordHashHelper->hashPassword($salt, $password);
            $concatedSaltAndHashedPassword = $passwordHashHelper->concateSaltAndPassword($salt, $hashedPassword);

            // bind the data 
            $prep_state->bindParam(1, $first_name);
            $prep_state->bindParam(2, $middle_name);
            $prep_state->bindParam(3, $family_name);
            $prep_state->bindParam(4, $birthdate);
            $prep_state->bindParam(5, $gender);
            $prep_state->bindParam(6, $email_address);
            $prep_state->bindParam(7, $country_code);
            $prep_state->bindParam(8, $phone_number);
            $prep_state->bindParam(9, $concatedSaltAndHashedPassword);
            $prep_state->bindParam(10, $role_id);
            $prep_state->bindParam(11, $status);
            $prep_state->bindParam(12, $date);
            $prep_state->bindParam(13, $date);

            // execute and return the result
            if ($prep_state->execute()) {
                // success, created
                return 201;
            } else {
                // failed, bad request
                return 400;
            }
        } else if ($isAccountRegistered) {
            // failed, confilct
            return 409;
        }
    }

    // Deactivate user account
    public function deactivateUserAccount($user)
    {
        // check user if exist
        $emailAddress = $user->getEmailAddress();
        $isAccountRegistered = $this->isAccountRegistered($emailAddress);
        if ($isAccountRegistered) {

            $sql = "UPDATE " . $this->table_name . " SET status = 'inactive', updated_at = ? WHERE email_address = ?";
            $prep_state = $this->dbConnection->prepare($sql);
            if (!$prep_state) {
                echo "\nPDO::errorInfo():\n" . $this->dbConnection->errorInfo();
            }

            $date = date("Y-m-d H:i:s");

            $prep_state->bindParam(1, $date);
            $prep_state->bindParam(2, $emailAddress);

            $prep_state->execute();

            if ($prep_state->rowCount() > 0) {
                // success, ok
                return 200;
            } else {
                // failed, bad request
                return 400;
            }
        } else if (!$isAccountRegistered) {
            // failed, not registered
            return 404;
        }
    }

    // update user 
    public function updateUser($user)
    {
        // check user if exist
        $emailAddress = $user->getEmailAddress();
        $isAccountRegistered = $this->isAccountRegistered($emailAddress);

        if ($isAccountRegistered) {
            $sql = "UPDATE " . $this->table_name . " SET first_name = ?, middle_name = ?, family_name = ?, birthdate = ?, gender = ?, country_code = ?, phone_number = ?, password = ?, role_id = ?, updated_at = ?";
            $prep_state = $this->dbConnection->prepare($sql);
            if (!$prep_state) {
                echo "\nPDO::errorInfo():\n" . $this->dbConnection->errorInfo();
            }

            // prepare the user data 

            $date = date("Y-m-d H:i:s");

            $first_name = $user->getFirstName();
            $middle_name = $user->getMiddleName();
            $family_name = $user->getFamilyName();
            $birthdate = $user->getBirthdate();
            $gender = $user->getGender();
            $email_address = $user->getEmailAddress();
            $country_code = $user->getCountryCode();
            $phone_number = $user->getPhoneNumber();
            $password = $user->getPassword();
            $role_id = $user->getRoleId();

            // prepare hashed password
            $passwordHashHelper = new PasswordHashHelper();
            $salt = $passwordHashHelper->generateSalt();
            $hashedPassword = $passwordHashHelper->hashPassword($salt, $password);
            $concatedSaltAndHashedPassword = $passwordHashHelper->concateSaltAndPassword($salt, $hashedPassword);

            // bind the data 
            $prep_state->bindParam(1, $first_name);
            $prep_state->bindParam(2, $middle_name);
            $prep_state->bindParam(3, $family_name);
            $prep_state->bindParam(4, $birthdate);
            $prep_state->bindParam(5, $gender);
            $prep_state->bindParam(6, $email_address);
            $prep_state->bindParam(7, $country_code);
            $prep_state->bindParam(8, $phone_number);
            $prep_state->bindParam(9, $concatedSaltAndHashedPassword);
            $prep_state->bindParam(10, $role_id);
            $prep_state->bindParam(11, $date);

            // execute and return the result
            if ($prep_state->execute()) {
                // success, updated
                return 200;
            } else {
                // failed, bad request
                return 400;
            }
        } else if (!$isAccountRegistered) {
            // failed, not registered
            return 404;
        }
    }

    // return array of employee users
    public function getEmployeeUsers()
    {
        $sql = "SELECT `user`.`id`, `user`.`first_name`, `user`.`middle_name`, `user`.`family_name`, `user`.`birthdate`, `user`.`gender`, `user`.`email_address`,`user`.`country_code`,`user`.`phone_number`,`user`.`jwt_token`
        FROM `role`
        JOIN `user` ON `user`.`role_id`=`role`.`id`
        WHERE `role`.`title`='Employee' AND `user`.`status`='active'";
        $prep_state = $this->dbConnection->prepare($sql);
        if (!$prep_state) {
            echo "\nPDO::errorInfo():\n" . $this->dbConnection->errorInfo();
        }
        $prep_state->execute();
        $result = $prep_state->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }


    // return user email and password
    public function getUserPassword($emailAddress)
    {
        $sql = "SELECT `user`.`password`
         FROM `user`
         WHERE `user`.`email_address` = ?";
        $prep_state = $this->dbConnection->prepare($sql);
        if (!$prep_state) {
            echo "\nPDO::errorInfo():\n" . $this->dbConnection->errorInfo();
        }
        $prep_state->bindParam(1, $emailAddress);
        $prep_state->execute();
        $result = $prep_state->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    // return user details 
    public function getUserDetails($emailAddress, $password)
    {
        $sql = "SELECT `user`.`id`, `user`.`first_name`, `user`.`middle_name`, `user`.`family_name`, `user`.`birthdate`, `user`.`gender`, `user`.`email_address`,`user`.`country_code`,`user`.`phone_number`,`user`.`jwt_token`, `user`.`role_id`
           FROM `user`
           WHERE `user`.`email_address`= ? AND `user`.`password`= ?";

        $prep_state = $this->dbConnection->prepare($sql);
        if (!$prep_state) {
            echo "\nPDO::errorInfo():\n" . $this->dbConnection->errorInfo();
        }
        $prep_state->bindParam(1, $emailAddress);
        $prep_state->bindParam(2, $password);

        $prep_state->execute();
        $result = $prep_state->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public function updateToken($user)
    {
        // check user if exist
        $emailAddress = $user->getEmailAddress();
        $isAccountRegistered = $this->isAccountRegistered($emailAddress);

        if ($isAccountRegistered) {
            $sql = "UPDATE " . $this->table_name . " SET jwt_token = ?, updated_at = ?" . "WHERE `user`.`email_address`= ?";
            $prep_state = $this->dbConnection->prepare($sql);
            if (!$prep_state) {
                echo "\nPDO::errorInfo():\n" . $this->dbConnection->errorInfo();
            }

            // prepare the user data 
            $jwt_token = $user->getjwtToken();
            $date = date("Y-m-d H:i:s");

            // bind the data 
            $prep_state->bindParam(1, $jwt_token);
            $prep_state->bindParam(2, $date);
            $prep_state->bindParam(3, $emailAddress);

            // execute and return the result
            if ($prep_state->execute()) {
                // success, updated
                return 200;
            } else {
                // failed, bad request
                return 400;
            }
        } else if (!$isAccountRegistered) {
            // failed, not registered
            return 404;
        }
    }
}
