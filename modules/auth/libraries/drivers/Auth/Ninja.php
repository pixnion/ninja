<?php
class Auth_Ninja_Driver extends Auth_ORM_Driver
{
	public function login($user, $password, $remember)
	{
		if (empty($user) || empty($password))
			return false;

		if (!is_object($user)) {
			$username = $user;
			$user = ORM::factory('user')->where('username', $username)->find();
		} else {
			$username = $user->username;
		}

		if (ninja_auth::valid_password($password, $user->password, $user->password_algo) === true) {
			$this->complete_login($user);
			return true;
		}

		return false;
	}
}
