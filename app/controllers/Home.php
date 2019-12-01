<?php

/**
* Home controller.
* Provides default controller and displays site front page to guest user, or the channels list to a logged in user.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

class Home extends Controller 
{
	
	/**
	* Default method for this controller.
	* Displays the home page, of if user is logged in displays the channels list page.
	* @returns void.
	*/
	public function index()
	{
		//If user IS logged in then go to channels page.
		if (isset($_SESSION) && isset($_SESSION['username']) && $_SESSION['username'] != "")
		{
			header("Location: /channel/viewall");
			exit;
		}
		
		$this->view('index', []);
	}
}

?>