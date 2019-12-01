<?php

/**
* Profilesave controller.
* Saves changes to a user's profile.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

class Profilesave extends Controller 
{
	/**
	* Default method.
	* Save changes to use profile.
	* @returns void.
	*/
	public function index()
	{
		
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		$user = $this->model('UserModel');
		
		$username = $_POST['username'];
		$password = $_POST['password'];
		$newPassword = $_POST['newpassword'];
		$email = $_POST['email'];
		$steam = $_POST['steam'];
		$twitch = $_POST['twitch'];
		$bio = $_POST['bio'];
		
		if (mb_strlen($username) > 16) App::error("Username too long.");
		if (mb_strlen($password) > 128) App::error("Password too long.");
		if (mb_strlen($newPassword) > 128) App::error("Password too long.");
		if (mb_strlen($email) > 128) App::error("Email too long.");
		if (mb_strlen($steam) > 128) App::error("Steam name too long.");
		if (mb_strlen($twitch) > 128) App::error("Twitch name too long.");
		if (mb_strlen($bio) > 128) App::error("Bio is too long.");
		
		$getString = "?username=".urlencode($username)."&email=".urlencode($email)."&steam=".urlencode($steam)."&twitch=".urlencode($twitch)."&bio=".urlencode($bio);
		
		//Check current password is correct.
		if (!$user->checkPassword($_SESSION['username'], $password))
		{
			header("Location: /profile/error/authentication_fail".$getString);
			exit;
		}
		
		$status = $user->editUser($_SESSION['user_id'], $username, $newPassword, $email, $steam, $twitch, $bio);
		
		//If user edit succeeds, go to channels controller.
		if ($status == "OK")
		{
			//Update username in session.
			$userDetails = $user->getUserById($_SESSION['user_id']);
			$_SESSION['username'] = $userDetails[0]['username'];
			
			header("Location: /channels");
			exit;
		}
		
		//Check status error.
		switch ($status)
		{
			case 'invalid_username':
				$error = "invalid_username";
				break;
			case 'invalid_password':
				$error = "invalid_password";
				break;
			case 'invalid_email':
				$error = "invalid_email";
				break;
			case 'username_used':
				$error = "username_used";
				break;
			case 'email_used':
				$error = "email_used";
				break;
			default:
				$error = "unknown";
				break;
		}
		
		//If user creation fails, go back to creation page with an error message.
		header("Location: /profile/error/".$error.$getString);
		exit;
	}
}

?>