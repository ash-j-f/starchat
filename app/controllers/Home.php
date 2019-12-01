<?php

/**
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

/**
* Home controller.
* Provides default controller and displays site front page to guest user, or the channels list to a logged in user.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
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