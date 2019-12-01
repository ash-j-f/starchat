<?php

/**
* Channel controller.
* Provides all channel related functions.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

class Channel extends Controller 
{
	
	/**
	* Default method.
	* Display all channels list.
	* @returns void.
	*/
	public function index()
	{
		//Default view for channel controller.
		$this->viewall();
	}
	
	/**
	* Display all channels list.
	* @param $sort_order the sort order for the channels list. See Channel model getChannels method for acceptable values.
	* @returns void.
	*/
	public function viewall($sort_order = "")
	{
		App::checkIsLoggedIn();
		
		$channel = $this->model('ChannelModel');
		
		$list = $channel->getChannels(false, $sort_order);
		
		$member = $this->model('MemberModel');
		
		$isSU = ($member->isSuperuser($_SESSION['user_id']) ? "yes" : "no");
		
		$this->view('channels', ["channels" => $list, "sort_order"=>$sort_order, "is_superuser"=>$isSU]);
	}
	
	/**
	* Display new channel form or edit channel form.
	* @param $mode Error mode (optional).
	* @param $errorType Error type to display (if there were errors in the data input from the user) (optional).
	* @returns void.
	*/
	public function new($mode = "", $errorType = "")
	{
		App::checkIsLoggedIn();
		
		$this->view('channelnew', ["error" => $errorType]);
	}
	
	/**
	* Display form to edit an existing channel's details.
	* @param $channel_id ID of the channel to edit.
	* @param $mode Error mode (optional).
	* @param $errorType Error type to display (if there were errors in the data input from the user) (optional).
	* @returns void.
	*/
	public function edit($channel_id, $mode = "", $errorType = "")
	{
		App::checkIsLoggedIn();
		
		//Check user has permission to edit that channel.
		$channel = $this->model('ChannelModel');
		
		$channelData = $channel->getChannelById($channel_id);
		
		$member = $this->model('MemberModel');
		
		if (!$channelData || !isset($channelData[0]['channel_id']) || ($channelData[0]['owner_id'] != $_SESSION['user_id'] && !$member->checkRole($channel_id, $_SESSION['user_id'], 'admin') && !$member->isSuperuser($_SESSION['user_id']))) App::error("You do not have permission to edit that channel.");
		
		if ($channelData[0]['deleted'] == 't') App::error("Cannot edit a deleted channel.");
		
		$this->view('channelnew', ["channel_data" => $channelData, "error" => $errorType]);
	}
	
	/**
	* Edit an existing channel's details and save changes.
	* @param $channel_id ID of the channel to edit.
	* @returns void.
	*/
	public function save($channel_id)
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		//Check user has permission to edit that channel.
		$channel = $this->model('ChannelModel');
		
		$channelData = $channel->getChannelById($channel_id);
		
		$member = $this->model('MemberModel');
		
		if (!$channelData || !isset($channelData[0]['channel_id']) || ($channelData[0]['owner_id'] != $_SESSION['user_id'] && !$member->checkRole($channel_id, $_SESSION['user_id'], 'admin') && !$member->isSuperuser($_SESSION['user_id']))) App::error("You do not have permission to edit that channel.");
		
		if ($channelData[0]['deleted'] == 't') App::error("Cannot edit a deleted channel.");
		
		$channelName = $_POST['channelname'];
		$channelDescription = $_POST['channeldesc'];
		$public = isset($_POST['public']) ? $_POST['public'] : "";
		$owner_id = $channelData[0]['owner_id'];
		
		$channelName = trim($channelName);
		$channelDescription = trim($channelDescription);
		
		if (mb_strlen($channelName) > 48) App::error("Channel name too long.");
		if (mb_strlen($channelDescription) > 128) App::error("Channel description too long.");
		
		$status = $channel->edit($channel_id, $channelName, $channelDescription, $public);
		
		//If channel edit succeeds, go to channel.
		if ($status == "OK")
		{
			header("Location: /chat/".$channelData[0]['channel_id']);
			exit;
		}
		
		//Check status error.
		switch ($status)
		{
			case 'invalid_channelname':
				$error = "invalid_channelname";
				break;
			case 'channelname_used':
				$error = "channelname_used";
				break;
			default:
				$error = "unknown";
				break;
		}
		
		//If channel creation fails, go back to creation page with an error message.
		header("Location: /channel/edit/".urlencode($channelData[0]['channel_id'])."/error/".$error."?channelname=".urlencode($channelName)."&channeldesc=".urlencode($channelDescription)."&channelpublic=". ($public=='t' ? "t" : ""));
		exit;
	}
	
	/**
	* Delete a channel.
	* @param $channel_id ID of the channel to delete.
	* @returns void.
	*/
	public function delete($channel_id)
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		$channel_id = $channel_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID");
		
		$channel = $this->model('ChannelModel');
		
		$channelData = $channel->getChannelById($channel_id);
		
		if (!$channelData || $channelData[0]['channel_id'] != $channel_id) App::error("Invalid channel ID.");
		
		$member = $this->model('MemberModel');
		
		if ($channelData[0]['owner_id'] != $_SESSION['user_id'] && !$member->isSuperuser($_SESSION['user_id'])) App::error("You do not have permission to delete that channel.");
		
		if ($channelData[0]['deleted'] == 't') App::error("Channel already deleted.");
		
		$channel->delete($channel_id);
		
		header("Location: /channel/viewall");
		exit;
	}
	
	/**
	* Create a channel.
	* @returns void.
	*/
	public function create()
	{
		
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		$channel = $this->model('ChannelModel');
		
		$channelName = $_POST['channelname'];
		$channelDescription = $_POST['channeldesc'];
		$public = isset($_POST['public']) ? $_POST['public'] : '';
		$owner_id = $_SESSION['user_id'];
		
		$channelName = trim($channelName);
		$channelDescription = trim($channelDescription);
		
		if (mb_strlen($channelName) > 48) App::error("Channel name too long.");
		if (mb_strlen($channelDescription) > 128) App::error("Channel description too long.");
		
		$status = $channel->create($channelName, $channelDescription, $public, $owner_id);
		
		//If channel creation succeeds, go to channel.
		if ($status == "OK")
		{
			//Get new channel ID.
			$channelData = $channel->getChannelByName($channelName);
			
			$member = $this->model('MemberModel');
			
			//Add owner as channel member.
			$member->createOrUpdateMember($channelData[0]['channel_id'], $owner_id, 'regular');
			
			header("Location: /chat/".$channelData[0]['channel_id']);
			exit;
		}
		
		//Check status error.
		switch ($status)
		{
			case 'invalid_channelname':
				$error = "invalid_channelname";
				break;
			case 'channelname_used':
				$error = "channelname_used";
				break;
			default:
				$error = "unknown";
				break;
		}
		
		//If channel creation fails, go back to creation page with an error message.
		header("Location: /channel/new/error/".$error."?channelname=".urlencode($channelName)."&channeldesc=".urlencode($channelDescription)."&channelpublic=". ($public=='t' ? "t" : ""));
		exit;
	}
	
	/**
	* Set owner of a channel.
	* @param $channel_id ID of the channel to edit.
	* @param $username Username of the user to set as owner.
	* @returns void.
	*/
	public function setownerbyname($channel_id, $username)
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		if ($channel_id < 1) App::error("Invalid channel ID.");
		
		$user = $this->model('UserModel');
		
		$userdata = $user->getUserByName($username);
		
		if (!$userdata || !isset($userdata[0]) || !isset($userdata[0]['user_id'])) App::error("Invalid username.");
		
		$this->setownerbyid($channel_id, $userdata[0]['user_id']);
		
	}
	
	/**
	* Set owner of a channel.
	* @param $channel_id ID of the channel to edit.
	* @param $user_id ID of the user to set as owner.
	* @returns void.
	*/
	public function setownerbyid($channel_id, $user_id)
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		if ($channel_id < 1) App::error("Invalid channel ID.");
		
		$user_id = $user_id * 1;
		if ($user_id < 1) App::error("Invalid user ID.");
		
		$channel = $this->model('ChannelModel');
		
		$channelData = $channel->getChannelById($channel_id);
		
		if (!$channelData || !isset($channelData[0]) || !isset($channelData[0]['channel_id']) || $channelData[0]['channel_id'] != $channel_id) App::error("Invalid channel ID.");
		
		$member = $this->model('MemberModel');
		
		if ($_SESSION['user_id'] != $channelData[0]['owner_id'] && !$member->isSuperuser($_SESSION['user_id'])) App::error("You do not have permission to set owner for this channel.");
		
		if ($user_id == $channelData[0]['owner_id']) App::error("That user is already the channel owner.");
		
		if ($user_id == $_SESSION['user_id'] && !$member->isSuperuser($_SESSION['user_id'])) App::error("Cannot set yourself channel owner.");
	
		$user = $this->model('UserModel');
		
		//Check that user id is valid.
		if (!$user->checkUser($user_id)) App::error("Invalid user ID.");
		
		//Prevent users from manipulating system accounts.
		if ($member->checkIfSystemAccount($user_id)) App::error("Cannot set owner to a system account.");
		
		//If previous owner is a member of the channel, remove any roles they have.
		if ($member->checkIfMember($channel_id, $channelData[0]['owner_id']))
		{
			$member->createOrUpdateMember($channel_id, $channelData[0]['owner_id'], 'regular');
		}
		
		//Set new channel owner.
		$channel->setChannelOwner($channel_id, $user_id);
		
		echo "OK";
		
	}
}

?>
