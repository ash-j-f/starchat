<?php

/**
* The Balckjack game module. Provides all blackjack game logic.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

include_once '../app/core/DB.php';

//Gmae state enum.
abstract class BlackjackState
{
	const Waiting = 0;
	const Begin = 1;
	const PlayingOpening = 2;
	const PlayingNextCard = 3;
	const PlayingEnded = 4;
}
//Player state enum.
abstract class BlackjackPlayerState
{
	const Joining = 0;
	const Leaving = 1;
	const Playing = 2;
}
//Allowed moves enum.
abstract class BlackjackPlayerMoves
{
	const Hold = 0;
	const Take = 1;
}

//Core game.
class BlackjackGame
{
	//Time to wait for player input.
	const ROUND_TIME_SECONDS = 20;
	
	//Minimum number of players in a game before it can start.
	const MIN_PLAYERS = 1;

	//All cards in a standard deck.
	private $cards = array(
		"A♠" => array(1, 11),
		"2♠" => array(2),
		"3♠" => array(3),
		"4♠" => array(4),
		"5♠" => array(5),
		"6♠" => array(6),
		"7♠" => array(7),
		"8♠" => array(8),
		"9♠" => array(9),
		"10♠" => array(10),
		"J♠" => array(10),
		"Q♠" => array(10),
		"K♠" => array(10),
		
		"A♣" => array(1, 11),
		"2♣" => array(2),
		"3♣" => array(3),
		"4♣" => array(4),
		"5♣" => array(5),
		"6♣" => array(6),
		"7♣" => array(7),
		"8♣" => array(8),
		"9♣" => array(9),
		"10♣" => array(10),
		"J♣" => array(10),
		"Q♣" => array(10),
		"K♣" => array(10),
		
		"A♥" => array(1, 11),
		"2♥" => array(2),
		"3♥" => array(3),
		"4♥" => array(4),
		"5♥" => array(5),
		"6♥" => array(6),
		"7♥" => array(7),
		"8♥" => array(8),
		"9♥" => array(9),
		"10♥" => array(10),
		"J♥" => array(10),
		"Q♥" => array(10),
		"K♥" => array(10),
		
		"A♦" => array(1, 11),
		"2♦" => array(2),
		"3♦" => array(3),
		"4♦" => array(4),
		"5♦" => array(5),
		"6♦" => array(6),
		"7♦" => array(7),
		"8♦" => array(8),
		"9♦" => array(9),
		"10♦" => array(10),
		"J♦" => array(10),
		"Q♦" => array(10),
		"K♦" => array(10)
	);
	
	/**
	* Get the game name.
	* @returns The game name.
	*/
	public function getName()
	{
		return "Blackjack";
	}
	
	/**
	* Get the game rules.
	* @returns The game rules.
	*/
	public function getRules()
	{
		return "
		<strong>***Blackjack Rules***</strong>
		<br />To start a game type <strong>/g start blackjack</strong>.
		<br />Type <strong>/g join</strong> to join a running game.
		<br />1. Each player is playing against the dealer's hand, not each other's.
		<br />2. Each player starts with a hand of two cards, including the dealer.
		<br />3. The dealer's second card is hidden at first.
		<br />4. Numbered cards are worth their numeric value.
		<br />5. Face cards K Q J are worth 10 points.
		<br />6. Ace is worth 1 or 11 points.
		<br />7. You must choose to hit (take a card) \"<strong>/g hit</strong>\" or stand (keep your current hand) \"<strong>/g stand</strong>\".
		<br />8. You can keep taking a new card until you either decide to stand, or you go bust (score greater than 21).
		<br />9. You must beat the dealer's total hand value with a score no higher than 21 points to win.
		";
	}
	
	/**
	* Run a game command from a user.
	* @param $gameData Array - The game database row as an array.
	* @param $user_id int The user ID of the user issuing the command.
	* @param $command string The command to issue to the game.
	* @returns Array - The first element is the chaged game data. The second element is the command response.
	*/
	public function command($gameRow, $user_id, $command)
	{
		$user_id = $user_id * 1;
		
		$command = trim(strtolower($command));
	
		$gameData = json_decode($gameRow[0]['game_data'], true);
	
		$response = "";
	
		require_once('../app/models/MessageModel.php');
		$message = new MessageModel();
	
		require_once('../app/models/UserModel.php');
		$user = new UserModel();
	
		$gameBot = $user->getUserByName('GameBot');
		if (!isset($gameBot[0]['user_id'])) App::error("Couldn't find GameBot account.");
	
		switch ($command)
		{
			case 'join':
				if (!array_key_exists($user_id, $gameData["players"]) || ($gameData["players"][$user_id]["state"] != BlackjackPlayerState::Joining && $gameData["players"][$user_id]["state"] != BlackjackPlayerState::Playing))
				{
					$gameData["players"][$user_id]["state"] = BlackjackPlayerState::Joining;
					$response = "Joining game. You will be included in the next round.";
				}
				else
				{
					$response = "You have already joined the game.";
				}
				break;
			case 'leave':
				if (!array_key_exists($user_id, $gameData["players"]))
				{
					$response = "You are not participating in the game.";
				}
				else if($gameData["players"][$user_id]["state"] == BlackjackPlayerState::Leaving)
				{
					$response = "You have already chosen to leave the game.";
				}
				else
				{
					$gameData["players"][$user_id]["state"] = BlackjackPlayerState::Leaving;
					$response = "You have chosen to leave the game. You must finish this hand and you will leave at the start of the next round.";
				}
				break;
			case 'hit':
			
				if ($gameData['state'] != BlackjackState::PlayingOpening && $gameData['state'] != BlackjackState::PlayingNextCard)
				{
					$response = "Can't take a card. A new round hasn't started yet.";
					break;
				}
				
				if (!isset($gameData["players"][$user_id]))
				{
					$response = "Can't take a card. You are not participating in this game. Type \"/g join\" to join.";
					break;
				}
			
				if (isset($gameData["players"][$user_id]["bust"]) && $gameData["players"][$user_id]["bust"]===true)
				{
					$response = "Can't take a card. You are bust!";
					break;
				}
				
				if (isset($gameData["players"][$user_id]["hit"]) && $gameData["players"][$user_id]["hit"]===true)
				{
					$response = "Can't take a card. You are already hit!";
					break;
				}
				
				if (isset($gameData["players"][$user_id]["stand"]) && $gameData["players"][$user_id]["stand"]===true)
				{
					$response = "Can't hit, you are standing!";
					break;
				}
			
				if (count($gameData["available_cards"]) == 0) $gameData["available_cards"] = $this->cards;
				$entry_key = array_rand($gameData["available_cards"]);
				$entry_value = $gameData["available_cards"][$entry_key];
				$gameData["players"][$user_id]["hand"][] = array("face" => $entry_key, "value" => $entry_value);
				unset($gameData["available_cards"][$entry_key]);
				if (count($gameData["available_cards"]) == 0) $gameData["available_cards"] = $this->cards;
			
				//Record player has hit.
				$gameData["players"][$user_id]["hit"] = true;
			
				//Calculate hand score min value.
				$score = $this->calcScore($gameData["players"][$user_id]["hand"], 'min');
				
				//Display cards.
				$playerData = $user->getUserById($user_id);
				$player_hand = $playerData[0]['username']." took a card! Their hand is now: `";
				foreach ($gameData["players"][$user_id]["hand"] as &$card)
				{
					$player_hand .= " " . $card["face"];
				}
				
				$bust = $score > 21 ? "They are bust!" : "";
				
				//Set player bust.
				if ($score > 21) $gameData["players"][$user_id]["bust"]=true;
				
				$player_hand .= /*". Score: " . $score .*/ "`. " . $bust;
			
				$message->create($gameData['channel_id'], $player_hand, false, $gameBot[0]['user_id']);
				
				$response = "You took a card.";
				
				break;
			
			case 'stand':
				
				if ($gameData['state'] != BlackjackState::PlayingOpening && $gameData['state'] != BlackjackState::PlayingNextCard)
				{
					$response = "Can't stand. A new round hasn't started yet.";
					break;
				}
				
				if (!isset($gameData["players"][$user_id]))
				{
					$response = "Can't stand. You are not participating in this game. Type \"/g join\" to join.";
					break;
				}
			
				if (isset($gameData["players"][$user_id]["bust"]) && $gameData["players"][$user_id]["bust"]===true)
				{
					$response = "Can't stand. You are bust!";
					break;
				}
				
				if (isset($gameData["players"][$user_id]["stand"]) && $gameData["players"][$user_id]["stand"]===true)
				{
					$response = "You are already standing!";
					break;
				}
				
				if (isset($gameData["players"][$user_id]["hit"]) && $gameData["players"][$user_id]["hit"]===true)
				{
					$response = "Can't stand, you already hit!";
					break;
				}
				
				//Record player stands.
				$gameData["players"][$user_id]["stand"] = true;
				
				$playerData = $user->getUserById($user_id);
				$player_hand = $playerData[0]['username']." stands!";
				
				$message->create($gameData['channel_id'], $player_hand, false, $gameBot[0]['user_id']);
				
				$response = "You stand.";
				
				break;
			default:
				$response = "Unknown game command.";
				break;
		}
		
		return array($gameData, $response);
	}
	
	/**
	* Process game actions.
	* Process function must be called while inside a database transaction initiated by the calling class, and ended by the 
	* calling class once this function is complete.
	* @param $gameData Array - The game database row as an array.
	* @returns void.
	*/
	public function process($gameRow)
	{
		
		$channel_id = $gameRow[0]['channel_id'];
		
		$gameData = json_decode($gameRow[0]['game_data'], true);
		
		$gameType = $gameData["gameType"];
		
		if ($gameType != "blackjack") App::error("Incorrect game type.");
		
		require_once('../app/models/MessageModel.php');
		$message = new MessageModel();
		
		require_once('../app/models/UserModel.php');
		$user = new UserModel();
		
		$gameBot = $user->getUserByName('GameBot');
		if (!isset($gameBot[0]['user_id'])) App::error("Couldn't find GameBot account.");
			
		require_once '../app/models/MemberModel.php';
		$member = new MemberModel();
	
		//Update GameBot's "last seen" time in this channel.
		$member->updateLastSeen($channel_id, $gameBot[0]['user_id']);
		
		if (!isset($gameData["state"]))
		{
			//NEW GAME.
			$gameData["state"] = BlackjackState::Waiting;
			
			$gameData["roundStartTime"] = time() + self::ROUND_TIME_SECONDS;
			
			$gameData["players"] = array();
			
			$gameData["round"] = 1;
			
			$message->create($channel_id, "***Blackjack***\n{$gameData['owner_name']} started a game of Blackjack! Waiting for at least ".self::MIN_PLAYERS." player".(self::MIN_PLAYERS == 1 ? "" : "s")." to join.\nGame will end automatically in ".self::ROUND_TIME_SECONDS." seconds if there aren't enough players. Type \"/g join\" to join the game.", false, $gameBot[0]['user_id']);
			
		}
		else
		{
			//CONTINUING GAME...
			
			
			//ENOUGH PLAYERS
			if ($gameData["state"] == BlackjackState::Waiting && count($gameData["players"]) >= self::MIN_PLAYERS)
			{
				$gameData["state"] = BlackjackState::Begin;

				$message->create($channel_id, "***Blackjack***\nThe minimum required number of players have joined. Round 1 will start in " . ($gameData["roundStartTime"] - time()) . " seconds. Type \"/g join\" to join the game.", false, $gameBot[0]['user_id']);
			}
			
			//NOT ENOUGH PLAYERS AFTER WAITING
			if ($gameData["state"] == BlackjackState::Waiting && count($gameData["players"]) < self::MIN_PLAYERS && time() > $gameData["roundStartTime"])
			{	
				$message->create($channel_id, "***Blackjack***\nThere were not enough players to continue the game. The game has been stopped automatically.", false, $gameBot[0]['user_id']);
				
				require_once('../app/models/GameModel.php');
				$game = new GameModel();
				
				$game->stopGame($channel_id);
			}
			
			//NOT ENOUGH PLAYERS DURING PLAY
			if (($gameData["state"] == BlackjackState::PlayingOpening || $gameData["state"] == BlackjackState::PlayingNextCard) && count($gameData["players"]) < self::MIN_PLAYERS)
			{
				$gameData["state"] = BlackjackState::Waiting;
				
				$gameData["roundStartTime"] = time() + self::ROUND_TIME_SECONDS;
				
				$message->create($channel_id, "***Blackjack***\nNot enough players. Game will end automatically in ".self::ROUND_TIME_SECONDS." seconds if there aren't enough players. Type \"/g join\" to join the game.", false, $gameBot[0]['user_id']);
			}
			
			//BEGIN ROUND
			if ($gameData["state"] == BlackjackState::Begin && count($gameData["players"]) >= self::MIN_PLAYERS && time() > $gameData["roundStartTime"])
			{
				$gameData["state"] = BlackjackState::PlayingOpening;
				
				$gameData["roundStartTime"] = time() + self::ROUND_TIME_SECONDS;
				
				//Reset dealer status.
				$gameData["dealer"]["hand"] = array();
				$gameData["dealer"]["bust"] = false;
				$gameData["dealer"]["hit"] = false;
				$gameData["dealer"]["stand"] = false;
				
				//Reset player status.
				foreach ($gameData["players"] as &$p)
				{
					$p["hand"] = array();
					$p["bust"] = false;
					$p["hit"] = false;
					$p["stand"] = false;
				}
				
				$messageText = "***Blackjack***\nBeginning round {$gameData["round"]}! The round will end in " . self::ROUND_TIME_SECONDS . " seconds.\n";
				
				$deletePlayers = array();
				
				//Process all join and leave commands.
				foreach ($gameData["players"] as $pkey => &$pvalue)
				{
					$player = $user->getUserById($pkey);
					
					if ($pvalue['state'] == BlackjackPlayerState::Joining)
					{
						$pvalue['state'] = BlackjackPlayerState::Playing;
						$messageText .= "{$player[0]['username']} has joined the game.\n";
					}
					
					if ($pvalue['state'] == BlackjackPlayerState::Leaving)
					{
						array_push($deletePlayers, $pkey);
						$messageText .= "{$player[0]['username']} has left the game.\n";
					}
				}
				
				//Process player deletion. We have to do this separately as it changes contents of an array we were iterating over.
				foreach ($deletePlayers as &$pid)
				{
					unset($gameData["players"][$pid]);
				}
				
				//Reset cards in the deck already used.
				$gameData["available_cards"] = $this->cards;
				
				//Only continue if there are still enough players.
				if (count($gameData["players"]) >= self::MIN_PLAYERS)
				{
					//Build dealers hand.
					$gameData["dealer"]["hand"] = array();
					if (count($gameData["available_cards"]) == 0) $gameData["available_cards"] = $this->cards;
					while (count($gameData["available_cards"]) > 0 && count($gameData["dealer"]["hand"]) < 2)
					{
						$entry_key = array_rand($gameData["available_cards"]);
						$entry_value = $gameData["available_cards"][$entry_key];
						$gameData["dealer"]["hand"][] = array("face" => $entry_key, "value" => $entry_value);
						unset($gameData["available_cards"][$entry_key]);
					}
					if (count($gameData["available_cards"]) == 0) $gameData["available_cards"] = $this->cards;
					
					//Build each player's hand.
					foreach ($gameData["players"] as $pkey => &$pvalue)
					{
						//Skip players not playing in this round.
						if ($pvalue['state'] != BlackjackPlayerState::Playing && $pvalue['state'] != BlackjackPlayerState::Leaving) continue;
						
						$pvalue["hand"] = array();
						if (count($gameData["available_cards"]) == 0) $gameData["available_cards"] = $this->cards;
						while (count($gameData["available_cards"]) > 0 && count($pvalue["hand"]) < 2)
						{
							$entry_key = array_rand($gameData["available_cards"]);
							$entry_value = $gameData["available_cards"][$entry_key];
							$pvalue["hand"][] = array("face" => $entry_key, "value" => $entry_value);
							unset($gameData["available_cards"][$entry_key]);
						}
						if (count($gameData["available_cards"]) == 0) $gameData["available_cards"] = $this->cards;
					}

					//Send message with dealer's and player's hands.
					
					$dealer_hand = "";
					foreach($gameData["dealer"]["hand"] as &$card)
					{
						$dealer_hand .= $card["face"] . " " . "##";
						break; //Only show the first card.
					}

					$player_hands = "";
					foreach($gameData["players"] as $pkey => &$pvalue)
					{
						//Skip players not playing in this round.
						if ($pvalue['state'] != BlackjackPlayerState::Playing && $pvalue['state'] != BlackjackPlayerState::Leaving) continue;
						
						$playerData = $user->getUserById($pkey);
						$player_hands .= "\n".$playerData[0]['username'].": `";
						foreach ($pvalue["hand"] as &$card)
						{
							$player_hands .= " " . $card['face'];
						}
						$player_hands .= "`";
					}

					$messageText .= "Player hands are:\nGameBot (Dealer): `{$dealer_hand}` {$player_hands}\n";
				
					$messageText .= "Hit or stand ends in " . self::ROUND_TIME_SECONDS . " seconds. Type \"/g hit\" to take a card, or \"/g stand\" to stand.\n";
				
					//Report when card taking will end.
					$message->create($channel_id, $messageText, false, $gameBot[0]['user_id']);
					
				}
			}
			
			//END CARD TAKING IF ALL PLAYERS FINISHED SIT/STAND CHOICES.
			if ($gameData["state"] == BlackjackState::PlayingNextCard && count($gameData["players"]) >= self::MIN_PLAYERS)
			{
				//End round completely if dealer bust, or all players have chosen to hit or stand, or are bust, or max round time exceeded waiting for player choices.
					
				$playersFinished = true;
				foreach($gameData["players"] as &$p)
				{
					//Skip players not playing in this round.
					if ($p['state'] != BlackjackPlayerState::Playing && $pvalue['state'] != BlackjackPlayerState::Leaving) continue;
					
					if (!$p["bust"] && !$p["stand"] && !$p["hit"])
					{
						$playersFinished = false;
						break;
					}
				}
				
				if ($gameData["dealer"]["bust"] || ($gameData["dealer"]["stand"] && $playersFinished))
				{
					$gameData["state"] = BlackjackState::PlayingEnded;
					$gameData["roundStartTime"] = time();
				}
			}
			
			//END OPENING ROUND AND START NEXT CARD ROUND
			if (($gameData["state"] == BlackjackState::PlayingOpening || $gameData["state"] == BlackjackState::PlayingNextCard) && count($gameData["players"]) >= self::MIN_PLAYERS && time() > $gameData["roundStartTime"])
			{
				
				$messageText = "***Blackjack***\n";
				
				//Mark all players who made no choice as standing.
				foreach($gameData["players"] as $pkey => &$pvalue)
				{
					//Skip players not playing in this round.
					if ($pvalue['state'] != BlackjackPlayerState::Playing && $pvalue['state'] != BlackjackPlayerState::Leaving) continue;
					
					if (!$pvalue["bust"] && !$pvalue["stand"] && !$pvalue["hit"])
					{
						$gameData["players"][$pkey]["stand"] = true;
						
						$playerData = $user->getUserById($pkey);
						$messageText .= $playerData[0]['username']." made no choice, so they stand!\n";
					}
				}
				
				//Reset player HIT status. 
				//Anyone standing or bust keeps that status until next round.
				foreach ($gameData["players"] as &$p)
				{
					$p["hit"] = false;
				}
				
				$dealer_hand = "";
				foreach($gameData["dealer"]["hand"] as &$card)
				{
					$dealer_hand .= $card['face'] . " ";
				}
				$dealer_hand = "`".$dealer_hand."`".($gameData["dealer"]['bust']===true ? " (bust)" : "") . ($gameData["dealer"]['stand']===true ? " (standing)" : "");
				
				$player_hands = "";
				foreach($gameData["players"] as $pkey => &$pvalue)
				{
					//Skip players not playing in this round.
					if ($pvalue['state'] != BlackjackPlayerState::Playing && $pvalue['state'] != BlackjackPlayerState::Leaving) continue;
					
					$playerData = $user->getUserById($pkey);
					$player_hands .= "\n".$playerData[0]['username'].": `";
					foreach ($pvalue["hand"] as &$card)
					{
						$player_hands .= " " . $card['face'];
					}
					$player_hands .= "`" .($pvalue['bust']===true ? " (bust)" : "") . ($pvalue['stand']===true ? " (standing)" : "");
				}
				
				//Dealer reveals hidden card.
				$revealMessage = "";
				if ($gameData["state"] == BlackjackState::PlayingOpening)
				{	
					$revealMessage = "Dealer reveals their hidden card!\n";
				}
				
				//Set game state.
				$gameData["state"] = BlackjackState::PlayingNextCard;
				
				//Update round end time.
				$gameData["roundStartTime"] = time() + self::ROUND_TIME_SECONDS;
				
				//Display all hands.
				$messageText .= "{$revealMessage}Player hands are:\nGameBot (Dealer): {$dealer_hand} {$player_hands}";
				
				//Dealer takes a new card, if score is 16 or under.
				$dealerHitStandMessage = "";
				if ($this->calcScore($gameData["dealer"]["hand"], "min") <= 16)
				{
					//Dealer hits.
					
					$gameData["dealer"]["hit"] = true;
					
					//Add a card to dealer hand.
					if (count($gameData["available_cards"]) == 0) $gameData["available_cards"] = $this->cards;
					$entry_key = array_rand($gameData["available_cards"]);
					$entry_value = $gameData["available_cards"][$entry_key];
					$gameData["dealer"]["hand"][] = array("face" => $entry_key, "value" => $entry_value);
					unset($gameData["available_cards"][$entry_key]);
					if (count($gameData["available_cards"]) == 0) $gameData["available_cards"] = $this->cards;

					//Show new dealer hand.
					$dealer_hand = "";
					foreach($gameData["dealer"]["hand"] as &$card)
					{
						$dealer_hand .= $card['face'] . " ";
					}
					
					$dealerHitStandMessage = "\nDealer takes a card. Their hand is now `{$dealer_hand}`\n";
					
					if ($this->calcScore($gameData["dealer"]["hand"], "min") > 21)
					{
						$gameData["dealer"]["bust"] = true;
						$dealerHitStandMessage .= "Dealer is bust!\n";
					} else if ($this->calcScore($gameData["dealer"]["hand"], "min") > 16)
					{
						$gameData["dealer"]["stand"] = true;
						$dealerHitStandMessage .= "Dealer stands!\n";
					}
				}
				else if ($gameData["dealer"]["stand"]!==true)
				{
					//Dealer stands.
					
					$gameData["dealer"]["stand"] = true;
					
					$dealerHitStandMessage = "\nDealer stands!\n";
					
				}
				
				if ($dealerHitStandMessage) $messageText .= $dealerHitStandMessage;
				
				//TODO: 
				
				if ($gameData["state"] != BlackjackState::PlayingEnded) 
				{
					//Report when card taking will end.
					$messageText .= "Hit or stand ends in " . self::ROUND_TIME_SECONDS . " seconds. Type \"/g hit\" to take a card, or \"/g stand\" to stand.";
				}
				
				$message->create($gameData['channel_id'], $messageText, false, $gameBot[0]['user_id']);
			}
			
			//END AND SCORE ROUND
			if ($gameData["state"] == BlackjackState::PlayingEnded && count($gameData["players"]) >= self::MIN_PLAYERS && time() > $gameData["roundStartTime"])
			{
				require_once('../app/models/GameModel.php');
				$game = new GameModel();
				
				$gameData["state"] = BlackjackState::Begin;
				
				$gameData["roundStartTime"] = time() + self::ROUND_TIME_SECONDS;
				
				$messageText = "***Blackjack***\nRound {$gameData["round"]} ended. Now scoring the round.\n";
				
				$winners = "";
				
				if ($gameData["dealer"]["bust"])
				{	
					//DEALER BUST
			
					$messageText .= "The dealer is bust!\n";
					$points = 5;
					foreach($gameData["players"] as $pkey => &$pvalue)
					{
						//Skip players not playing in this round.
						if ($pvalue['state'] != BlackjackPlayerState::Playing && $pvalue['state'] != BlackjackPlayerState::Leaving) continue;
						
						$playerData = $user->getUserById($pkey);
						$username = $playerData[0]['username'];
						
						if (!$pvalue['bust']) 
						{
							$winners .= $username . " BEATS dealer. Gained +♖{$points}.\n";
							$game->changePoints($pkey, $points);
						}
						else if ($pvalue['bust']) 
						{
							$winners .= $username . " is BUST. Lost -♖{$points}.\n";
							$game->changePoints($pkey, -$points);
						}
					}
				}
				else
				{

					//DEALER NOT BUST
				
					$dealerScore = $this->calcScore($gameData['dealer']['hand'], "max");
					
					$messageText .= "The dealer's hand is worth {$dealerScore}.\n";
					
					$points = 5;
					foreach($gameData["players"] as $pkey => &$pvalue)
					{
						//Skip players not playing in this round.
						if ($pvalue['state'] != BlackjackPlayerState::Playing && $pvalue['state'] != BlackjackPlayerState::Leaving) continue;
						
						$playerData = $user->getUserById($pkey);
						$username = $playerData[0]['username'];
						
						$playerMaxScore = $this->calcScore($pvalue['hand'], "max");
						$playerMinScore = $this->calcScore($pvalue['hand'], "min");
						if ($pvalue["bust"])
						{
							$winners .= $username . " is BUST (hand worth {$playerMinScore}). Lost -♖{$points}.\n";
							$game->changePoints($pkey, -$points);
						}
						else if ($playerMaxScore > $dealerScore) 
						{
							$winners .= $username . " BEATS dealer (hand worth {$playerMaxScore}). Gained +♖{$points}.\n";
							$game->changePoints($pkey, $points);
						}
						else
						{
							$winners .= $username . " LOSES to dealer (hand worth {$playerMaxScore}). Lost -♖{$points}.\n";
							$game->changePoints($pkey, -$points);
						}
					}
				}
				
				$messageText .= "{$winners}The next round begins in " . self::ROUND_TIME_SECONDS . " seconds. Type \"/g join\" to join the game, or \"/g leave\" to leave it.";
				
				$message->create($channel_id, $messageText, false, $gameBot[0]['user_id']);
				
				$gameData["round"]++;
			}
		}
		
		return $gameData;
	}
	
	/**
	* Calculate the score of a hand of Blackjack.
	* @param $hand An array reprsentation of the player's hand of cards.
	* @param $scoreType The scoring method - one of "min" (the lowest possible score) or "max" (the highest possible score under 21, or the lowest score over 21, whichever is higher).
	* @returns The hand score.
	*/
	private function calcScore($hand, $scoreType)
	{
		$score = 0;
		switch($scoreType)
		{
			//Minimum score from hand.
			case 'min':
				foreach($hand as &$card)
				{
					$score += min($card['value']);
				}
				break;
			//Maximum score not over 21 for hand.
			case 'max':
				$high_score = 0;
				//Cycle through random combinations of all possible card values.
				//The best scores are detected and recorded.
				for ($i = 0; $i <= 1000; $i++)
				{
					$tmp_score = 0;
					foreach($hand as &$card)
					{
						$tmp_score += $card['value'][array_rand($card['value'])];
					}
					if ($tmp_score > $high_score) $high_score = $tmp_score;
					if ($tmp_score > $score && $tmp_score <= 21)
					{
						$score = $tmp_score;
					}
				}
				if ($score == 0) $score = $high_score;
				break;
			default:
				App::error("Unknown score calculation type.");
		}
		return $score;
	}
}

?>