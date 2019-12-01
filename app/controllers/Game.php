<?php

/**
* Game controller.
* Provides in-chat game functions.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

class Game extends Controller 
{

	/**
	* Default method. This controller should not be called via its default method.
	* @returns void.
	*/
	public function index()
	{
		App::error("This controller has no default output.");
	}
	
	/**
	* List all in-built games available in the system.
	* @returns void.
	*/
	public function listgames()
	{
		require_once('../app/games/BlackjackGame.php');
		$game = new BlackjackGame();
		$name1 = $game->getName();
		
		$games = "<strong>***Available Games***</strong>
		<br />
		<br />1. {$name1}
		<br />
		<br />To see game rules type: <strong>/game rules GameName</strong>
		<br />To start a game type: <strong>/game start GameName</strong>
		<br />To stop a running game type: <strong>/game stop</strong>
		<br />To issue commands to a running game type: <strong>/g CommandName</strong>
		";
		
		echo json_encode(array('message_htmlsafe' => $games));
	}
	
	/**
	* List game rules for a given in-built game.
	* @param $game_type The game name to list rules for.
	* @returns void.
	*/
	public function listrules($game_type="")
	{
		$rules = "";
		
		switch (strtolower(trim($game_type)))
		{
			case 'blackjack':
				require_once('../app/games/BlackjackGame.php');
				$game = new BlackjackGame();
				$rules = $game->getRules();
				break;
			default:
				App::error("Unknown game type given.");
				break;
		}
		
		echo json_encode(array('message_htmlsafe' => $rules));
	}
	
	/**
	* Start an in-built game running.
	* @param $channel_id ID of the channel to start the game in.
	* @param $game_type The name of the in-buult game to start.
	* @returns void.
	*/
	public function start($channel_id="", $game_type="")
	{
		
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		//Check channel ID is sane and that we have permission to interact with a game in the channel.
		$this->channelCheck($channel_id);
		
		$member = $this->model('MemberModel');
		
		$user = $this->model('UserModel');
		
		$gameBot = $user->getUserByName('GameBot');
		if (!isset($gameBot[0]['user_id'])) App::error("Couldn't find GameBot account.");
		
		//Add gamebot to the channel.
		$member->createOrUpdateMember($channel_id, $gameBot[0]['user_id'], 'regular');
		
		$game = $this->model('GameModel');

		//Start a new game. Will check if there is a game in progress and report an error.
		$game->startGame($channel_id, $game_type, $_SESSION['user_id']);
		
		//Process at least one game loop.
		$game->processGame($channel_id);
		
		//The owner of a game automatically joins the game.
		$game->command($channel_id, $_SESSION['user_id'], "join");
		
		//Update GameBot's "last seen" time in this channel.
		$member->updateLastSeen($channel_id, $gameBot[0]['user_id']);
		
		echo "OK";

	}
	
	/**
	* Stop an in-built game that is running in a given channel.
	* @param $channel_id ID of the channel to stop the game in.
	* @returns void.
	*/
	public function stop($channel_id="")
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		//Check channel ID is sane and that we have permission to interact with a game in the channel.
		$channelData = $this->channelCheck($channel_id);
		
		$user = $this->model('UserModel');
		
		$gameBot = $user->getUserByName('GameBot');
		if (!isset($gameBot[0]['user_id'])) App::error("Couldn't find GameBot account.");
		
		$member = $this->model('MemberModel');
		
		//Add gamebot to the channel.
		$member->createOrUpdateMember($channel_id, $gameBot[0]['user_id'], 'regular');
		
		$game = $this->model('GameModel');
		
		//Check if user requesting the stop is game owner, channel owner, admin or mod in channel.
		//Regular users cannot stop a game unless they own it.
		$gameInfo = $game->getGameByChannelId($channel_id);
		if (!$gameInfo || !isset($gameInfo[0]) || !isset($gameInfo[0]['game_id'])) App::error("There is no game running in this channel.");
		$gameData = json_decode($gameInfo[0]['game_data'], true);
		if ($_SESSION['user_id'] != $gameData['owner_id'] && $member->checkRole($channel_id, $_SESSION['user_id'], 'regular') && $_SESSION['user_id'] != $channelData[0]['owner_id'] && !$member->isSuperuser($_SESSION['user_id'])) App::error("You do not have permission to stop that game.");
		
		//Stop game.
		$game->stopGame($channel_id);
		
		$message = $this->model('MessageModel');
		
		//Update GameBot's "last seen" time in this channel.
		$member->updateLastSeen($channel_id, $gameBot[0]['user_id']);
		
		$username = $_SESSION['username'];
		
		$message->create($channel_id, "The current game has been stopped by {$username}.", false, $gameBot[0]['user_id']);
		
		echo "OK";
	}
	
	/**
	* Issue a command to a game running in a given channel.
	* @param $channel_id ID of the channel to issue the command in.
	* @returns void.
	*/
	public function command($channel_id)
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		//Check channel ID is sane and that we have permission to interact with a game in the channel.
		$this->channelCheck($channel_id);
		
		$user = $this->model('UserModel');
		
		$gameBot = $user->getUserByName('GameBot');
		if (!isset($gameBot[0]['user_id'])) App::error("Couldn't find GameBot account.");
		
		$member = $this->model('MemberModel');
		
		//Add gamebot to the channel.
		$member->createOrUpdateMember($channel_id, $gameBot[0]['user_id'], 'regular');
		
		$game = $this->model('GameModel');

		$user_id = $_SESSION['user_id'];
		
		//Issue game command.
		$response = $game->command($channel_id, $user_id, $_POST['command']);
		
		$message = $this->model('MessageModel');
		
		//Update GameBot's "last seen" time in this channel.
		$member->updateLastSeen($channel_id, $gameBot[0]['user_id']);
		
		echo json_encode(array('message_htmlsafe' => $response));
	}
	
	/**
	* Check channel ID is sane and that we have permission to interact with a game in the channel.
	* Private helper function used by this controller.
	* @param $channel_id int The channel ID.
	* @returns The channel data as an array, of the channel is valid and the user has permission to access it.
	*/
	private function channelCheck($channel_id)
	{
		$member = $this->model('MemberModel');
		
		//Check if we are banned from that channel.
		if ($member->checkIfBanned($channel_id, $_SESSION['user_id'])) App::error("You have been banned from this channel.");
		
		$channel = $this->model('ChannelModel');
		
		//Check if we are member of private channel.
		$channelData = $channel->getChannelById($channel_id, false);
		if ($channelData && isset($channelData[0]) && $channelData[0]['public'] != 't' && $channelData[0]['owner_id'] != $_SESSION['user_id'] && !$member->checkIfMember($channel_id, $_SESSION['user_id'])) App::error("You are not a member of this private channel.");
		
		//Check user has permission to post in this channel.
		$channelData = $channel->getChannelById($channel_id);
		
		if (!$channelData || !isset($channelData[0]['channel_id'])) App::error("Invalid channel ID.");
		
		if ($channelData[0]['deleted'] == 't') App::error("Channel has been deleted.");
		
		return $channelData;
	}
	
}

?>