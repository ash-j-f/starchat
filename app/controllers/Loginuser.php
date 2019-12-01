<?php

/**
* Loginuser controller.
* Provides login check functions. If login fails, sends user back to login screen to display an error.
* If login succeeds, sets user session to logged in and sends user to the "list all channels" page.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

class Loginuser extends Controller 
{
	/**
	* Default method for this controller.
	* Provides login check functions.
	* @returns void.
	*/
	public function index()
	{

		App::checkToken();

		$user = $this->model('UserModel');
		
		$username = trim($_POST['username']);
		$password = $_POST['password'];
		
		if (mb_strlen($username) > 16) App::error("Username too long.");
		if (mb_strlen($password ) > 128) App::error("Password too long.");

		$success = false;
		$recaptcha_response = null;
		$recaptcha_success = false;
		$recaptcha_required = Config::getConfigOption("EnableCaptcha");
		
		if ($recaptcha_required)
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

		if (!$recaptcha_required || ($recaptcha_required && $recaptcha_success))
		{
			$success = $user->login($username, $password);
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
			$success = false;
			header("Location: /login/error/recaptcha_fail?username=".urlencode($username));
			exit;
		}
		
		if (!$success)
		{	
			header("Location: /login/error/?username=".urlencode($username));
			exit;
		}
		
		header("Location: /");
		exit;
	}
}

?>