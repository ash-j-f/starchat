<?php

/**
* Controller base class. Used by all derived controller classes.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
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