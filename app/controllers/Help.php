<?php

/**
* Help controller.
* Displays help screen to user.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

class Help extends Controller 
{
	/**
	* Default method for this controller.
	* Displays the help page.
	* @returns void.
	*/
	public function index()
	{	
		$this->view('help', []);
	}
}

?>