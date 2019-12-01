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
* Controller base class. Used by all derived controller classes.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
*/

class Controller
{
	/**
	* Loads a given model and creates a new model object from it.
	* @returns New model object.
	*/
	protected function model($model)
	{
		require_once '../app/models/'. $model. '.php';
		return new $model();
	}
	
	/**
	* Display a chosen view.
	* @returns void.
	*/
	protected function view($view, $data = [])
	{
		require_once '../app/views/' . $view . '.php';
	}
}


?>