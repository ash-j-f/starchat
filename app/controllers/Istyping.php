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
* Istyping controller.
* Records a user's "is typing" status. As a user starts typing, the chat window sends an AJAX request to this controller to notify
* all other users that this user has started typing.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
*/

class Istyping extends Controller 
{
	/**
	* Default method for this controller.
	* Records a user's "is typing" status.
	* @returns void.
	*/
	public function index($channel_id="")
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
		if ($channelData && isset($channelData[0]) && $channelData[0]['public'] != 't' && $channelData[0]['owner_id'] != $_SESSION['user_id'] && !$member->checkIfMember($channel_id, $_SESSION['user_id'])) App::error("You are not a member of this private channel.");
		
		//Check user has permission to post in this channel.
		$channelData = $channel->getChannelById($channel_id);
		
		if (!$channelData || !isset($channelData[0]['channel_id'])) App::error("Invalid channel ID.");
		
		if ($channelData[0]['deleted'] == 't') App::error("Channel has been deleted.");
		
		$message = $this->model('MessageModel');
		
		$member->updateIsTyping($channel_id, $_SESSION['user_id']);
		
		//Update user's "last seen" time in this channel.
		$member->updateLastSeen($channel_id, $_SESSION['user_id']);
		
		echo "OK";
	}
	
}

?>
