<?php

/**
* Chat controller.
* Loads the main Chat view.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

class Chat extends Controller 
{
	/**
	* Display chat view for a channel.
	* @param $channel_id ID of the channel to display.
	* @returns void.
	*/
	public function index($channel_id = "")
	{
		
		App::checkIsLoggedIn();
		
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
		
		//Check channel using permissions.
		$channelData = $channel->getChannelById($channel_id);
		
		if (!$channelData || !isset($channelData[0]) || !isset($channelData[0]['channel_id']) || $channelData[0]['channel_id'] != $channel_id) App::error("Invalid channel ID.");
		
		$isSU = ($member->isSuperuser($_SESSION['user_id']) ? "yes" : "no");
		
		$this->view('chat', ["channel_data" => $channelData, "is_superuser"=>$isSU]);
	}
	
}

?>