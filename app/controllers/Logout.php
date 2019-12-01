<?php

/**
* Logout controller.
* Logs user out, by destroying session and sending user back to home page.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

class Logout extends Controller 
{
	
	/**
	* Default method for this controller.
	* Logs user out.
	* @returns void.
	*/
	public function index()
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		$user = $this->model('UserModel');
		$user->logout();
		
		//Send user back to home page.
		header('Location: /');
		exit;
	}
}

?>