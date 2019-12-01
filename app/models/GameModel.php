<?php

/**
* Game model.
* Provides functions for the manipulation of Game data.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

include_once '../app/core/DB.php';

class GameModel
{
	//Available built-in game types.
	private $game_types = array('blackjack');
	
	/**
	* Get game data for a given channel.
	* @param $channel_id The channel ID.
	* @returns The game data.
	*/
	public function getGameByChannelId($channel_id)
	{
		//Sanitize.
		$channel_id = $channel_id * 1;
		
		require_once '../app/models/ChannelModel.php';
		$channel = new ChannelModel();
	
		if ($channel_id < 1) App::error("Invalid channel ID");
	
		//Check that channel ID is valid.
		$result = $channel->getChannelById($channel_id);
	
		if (!$result || !isset($result[0]) || !isset($result[0]['channel_id'])) App::error("That channel ID not found.");
		
		$DB = new DB();
		
		$result = $DB->query("select game_id, channel_id, game_data from games where channel_id = $1", $channel_id);
		
		return $result;
	}
	
	/**
	* Start a given game type for a given channel.
	* @param $channel_id The channel ID.
	* @param $game_type The game type to start.
	* @param $user_id The user ID of the game owner (the user who started the game).
	* @returns void.
	*/
	public function startGame($channel_id, $game_type, $user_id)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		$user_id = $user_id * 1;
		$game_type = trim($game_type);
		$game_type = strtolower($game_type);
		
		//Check game type is valid.
		if (!in_array($game_type, $this->game_types)) App::error("Unknown game type.");
		
		require_once '../app/models/ChannelModel.php';
		$channel = new ChannelModel();
	
		if ($channel_id < 1) App::error("Invalid channel ID");
	
		//Check that channel ID is valid.
		$result = $channel->getChannelById($channel_id);
	
		if (!$result || !isset($result[0]) || !isset($result[0]['channel_id'])) App::error("That channel ID not found.");
		
		$DB = new DB();
		
		//Check if game row already exists for channel and abort if so.
		$result = $DB->query("select game_id from games where channel_id = $1", $channel_id);
		
		if ($result && isset($result[0]) && isset($result[0]['game_id'])) App::error("A game is already running in this channel.");
		
		//Begin transaction so entire process rolls back if there is an error.
		$DB->query("BEGIN");
		//Create game database row for channel.
		$DB->query("insert into games (channel_id) values($1)", $channel_id);
		//Lock game database row. Abort if failed.
		$result = $DB->query("select * from games where channel_id = $1 FOR UPDATE NOWAIT", $channel_id);
		if (!$result || !isset($result[0]) || !isset($result[0]['channel_id']))
		{
			$DB->query("COMMIT");
			App::error("Unable to get lock on game data. Please try again.");
		}
		
		require_once '../app/models/UserModel.php';
		$user = new UserModel();
		$userData = $user->getUserById($user_id);
		if (!$userData || !isset($userData[0]) || !isset($userData[0]['username'])) App::error("Couldn't find that user ID.");
		
		$gameData = array('gameType'=>$game_type, 'channel_id'=>$channel_id, 'owner_id'=>$user_id, 'owner_name'=>$userData[0]['username']);
		
		//Set initial game data.
		$DB->query("update games set game_data = $2 where channel_id = $1", $channel_id, json_encode($gameData));

		//Unlock game database row.
		$DB->query("COMMIT");
	}
	
	/**
	* Stop a game in a given channel.
	* @param $channel_id The channel ID.
	* @returns void.
	*/
	public function stopGame($channel_id)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		
		require_once '../app/models/ChannelModel.php';
		$channel = new ChannelModel();
	
		if ($channel_id < 1) App::error("Invalid channel ID");
	
		//Check that channel ID is valid.
		$result = $channel->getChannelById($channel_id);
	
		if (!$result || !isset($result[0]) || !isset($result[0]['channel_id'])) App::error("That channel ID not found.");
		
		$DB = new DB();
		
		//Check if game row already exists for channel and abort if so.
		$result = $DB->query("select game_id from games where channel_id = $1", $channel_id);
		
		if (!$result || !isset($result[0]) || !isset($result[0]['game_id'])) App::error("There is no game running in this channel.");
		
		//Begin transaction so entire process rolls back if there is an error.
		$DB->query("BEGIN");
		//Lock game database row. Wait for lock.
		$result = $DB->query("select * from games where channel_id = $1 FOR UPDATE", $channel_id);
		if (!$result || !isset($result[0]) || !isset($result[0]['channel_id']))
		{
			$DB->query("COMMIT");
			App::error("Unable to get lock on game data.");
		}
	
		//Delete game row.
		$DB->query("delete from games where channel_id = $1", $channel_id);

		//Unlock game database row.
		$DB->query("COMMIT");
	}
	
	/**
	* Issue a command to a game in a given channel.
	* @param $channel_id The channel ID.
	* @param $user_id The user ID of the user issuing the command.
	* @param $command The command string.
	* @returns void.
	*/
	public function command($channel_id, $user_id, $command)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		$user_id = $user_id * 1;
		
		require_once '../app/models/ChannelModel.php';
		$channel = new ChannelModel();
	
		if ($channel_id < 1) App::error("Invalid channel ID");
		if ($user_id < 1) App::error("Invalid user ID");
	
		//Check that channel ID is valid.
		$result = $channel->getChannelById($channel_id);
	
		if (!$result || !isset($result[0]) || !isset($result[0]['channel_id'])) App::error("That channel ID not found.");
		
		$DB = new DB();
		
		//Check if game row already exists for channel and abort if so.
		$result = $DB->query("select game_id from games where channel_id = $1", $channel_id);
		
		if (!$result || !isset($result[0]) || !isset($result[0]['game_id'])) App::error("There is no game running in this channel.");
		
		//Begin transaction so entire process rolls back if there is an error.
		$DB->query("BEGIN");
		//Lock game database row. Wait for lock.
		$gameRow = $DB->query("select * from games where channel_id = $1 FOR UPDATE", $channel_id);
		if (!$gameRow || !isset($gameRow[0]) || !isset($gameRow[0]['channel_id']))
		{
			$DB->query("COMMIT");
			App::error("Unable to get lock on game data.");
		}
	
		//Issue command to game.
		$gameData = json_decode($gameRow[0]['game_data'], true);
	
		$response = "";
	
		switch($gameData['gameType'])
		{
			case 'blackjack':
				require_once('../app/games/BlackjackGame.php');
				$game = new BlackjackGame();
				list($gameData, $response) = $game->command($gameRow, $user_id, $command);
				break;
			default:
				App::error("Unknown game type while processing game in channel ID " . $gameRow[0]['channel_id']);
				break;
		}

		//Save changed game data.
		//Insert game data back to game table.
		$gameDataJSON = json_encode($gameData);
		$DB->query("update games set game_data = $2 where channel_id = $1", $gameRow[0]['channel_id'], $gameDataJSON);
		
		//Unlock game database row.
		$DB->query("COMMIT");
		
		return $response;
	}
	
	/**
	* Process state and actions for a game in a given channel.
	* @param $channel_id The channel ID.
	* @returns void.
	*/
	public function processGame($channel_id)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		
		require_once '../app/models/ChannelModel.php';
		$channel = new ChannelModel();
	
		if ($channel_id < 1) App::error("Invalid channel ID");
	
		//Check that channel ID is valid.
		$result = $channel->getChannelById($channel_id);
	
		if (!$result || !isset($result[0]) || !isset($result[0]['channel_id'])) App::error("That channel ID not found.");
		
		$DB = new DB();
		
		//Check if game row already exists for channel and abort if so.
		$result = $DB->query("select game_id from games where channel_id = $1", $channel_id);
		
		//If there is no game running in this channel then exit the function.
		if (!$result || !isset($result[0]) || !isset($result[0]['game_id']))
		{
			return;
		}
		
		//Begin transaction so entire process rolls back if there is an error.
		$DB->query("BEGIN");
		
		//Lock game database row. Abort silently if unable to get lock. Use SKIP LOCKED here as a lock fail on NOWAIT generates SQL errors in the database log.
		$gameRow = $DB->query("select * from games where channel_id = $1 FOR UPDATE SKIP LOCKED", $channel_id);
		if (!$gameRow || !isset($gameRow[0]) || !isset($gameRow[0]['channel_id']) || $gameRow[0]['channel_id'] != $channel_id)
		{
			$DB->query("COMMIT");
			return;
		}
	
		$gameData = json_decode($gameRow[0]['game_data'], true);
	
		switch($gameData['gameType'])
		{
			case 'blackjack':
				require_once('../app/games/BlackjackGame.php');
				$game = new BlackjackGame();
				$gameData = $game->process($gameRow);
				break;
			default:
				App::error("Unknown game type while processing game in channel ID " . $gameRow[0]['channel_id']);
				break;
		}
		
		//Save changed game data.
		//Insert game data back to game table.
		$gameDataJSON = json_encode($gameData);
		$DB->query("update games set game_data = $2 where channel_id = $1", $gameRow[0]['channel_id'], $gameDataJSON);

		//Unlock game database row.
		$DB->query("COMMIT");
	}
	
	/**
	* Add or remove points from user's game_points.
	* NOTE: A database trigger ensures the total game score never drops below 0.
	* @param $user_id int The user ID of account to change.
	* @param $points int The number of points to add or subtract (Eg: 5, -5).
	* @returns void.
	*/
	public function changePoints($user_id, $points)
	{
		//Sanitize.
		$user_id *= 1;
		$points *=1;
		
		if ($user_id < 1) App::error("Invalid user ID");
		
		$DB = new DB();
		$DB->query("update users set game_points = game_points + $2 where user_id = $1", $user_id, $points);
	}
}

?>