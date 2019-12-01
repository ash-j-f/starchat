<?php

/**
* Token model.
* Provides functions for the manipulation of Token data.
* Tokens provide XSS protection by validating form submission data.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
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