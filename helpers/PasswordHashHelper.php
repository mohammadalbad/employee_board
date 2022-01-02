<?php

class PasswordHashHelper
{

	public function generateSalt()
	{
		return substr(md5(uniqid(rand(), true)), 0, 17);
	}

	public function hashPassword($salt, $password)
	{
		$hashedPassword = hash("sha256", $password . $salt);
		$saltAndHashedPassword = $salt . $hashedPassword;
		return $saltAndHashedPassword;
	}

	public function concateSaltAndPassword($salt, $hashedPassword)
	{
		$saltAndHashedPassword = $salt . $hashedPassword;
		return $saltAndHashedPassword;
	}

	public function getHashedPasswordSalt($password)
	{
		return substr($password, 0, 17);
	}

	public function verifyPassword($salt, $userPassword, $enteredPassword)
	{
		$enteredPasswordHash = $this->hashPassword($salt, $enteredPassword);
		$hashedPasswordWithSalt = $this->concateSaltAndPassword($salt, $enteredPasswordHash);
		if ($hashedPasswordWithSalt == $userPassword) {
			return true;
		} else {
			return false;
		}
	}

	
}
