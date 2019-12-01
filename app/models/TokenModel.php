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
* Token model.
* Provides functions for the manipulation of Token data.
* Tokens provide XSS protection by validating form submission data.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
*/

class TokenModel
{
	/**
	 * Generate a random string, using a cryptographically secure 
	 * pseudorandom number generator (random_int)
	 *
	 * This function by Scott Arciszewski https://stackoverflow.com/a/31107425
	 * 
	 * For PHP 7, random_int is a PHP core function
	 * For PHP 5.x, depends on https://github.com/paragonie/random_compat
	 * 
	 * @param int $length      How many characters do we want?
	 * @param string $keyspace A string of all possible characters
	 *                         to select from
	 * @return string
	 */
	function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
	{
		$pieces = [];
		$max = mb_strlen($keyspace, '8bit') - 1;
		for ($i = 0; $i < $length; ++$i) {
			$pieces []= $keyspace[random_int(0, $max)];
		}
		return implode('', $pieces);
	}
	
	/**
	* Check if a given form validation token is valid.
	* @param $token The token value to check.
	* @returns bool True if the token is valid, false if not.
	*/
	public function check($token)
	{
		if ($token=="" || $token==null || $token == false)
		{
			return false;
		}
		
		if (session_status() == PHP_SESSION_NONE || !isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) 
		{
			return false;
		}
		
		if (!isset($_SESSION['token']) || $_SESSION['token']=="" || $_SESSION['token']==null || $_SESSION['token']==false)
		{
			return false;
		}
		
		if (hash_equals($_SESSION['token'], $token))
		{
			return true;
		}
		
		return false;
		
	}
	
	/**
	* Create a new token value from a crypto-safe random string generator.
	* @returns The new token value.
	*/
	public function create()
	{
		return $this->random_str(64, '0123456789abcdef');
	}
}

?>