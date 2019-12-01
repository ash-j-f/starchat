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
* Database connector.
* Provides database connection and querying functionality.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
*/

include_once '../app/core/Config.php';

class DB
{
	//Database connection, held by the DB object so only one gets created per instance.
	public $DBconn = false;
	
	/**
	* Class constructor.
	* @returns void.
	*/
	function __construct()
	{	
		if (Config::getConfigOption("DatabaseAutoSetup"))
		{
			//Establish database connection if none yet exists.
			if (!$this->DBconn) $this->connect();
			
			//Check if database is set up...
			$arr = $this->query("select tablename from pg_tables where tablename = 'users';");
			$tableExists = ($arr && isset($arr[0]) && isset($arr[0]['tablename']) && $arr[0]['tablename'] == 'users');
		
			//Set up database if not set up yet.
			if (!$tableExists)
			{
				//Initialise the database.
				$file = file_get_contents('../conf/init.sql');
				if (!$file) App::error("Could not read init.sql while initialising database.");
				//Establish database connection if none yet exists.
				if (!$this->DBconn) $this->connect();
				//Run contents of database init file as a single query.
				pg_query($this->DBconn, "BEGIN;");
				pg_query($this->DBconn, $file);
				pg_query($this->DBconn, "COMMIT;");
			}
		}
	}
	
	/**
	* Connect to database using connection settings defined in config.ini.
	* @returns void.
	*/
	public function connect()
	{
		$host = Config::getConfigOption("DatabaseHost");
		$port = Config::getConfigOption("DatabasePort");
		$dbname = Config::getConfigOption("DatabaseName");
		$user = Config::getConfigOption("DatabaseUser");
		$password = Config::getConfigOption("DatabasePassword");
		
		$this->DBconn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
		
		//Abort on connection failure.
		if (!$this->DBconn)
		{
			ob_clean();
			App::error("Cannot connect to the database. Check database settings in the config.ini file.");
		}
	}
	
	/**
	* Run a PARAMETERISED SQL query and return the result. https://php.net/manual/en/function.pg-query-params.php
	* Parameterised queries separate the query string from the value input.
	* Eg: query("select * from users where user_id = $1", $user_id)
	* @params $query The SQL query string.
	* @params ... All parameters following the query string are treated as PARAMETERISED SQL values.
	* @returns Query result.
	*/
	public function query($query = "")
	{
		//Establish database connection if none yet exists.
		if (!$this->DBconn) $this->connect();
		
		//Use all arguments passed to this function following the first argument as parameter values.
		$args = func_get_args();
		$params = array_splice($args, 1);
		
		//Run parameterised query.
		$result = pg_query_params($this->DBconn, $query, $params);
		
		//Return false if query failed.
		if ($result!==false) {
			$arr = pg_fetch_all($result);
		} else {
			$arr = false;
		}
		
		return $arr;
	}
	
}

?>