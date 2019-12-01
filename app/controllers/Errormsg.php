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
* Errormsg controller. 
* Provides display of system error messages to user.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
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