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
* Signup controller.
* Displays the signup page to the user, along with error messages for any fields with incorrect data entered by the user, if required.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
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