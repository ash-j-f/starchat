<?php

/**
* Login controller.
* Displays login screen to user, and a login error message if required.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

class Login extends Controller 
{
	/**
	* Default method for this controller.
	* Displays the help page.
	* @returns void.
	*/
	public function index()
	{
		$this->view('login', []);
	}
	
	/**
	* Displays the help page with field errors.
	* @param $errorType Error type to display.
	* @returns void.
	*/
	public function error($errorType = "failed")
	{
		$this->view('login', ["error" => $errorType]);
	}
}

?>