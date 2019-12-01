<?php

/**
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

/**
* User model.
* Provides functions for the manipulation of User data.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
*/

include_once '../app/core/DB.php';

class UserModel
{
	
	/**
	* Create a password hash from a given password.
	* @param $password The password to hash.
	* @returns The hashed value.
	*/
	private function createHash($password) 
	{
	
		//Double-check nulls are not in password string. They can cause big problems with password hashing,
		//as the hash will terminate at the first null, which may create a much weaker password hash than expected.
		$password = str_replace("\0", "", $password);
	
		if (mb_strlen($password) > 128) App::error("Password too long.");
	
		//Hash the password with a SPECIFIED hash method. Not specifying a method here will use the defauly which \
		//is dangerous in case the default changes in a later version of PHP. This would render old accounts inaccessible.
		$hash = password_hash($password, PASSWORD_BCRYPT);
		
		if (!$hash) App::error("ERROR: Invalid hash returned by createHash()");
		
		return $hash;
	}
	
	/**
	* Set a user account's "playing" status string.
	* @param $user_id The user ID to use.
	* @param $gamename The game name string to use.
	* @returns void.
	*/
	public function setPlaying($user_id, $gamename)
	{
		if (mb_strlen($gamename) > 128) App::error("Game name too long.");
		
		//Sanitize.
		$user_id = $user_id * 1;
		
		$DB = new DB();
		
		//Mark user deleted by id.
		$DB->query("update users set playing = $1 where user_id = $2", $gamename, $user_id);
	}
	
	/**
	* Mark an account deleted.
	* @param $user_id The user ID to use.
	* @returns void.
	*/
	public function del($user_id)
	{
		//Sanitize.
		$user_id = $user_id * 1;
		
		$DB = new DB();
		
		//Mark user deleted by id.
		$DB->query("update users set deleted = 't' where user_id = $1", $user_id);
	}
	
	/**
	* Undelete an account.
	* @param $user_id The user ID to use.
	* @returns void.
	*/
	public function undel($user_id)
	{
		//Sanitize.
		$user_id = $user_id * 1;
		
		$DB = new DB();
		
		//Mark user deleted by id.
		$DB->query("update users set deleted = 'f' where user_id = $1", $user_id);
	}
	
	/**
	* Edit user profile details.
	* @param $user_id The user ID of the account to edit.
	* @param $username The new user ID for the account.
	* @param $password The new password for this account. Optional. Leave blank for no change.
	* @param $email The new email for this account.
	* @param $steam The new steam account name for this account.
	* @param $twitch The new twitch account name for this account.
	* @param $bio The new bio text for this account.
	* @returns Returns "OK" on successful update, or error codes on failure.
	*/
	public function editUser($user_id, $username, $password, $email, $steam, $twitch, $bio)
	{
		
		if (mb_strlen($username) > 16) App::error("Username too long.");
		if (mb_strlen($password) > 128) App::error("Password too long.");
		if (mb_strlen($email) > 128) App::error("Email too long.");
		if (mb_strlen($steam) > 128) App::error("Steam name too long.");
		if (mb_strlen($twitch) > 128) App::error("Twitch name too long.");
		if (mb_strlen($bio) > 128) App::error("Bio too long.");
		
		//Trim email of whitespace.
		$email = trim($email);
		
		//Usernames may only be alphanumeric.
		if (!$username || !preg_match('/^[a-zA-Z0-9]+$/', $username)) return "invalid_username";
		
		//Usernames must be at least 3 characters long.
		if (mb_strlen($username) < 3) return "invalid_username";
				
		//Password must be at least 8 characters long.
		if ($password && mb_strlen($password) < 8) return "invalid_password";
		
		if (mb_strlen($password) > 128) App::error("Password too long.");
		
		//Email must be a valid email address.
		if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) return "invalid_email";
		
		$DB = new DB();
		
		//Check if username already exists.
		$existingUser = $DB->query("select username from users where username ilike $1 and user_id != $2", $username, $_SESSION['user_id']);
		if (strtolower($username) == strtolower($existingUser[0]['username'])) return "username_used";
		
		//Check if email already used.
		$existingEmail = $DB->query("select email from users where email ilike $1 and user_id != $2", $email, $_SESSION['user_id']);
		if (strtolower($email) == strtolower($existingEmail[0]['email'])) return "email_used";
		
		if ($password)
		{
			
			$password_hash = $this->createHash($password);

			//...when new password is chosen.
			$DB->query("update users set username = $2, password_hash = $3, email = $4, steam = $5, twitch = $6, bio = $7 where user_id = $1", $_SESSION['user_id'], $username, $password_hash, $email, $steam, $twitch, $bio);
		}
		else
		{
			//...when password is to stay the same.
			$DB->query("update users set username = $2, email = $3, steam = $4, twitch = $5, bio = $6 where user_id = $1", $_SESSION['user_id'], $username, $email, $steam, $twitch, $bio);
		}
		
		return "OK";
	}
	
	
	/**
	* Create user.
	* @param $username The new user ID for the account.
	* @param $password The new password for this account.
	* @param $email The new email for this account.
	* @returns Returns "OK" on successful account creation, or error codes on failure.
	*/
	public function createUser($username, $password, $email)
	{
		
		if (mb_strlen($username) > 16) App::error("Username too long.");
		if (mb_strlen($password) > 128) App::error("Password too long.");
		if (mb_strlen($email) > 128) App::error("Email too long.");
		
		//Trim email of whitespace.
		$email = trim($email);
		
		//Usernames may only be alphanumeric.
		if (!$username || !preg_match('/^[a-zA-Z0-9]+$/', $username)) return "invalid_username";
		
		//Usernames must be at least 3 characters long.
		if (mb_strlen($username) < 3) return "invalid_username";
				
		//Password must be at least 8 characters long.
		if (mb_strlen($password) < 8) return "invalid_password";
		
		//Email must be a valid email address.
		if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) return "invalid_email";
		
		$DB = new DB();
		
		//Check if username already exists.
		$existingUser = $DB->query("select username from users where username ilike $1", $username);
		if (strtolower($username) == strtolower($existingUser[0]['username'])) return "username_used";
		
		//Check if email already used.
		$existingEmail = $DB->query("select email from users where email ilike $1", $email);
		if (strtolower($email) == strtolower($existingEmail[0]['email'])) return "email_used";
		
		$password_hash = $this->createHash($password);
		
		//Get next user id.
		$arr = $DB->query("select nextval('users_user_id_seq'::regclass)");
		$next_id = $arr[0]['nextval'];
		
		if (!is_numeric($next_id) || $next_id * 1 < 1) App::error("Cannot get next available user id from database when creating user.");
		
		//Insert new user.
		$DB->query("insert into users (user_id, username, password_hash, email) values($1, $2, $3, $4)", $next_id, $username, $password_hash, $email);
		
		return "OK";
	}
	
	/**
	* Set an account password.
	* @param $user_id The user ID of the account to change password for.
	* @param $password The new password for this account.
	* @returns Returns "OK" on successful update.
	*/
	public function setPassword($user_id, $password)
	{
		
		if (!$password || mb_strlen($password) < 8) App::error("Password too short.");
		
		if (mb_strlen($password) > 128) App::error("Password too long.");
		
		$DB = new DB();
		
		$password_hash = $this->createHash($password);
		
		//Update password.
		$DB->query("update users set password_hash = $1 where user_id = $2", $password_hash, $user_id);
		
		return "OK";
	}
	
	/**
	* Check user with ID $user_id exists and isn't marked deleted.
	* @param $user_id int The user ID to check.
	* @returns bool True if the user exists and isn't marked deleted, false if user doesn't exist or is marked deleted.
	*/
	public function checkUser($user_id)
	{
		$DB = new DB();
		
		$user = $DB->query("select user_id, deleted from users where user_id = $1", $user_id);
			
		//Check a valid username was returned.
		if (!$user || !isset($user[0]) || !isset($user[0]['user_id']) || $user[0]['deleted']=='t') return false;
		
		return true;
	}
	
	/**
	* Log user out. Destroys session and regenerates session ID.
	* @returns void.
	*/
	public function logout()
	{
		//Start session. @ = Don't complain if it's already been started.
		@session_start();
		
		if (session_status() === PHP_SESSION_ACTIVE) 
		{ 
			unset($_SESSION['user_id']); //Paranoid.
			unset($_SESSION['username']); //Paranoid.
			
			session_unset();
			session_destroy();
			$_SESSION = array();
			
			session_write_close();
			
			@session_start();
			
			session_regenerate_id(true);
			
			$_SESSION['user_id'] = 0;
			
			require_once '../app/models/TokenModel.php';
			$token = new TokenModel();
			$_SESSION['token'] = $token->create();
		}
	}
	
	/**
	* Log a user in. Sets up logged-in session on login success.
	* @param $username The username of the account to attempt log in for.
	* @param $password The password to try when logging in.
	* @returns True on login success, false on login failure.
	*/
	public function login($username, $password)
	{
		$DB = new DB();
			
		if (mb_strlen($username) > 16) App::error("Username too long.");
		if (mb_strlen($password) > 128) App::error("Password too long.");
		
		$username = trim($username);
		
		if ($this->checkPassword($username, $password))
		{
			//Start session, but only if it hasn't been started already.
			@session_start;
			
			//Create a new session ID. This is essential for security to prevent a Session Fixation attack.
			session_regenerate_id(true);
			
			$user = $DB->query("select user_id, username, deleted from users where username ilike $1", $username);
			
			//Check a valid user was returned, that hasn't been deleted.
			if (!$user || !isset($user[0]) || !isset($user[0]['username']) || $user[0]['username'] == "" || $user[0]['deleted'] == 't') return false;
			
			$_SESSION['user_id'] = $user[0]['user_id'];
			$_SESSION['username'] = $user[0]['username'];
			
			require_once '../app/models/TokenModel.php';
			$token = new TokenModel();
			$_SESSION['token'] = $token->create();
			
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/**
	* Check a given username and password are valid.
	* Inserts a short delay to slow down brute-force password cracking attempts. 
	* Always denies login to system accounts.
	* @param $username The username to check.
	* @param $password The password to check.
	* @returns True if password is correct for given account name, false if not. (Always returns false for system accounts).
	*/
	public function checkPassword($username, $password)
	{
		
		if (mb_strlen($username) > 16) App::error("Username too long.");
		if (mb_strlen($password) > 128) App::error("Password too long.");
		
		$username = trim($username);
		
		$DB = new DB();
		
		//Find account by username, case-insensitive. Prevent login to system accounts.
		$DB->query("BEGIN;");
		
		//Get user data.
		$user = $DB->query("select user_id, username, password_hash from users where username ilike $1 and system_account != 't'", $username);
		
		//Check a valid password_hash was returned.
		if (!$user || !isset($user[0]) || !isset($user[0]['password_hash']) || $user[0]['password_hash'] == "") return false;
		
		//Wait for lock on user_login_lock.
		$lockcheck = $DB->query("select * from user_login_lock where user_id = $1 FOR UPDATE", $user[0]['user_id']);
		if (!$lockcheck || !isset($lockcheck[0]) || $lockcheck[0]['user_id']!=$user[0]['user_id']) App::error("Unable to get row lock during login.");
		
		//Sleep while holding row lock, to slow down brute force login attacks.
		//2 seconds = 2000000 useconds
		usleep(1500000);
		
		if (password_verify ($password, $user[0]['password_hash']))
		{	
			$DB->query("COMMIT;");
			return true;
		}
		else
		{
			$DB->query("COMMIT;");
			return false;
		}
	}
	
	/**
	* Get details for a given user, identified by user ID.
	* @param $user_id The user ID to get details for.
	* @returns The user details.
	*/
	public function getUserById($user_id)
	{
		
		//Sanitize.
		$user_id = $user_id * 1;
		
		$DB = new DB();
		
		$result = $DB->query("select user_id, username, email, system_account, creation_date, deleted, null as avatar_url, avatar_override, game_points, steam, twitch, bio from users where user_id = $1", $user_id);
		
		if ($result[0]['avatar_override'] == "")
		{
			$result[0]['avatar_url'] = $this->getAvatar($result[0]['email']);
		}
		else
		{
			$result[0]['avatar_url'] = $result[0]['avatar_override'];
		}
		
		unset($result[0]['avatar_override']);
		
		return $result;
	}
	
	/**
	* Get user details based on given name (case insensitive search).
	* @param $username string The user name to search for.
	* @returns User data based on given name (case insensitive search).
	*/
	public function getUserByName($username)
	{
		
		$DB = new DB();
		
		$result = $DB->query("select user_id from users where username ilike $1", $username);
		
		return $this->getUserById($result[0]['user_id']);
	}
	
	/**
	* Get the avatar icon image source URL from the Gravatar site based on a given email.
	* Uses the "identicon" method to generate an icon pattern based on the email if no avatar is registered for that email.
	* @param $email The email address to use.
	* @returns The avatar image source URL.
	*/
	public function getAvatar($email)
	{
        //Use hash-generated icon as default.
        $default = "identicon";
        $size = 128;
        return "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?r=x&d=" . urlencode( $default ) . "&s=" . $size;
    }
}

?>
