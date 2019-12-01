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
* Profile controller.
* Displays the user's profile for editing.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
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