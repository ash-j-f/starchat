<?php

/**
* Message controller.
* Provides message functions such as retrieiving latest messages for a channel, and posting messages.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

class Message extends Controller 
{
	/**
	* Default method.
	* This controller should not be accessed by its default method.
	* @returns void.
	*/
	public function index()
	{
		App::error("This controller has no default output.");
	}
	
	/**
	* Post a message to a channel.
	* @param $channel_id The ID of the channel.
	* @returns void.
	*/
	public function post($channel_id = "")
	{
		
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		$channel_id = $channel_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID");
		
		$member = $this->model('MemberModel');
		
		//Check if we are banned from that channel.
		if ($member->checkIfBanned($channel_id, $_SESSION['user_id'])) App::error("You have been banned from this channel.");
		
		$channel = $this->model('ChannelModel');
		
		//Check if we are member of private channel.
		$channelData = $channel->getChannelById($channel_id, false);
		if ($channelData && isset($channelData[0]) && $channelData[0]['public'] != 't' && $channelData[0]['owner_id'] != $_SESSION['user_id'] && !$member->checkIfMember($channel_id, $_SESSION['user_id']) && !$member->isSuperuser($_SESSION['user_id'])) App::error("You are not a member of this private channel.");
		
		$message_text = null;
		$is_stream = false;
		$stream_name = null;
		$delay = null;
		$delay_type = null;
		
		if (isset($_POST['streamname']) && $_POST['streamname'] != "")
		{
			$is_stream = true;
			
			$stream_name = $_POST['streamname'];
			$delay = trim($_POST['delay']);
			$delay_type = strtolower(trim($_POST['delaytype']));
			
			if (mb_strlen($stream_name) > 128) App::error("Stream account name too long.");
			if (!is_numeric($delay) || $delay < 0) App::error("Invalid delay number. Delay must be a positive numeric value, or zero.");
			if (!in_array(strtolower($delay_type), array("hour", "hours", "minute", "minutes"))) App::error("Invalid delay type. It must be one of \"hour\",\"hours\",\"minute\" or \"minutes\".");
			
			//Fix correct plural.
			if ($delay == 1)
			{
				if ($delay_type == "minutes") $delay_type = "minute";
				if ($delay_type == "hours") $delay_type = "hour";
			}
			else
			{
				if ($delay_type == "minute") $delay_type = "minutes";
				if ($delay_type == "hour") $delay_type = "hours";
			}
			
			$delay_text = $delay > 0 ? " in {$delay} {$delay_type}." : " now!";
			
			$message_text = "STREAMING at https://twitch.tv/{$stream_name}{$delay_text}";
		}
		else
		{
			$message_text = trim($_POST['usermessage']);
		}
		
		if (!$message_text || $message_text == "") App::error("Cannot post a blank message.");
		
		if (mb_strlen($message_text) > 1024) App::error("Message too long.");
		
		//Check user has permission to post in this channel.
		$channelData = $channel->getChannelById($channel_id);
		
		if (!$channelData || !isset($channelData[0]['channel_id'])) App::error("Invalid channel ID.");
		
		if ($channelData[0]['deleted'] == 't') App::error("Channel has been deleted.");
		
		$member = $this->model('MemberModel');
		
		if ($channelData[0]['owner_id'] != $_SESSION['user_id'] && $channelData[0]['public'] != 't' && !$member->checkIfMember($channel_id, $_SESSION['user_id'])) App::error("You do not have permission to post a message to that channel.");
		
		//Check user is member of channel. If not, create a new regular member entry.
		$memberData = $member->getMemberByChannelAndUser($channel_id, $_SESSION['user_id']);
		
		if (!$memberData || !isset($memberData[0]) || !isset($memberData[0]['member_id']))
		{
			$member->createOrUpdateMember($channel_id, $_SESSION['user_id'], 'regular');
		}
		
		$message = $this->model('MessageModel');
		
		$messageData = $message->create($channel_id, $message_text, false, 0, $stream_name, $delay, $delay_type);
		
		//Update user's "last seen" time in this channel.
		$member->updateLastSeen($channel_id, $_SESSION['user_id']);
		
		if ($messageData)
		{
			echo json_encode($messageData);
		}
		else
		{
			echo "{}";
		}
	}
	
	/**
	* Get recent messages for a channel. Includes information on users currently typing. Echoes data as JSON string.
	* @param $channel_id The ID of the channel.
	* @returns void.
	*/
	public function receive($channel_id = "")
	{
		
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		//////////////////
		//GAME PROCESS.
		//Process any active games in this chat. This saves having to have a scheduled task running on the server.
		$game = $this->model('GameModel');
		$game->processGame($channel_id);
		//////////////////
		
		$message = $this->model('MessageModel');
		
		//////////////////
		//STREAM ADVERTISING PROCESS.
		//Process any streams that need to be re-posted as they have hit their advertising time.
		$message->advertiseOutstandingStreams($channel_id);
		//////////////////
		
		//Sanitize channel ID.
		$channel_id = $channel_id * 1;
		if (!$channel_id || $channel_id < 1) App::error("Invalid channel ID.");
		
		$member = $this->model('MemberModel');
		
		//Check if we are banned from that channel.
		if ($member->checkIfBanned($channel_id, $_SESSION['user_id'])) App::error("You have been banned from this channel.");
		
		$channel = $this->model('ChannelModel');
		
		//Check if we are member of private channel.
		$channelData = $channel->getChannelById($channel_id, false);
		if ($channelData && isset($channelData[0]) && $channelData[0]['public'] != 't' && $channelData[0]['owner_id'] != $_SESSION['user_id'] && !$member->checkIfMember($channel_id, $_SESSION['user_id']) && !$member->isSuperuser($_SESSION['user_id'])) App::error("You are not a member of this private channel.");
		
		//Check if user has permission to access this channel ID, and that ID is valid.
		$channelData = $channel->getChannelById($channel_id);
		
		if (!$channelData || !isset($channelData[0]) || !isset($channelData[0]['channel_id']) || $channelData[0]['channel_id'] != $channel_id) App::error("Invalid channel ID.");
		
		//Get members who are currently typing, excluding current logged in user.
		$typing = $member->getIsTyping($channelData[0]['channel_id'], $_SESSION['user_id']);
		
		//Check user is member of channel. If not, create a new regular member entry.
		$memberData = $member->getMemberByChannelAndUser($channel_id, $_SESSION['user_id']);
		
		if (!$memberData || !isset($memberData[0]) || !isset($memberData[0]['member_id']))
		{
			$member->createOrUpdateMember($channel_id, $_SESSION['user_id'], 'regular');
		}
		
		//Update user's "last seen" time in this channel.
		$member->updateLastSeen($channel_id, $_SESSION['user_id']);
		
		//Sanitise.
		$count = $_POST['count'] * 1;
		$lastRequestTime = $_POST['lastRequestTime'] * 1;
		
		$messages = $message->getMessages($channelData[0]['channel_id'], $count, $lastRequestTime);
		
		//Return message and typing data.
		if (!$messages) $messages = [];
		if (!$typing) $typing = [];
		echo json_encode(array('messages'=>$messages, 'members_typing'=>$typing));
		
	}
	
}

?>
