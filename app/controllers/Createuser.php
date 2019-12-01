<?php

/**
* Createuser controller.
* Provides user creation functions.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

class Createuser extends Controller 
{
	
	/**
	* Create a new user based on details given in a form.
	* @returns void.
	*/
	public function index()
	{
		
		App::checkToken();
		
		$user = $this->model('UserModel');
		
		$username = trim($_POST['username']);
		$password = $_POST['password'];
		$email = trim($_POST['email']);
		
		if (mb_strlen($username) > 16) App::error("Username too long.");
		if (mb_strlen($password) > 128) App::error("Password too long.");
		if (mb_strlen($email) > 128) App::error("Email too long.");
		
		$status = "";
		$recaptcha_response = null;
		$recaptcha_success = false;
		
		if (Config::getConfigOption("EnableCaptcha"))
		{
			//Run ReCaptcha check
			require_once '../app/core/ReCaptchaAutoLoad.php';
			$recaptcha = new \ReCaptcha\ReCaptcha(Config::getConfigOption("GoogleCaptchaServerSecretKey"));
			//Determine user's IP.
			$ip = "";
			if (!empty($_SERVER['HTTP_CLIENT_IP']))
			{
				//IP is from shared internet.
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			}
			else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			{
				//IP is from a proxy.
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}
			else
			{
				//IP is direct.
				$ip = $_SERVER['REMOTE_ADDR'];
			}
			$recaptcha_response = $recaptcha->setExpectedHostname($_SERVER['HTTP_HOST'])
								->verify($_POST['g-recaptcha-response'], $ip);
			$recaptcha_success = $recaptcha_response->isSuccess();
		}
		
		if (!Config::getConfigOption("EnableCaptcha") || (Config::getConfigOption("EnableCaptcha") && $recaptcha_success)) 
		{
			//ReCaptcha verified ok, so try to create user.
			$status = $user->createUser($username, $password, $email);
		} 
		else 
		{
			//Exit immediately on critical ReCaptcha error.
			//Errors to do with bad user input or expired captcha checks are not considered critical.
			//All other errors are considered fatal.
			$recaptcha_errors = $recaptcha_response->getErrorCodes();
			if ($recaptcha_errors && is_array($recaptcha_errors) && count($recaptcha_errors) > 0)
			{
				$permitted_errors = array("missing-input-response", "timeout-or-duplicate");
				foreach($recaptcha_errors as $errortype)
				{
					if (!in_array($errortype, $permitted_errors)) App::error("ReCaptcha errors reported: " . implode(", ", $recaptcha_errors));
				}
			}
			
			//ReCaptcha failed to verify.
			$status = "recaptcha_fail";
		}
		
		//If user creation succeeds, go to channels controller.
		if ($status == "OK")
		{
			//Automaticlaly log user in.
			$user->login($username, $password);
			
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
			case 'recaptcha_fail':
				$error = "recaptcha_fail";
				break;
			default:
				$error = "unknown";
				break;
		}
		
		//If user creation fails, go back to creation page with an error message.
		header("Location: /signup/error/".$error."?username=".urlencode($username)."&email=".urlencode($email));
		exit;
	}
}

?>