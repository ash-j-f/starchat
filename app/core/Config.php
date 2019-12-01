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
* Config class.
* Provides access to the configuration options defined in the application's "config.ini" file.
* Parses config.ini file and store result per request in a static variable.
* Config options can be accessed anywhere in the codebase by using "Config::getConfigOption('option_name')".
* ABSTRACT class, to be used as a singleton only.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
*/
abstract class Config
{
	//Stored data of the parsed ini file.
	private static $parsedIni = false;
	
	/**
	* Get a config option by name. Note that PHP will throw an error if 
	* config option $option does not exist. If the ini file has already been parsed,
	* it will get the ini data from the static $parsedIni variable. If not, then it
	* will parse the ini file and store it in $parsedIni.
	* @param $option The name of the config option.
	* @returns The config option data.
	*/
	public static function getConfigOption($option)
	{
		if (!Config::$parsedIni) Config::$parsedIni = parse_ini_file('../conf/config.ini');
		
		return Config::$parsedIni[$option];
	}
	
}

?>