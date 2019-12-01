<?php

/**
* Profile controller.
* Displays the user's profile for editing.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

class Profile extends Controller 
{
	
	/**
	* Default method.
	* Displays the user profile in a form for editing.
	* Includes error messages for invalid fields, if required.
	* @param $mode The error mode, if any.
	* @param $errorType The error type, if any.
	* @returns void.
	*/
	public function index($mode = "", $errorType = "")
	{
		App::checkIsLoggedIn();
		
		$user = $this->model('UserModel');
		
		$user_data = $user->getUserById($_SESSION['user_id']);
		
		$this->view('profile', ["user_data" => $user_data[0], "error" => $errorType]);
	}
}

?>