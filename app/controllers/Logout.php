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
* Logout controller.
* Logs user out, by destroying session and sending user back to home page.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
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