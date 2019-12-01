<?php

/**
* Errormsg controller. 
* Provides display of system error messages to user.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

class Errormsg extends Controller 
{
	
	/**
	* Display error message to user.
	* @returns void.
	*/
	public function index()
	{	
		$this->view('errormsg', []);
	}
	
}

?>