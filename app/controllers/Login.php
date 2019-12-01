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
* Login controller.
* Displays login screen to user, and a login error message if required.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
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