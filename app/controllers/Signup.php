<?php

/**
* Signup controller.
* Displays the signup page to the user, along with error messages for any fields with incorrect data entered by the user, if required.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

class Signup extends Controller 
{
	/**
	* Default method.
	* Display the new user signup page.
	* @returns void.
	*/
	public function index()
	{	
		$this->view('signup', []);
	}
	
	/**
	* Default method.
	* Display the new user signup page, with field errors.
	* @param $errorType The error type to display.
	* @returns void.
	*/
	public function error($errorType = "")
	{	
		$this->view('signup', ["error" => $errorType]);
	}
}

?>