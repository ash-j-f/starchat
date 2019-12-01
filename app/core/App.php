<?php

/** 
* App class, the core of the application.
* This class provides the core of the MVC framework, and redirects an incmong request to the correct controller.
* Also privides error handling, as well as security such as login check functions and form token check functions.
*
* MVC framework based on ideas by:
* https://github.com/panique and https://github.com/JaoNoctus and their simple MVC framework "MINI3" https://github.com/panique/mini3
* An excellent MVC tutorial by https://codecourse.com/ at https://www.youtube.com/watch?v=OsCTzGASImQ&list=PLfdtiltiRHWGXVHXX09fxXDi-DqInchFD 
*
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

class App
{
	//Default controller.
	protected $controller = 'home'; 
	
	//Default controller method.
	protected $method = 'index'; 
	
	//Default parameters to pass to views.
	protected $params = [];
	
	/**
	* Class constructor.
	* @returns void.
	*/
	public function __construct()
	{

		//Remove null bytes from all incoming POST and GET data.
		foreach ($_POST as &$data)
		{
			$data = str_replace("\0", "", $data);
		}
		foreach ($_GET as &$data)
		{
			$data = str_replace("\0", "", $data);
		}
		
		//Start a session if there isn't already one.
		session_start();
		if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id']) || !isset($_SESSION['token']))
		{
			$_SESSION['user_id'] = 0;
			require_once '../app/models/TokenModel.php';
			$token = new TokenModel();
			$_SESSION['token'] = $token->create();
		}
		
		//If user is logged in...
		if (isset($_SESSION['user_id']) && $_SESSION['user_id']!=0)
		{
			//Check the user account still exists and isn't deleted.
			require_once '../app/models/UserModel.php';
			$user = new UserModel();
			if (!$user->checkUser($_SESSION['user_id']))
			{
				//User is invalid or deleted. Log out.
				$user->logout();
				
				if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
				{
					//AJAX REQUEST...
					//Exit with error message.
					die("Account has been deleted.");
				}
				else
				{
					//REGULAR BROWSER REQUEST...
					//Redirect to front page.
					header("Location: /");
					exit;
				}
			}
		
			//Update session details.
			$userData = $user->getUserById($_SESSION['user_id']);
			//Update username (but only if it is already set). DON'T set initial username here. That should be done in model login function.
			if (isset($_SESSION['username'])) $_SESSION['username'] = $userData[0]['username'];
			//Update game points.
			$_SESSION['game_points'] = $userData[0]['game_points'];
		}
		
		//Get data from incoming URL.
		$url = $this->parseUrl();
		
		//Sanitize.
		$url[0] = trim(strtolower($url[0]));
		
		//Check if the chosen controller exists and use default if not.
		if (file_exists('../app/controllers/'. ucfirst($url[0]) . '.php'))
		{
			$this->controller = ucfirst($url[0]);
			unset($url[0]);
		}
		
		//Invoke the chosen controller.
		require_once '../app/controllers/' . ucfirst($this->controller) .  '.php';
		
		$this->controller = new $this->controller;
		
		if (isset($url[1]))
		{
			
			if (method_exists($this->controller, $url[1]) &&
			 is_callable(array($this->controller, $url[1])))
			{
				$this->method = $url[1];
				unset($url[1]);
			}
		}
		
		$this->params = $url ? array_values($url) : [];
		
		call_user_func_array([$this->controller, $this->method], $this->params);
	}
	
	/**
	* Parse the incoming URL to extract controller, method and other data.
	* @returns The URL data as an array.
	*/
	public function parseUrl()
	{
		if (isset($_GET['url']))
		{
			return $url = explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
		}
	}
	
	/**
	* Check if this user is logged in. 
	* Calls the system error method if user is not logged in.
	* @returns void.
	*/
	static public function checkIsLoggedIn()
	{
		//Go back to front page if not logged in.
		if (!isset($_SESSION) || !isset($_SESSION['username']) || $_SESSION['username'] == "" || $_SESSION['user_id'] < 1)
		{
			App::error("You must be logged in to perform that action.");
		}
	}
	
	/**
	* Check a given form token is valid.
	* Calls the system error method if form token is not valid.
	* @returns void.
	*/
	static public function checkToken()
	{
		//Check token.
		require_once('../app/models/TokenModel.php');
		$token = new TokenModel();
		
		//Show error if token is bad.
		if (!$token->check($_POST['token']))
		{	
			App::error("Invalid token.");
			//App::error should stop execution but make doubly sure execution stops here.
			exit;
		}
		
		//Show error if referrer is bad.
		$url_array = parse_url($_SERVER['HTTP_REFERER']);
		if ($_SERVER['HTTP_REFERER'] && $url_array['host']!=$_SERVER['HTTP_HOST'])
		{	
			App::error("Invalid referrer.");
			//App::error should stop execution but make doubly sure execution stops here.
			exit;
		}
	}
	
	/**
	* System error handler.
	* Displays error message to user.
	* @returns void.
	*/
	static public function error($message)
	{
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') 
		{
			//Request was made from an AJAX call...
			//Exit immediately and echo the message.
			die(htmlentities($message));
		}
		else	
		{
			//If the page that called this error was the error page itself, we're in an infinite error loop.
			//So just dump the error message as text.
			if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI']))
			{
				if (substr( $_SERVER['REQUEST_URI'], 0, 9 ) == '/errormsg') die("Infinite error loop detected. The last error message was: " . htmlentities($message));
			}
			
			//Request was made as a regular browser page request...
			//Send user to error page.
			header('Location: /errormsg?msg='.urlencode($message));
			exit;
		}
	}
}

?>