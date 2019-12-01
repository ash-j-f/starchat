<?php

/**
* Member controller.
* Provides functions relating to members such as retrieving a channel's member list, adding or removing channel members, etc.
* Also privides channel administrative functions such as banning and unbanning users, listing banned users, etc.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

class Member extends Controller 
{
	/**
	* Default method for this controller.
	* This controller should not be accessed by its default method.
	* @returns void.
	*/
	public function index()
	{
		App::error("This controller has no default output.");
	}
	
	/**
	* Cause the currently logged in user to leave a given channel.
	* @param $channel_id The ID of the channel to leave.
	* @param $mode string Leave blank for a normal page request (redirects to all channels page when done), set to "ajax" when using as an ajax request (echoes OK" when done).
	* @returns void.
	*/
    public function leave($channel_id, $mode="")
	{
        App::checkIsLoggedIn();
		
		//Sanitize channel ID.
		$channel_id = $channel_id * 1;
		if ($channel_id < 1) App::error("Invalid channel ID.");
		
		$channel = $this->model('ChannelModel');
		
		//Owner cannot leave their own channel.
		if ($channel->checkIfOwner($channel_id, $_SESSION['user_id'])) App::error("Owner cannot leave a channel they own.");
		
		$member = $this->model('MemberModel');
		
		$member->removeMember($channel_id, $_SESSION['user_id']);
		
		if ($mode=="ajax")
		{
			echo "OK";
			exit;
		}
		else
		{
			header("Location: /channel/viewall");
			exit;
		}
	}
	
	/**
	* Add a user to a channel, identifying them using their username.
	* @param $channel_id ID of the channel.
	* @param $username Username of the user.
	* @returns void.
	*/
	public function addbyname($channel_id, $username)
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		if ($channel_id < 1) App::error("Invalid channel ID.");
		
		$user = $this->model('UserModel');
		
		$userdata = $user->getUserByName($username);
		
		if (!$userdata || !isset($userdata[0]) || !isset($userdata[0]['user_id'])) App::error("Invalid username.");
		
		$this->addbyid($channel_id, $userdata[0]['user_id']);
		
	}
	
	/**
	* Add a user to a channel, identifying them using their user ID.
	* @param $channel_id ID of the channel.
	* @param $user_id ID of the user.
	* @returns void.
	*/
	public function addbyid($channel_id, $user_id)
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
		
		if ($channelData[0]['public']=='t') App::error("You can only add members to a private channel. This channel is public.");
		
		$member = $this->model('MemberModel');
		
		if ($_SESSION['user_id'] != $channelData[0]['owner_id'] && !$member->checkRole($channel_id, $_SESSION['user_id'], 'admin') && !$member->isSuperuser($_SESSION['user_id'])) App::error("You do not have permission to add users to this channel.");
		
		if ($user_id == $channelData[0]['owner_id']) App::error("Cannot add channel owner.");
		
		if ($user_id == $_SESSION['user_id']) App::error("Cannot add yourself.");
		
		//Prevent users from manipulating system accounts.
		if ($member->checkIfSystemAccount($user_id)) App::error("Cannot add system accounts.");
		
		//Can't add member already in channel.
		if ($member->checkIfMember($channel_id, $user_id)) App::error("That user is already a member of this channel.");
		
		//Can't add a banned user.
		if ($member->checkIfBanned($channel_id, $user_id)) App::error("That user is banned from this channel.");
		
		//Add member to the given channel.
		$member->createOrUpdateMember($channel_id, $user_id, 'regular');
		
		echo "OK";
		
	}
	
	/**
	* Get account details about a user. For use by Superuser accounts only.
	* @param $username Username of the user.
	* @returns void.
	*/
	public function details($username)
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		$member = $this->model('MemberModel');
		
		if (!$member->isSuperuser($_SESSION['user_id'])) App::error("Only superusers can access account details.");
		
		$user = $this->model('UserModel');
		
		$userdata = $user->getUserByName($username);
		
		if (!$userdata || !isset($userdata[0]) || !isset($userdata[0]['user_id'])) App::error("Invalid username.");
		
		$detailsArray = array("details"=>"Account '{$userdata[0]['username']}' with user ID {$userdata[0]['user_id']} and email '{$userdata[0]['email']}' created at {$userdata[0]['creation_date']}." . ($userdata[0]['deleted']=='t' ? " ACCOUNT HAS BEEN DELETED." : "") . ($userdata[0]['system_account']=='t' ? " SYSTEM ACCOUNT." : ""));
		
		echo json_encode($detailsArray);
	}
	
	/**
	* Force-set password on a user account. For use by Superuser accounts only.
	* @returns void.
	*/
	public function setpassword()
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
	
		$username = $_POST['username'];
		
		$password = $_POST['password'];
	
		$user = $this->model('UserModel');
		
		$userdata = $user->getUserByName($username);
		
		if (!$userdata || !isset($userdata[0]) || !isset($userdata[0]['user_id'])) App::error("Invalid username.");
	
		if (mb_strlen($password) < 8 || !$password) App::error("Password too short.");
	
		if (mb_strlen($password) > 128) App::error("Password too long.");
	
		$member = $this->model('MemberModel');
		
		//Only superusers can change passwords.
		if (!$member->isSuperuser($_SESSION['user_id'])) App::error("Only superusers can change account passwords.");
		
		//Set account password.
		$user->setPassword($userdata[0]['user_id'], $password);
		
		echo "OK";
		
	}
	
	/**
	* Set a member's "playing" status.
	* @returns void.
	*/
	public function playing()
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		//Limit string length.
		$gamename = substr($_POST['gamename'], 0, 24);
		
		//Strip carriage returns.
		$gamename = str_replace("\r\n", " ", $gamename);
		$gamename = str_replace("\n", " ", $gamename);
		
		$user = $this->model('UserModel');
		
		//Set playing status.
		$user->setPlaying($_SESSION['user_id'], $gamename);
		
		echo "OK";
		
	}
	
	/**
	* Undelete an account identified by username. Usable only by Superusers.
	* @param $username The username of the account to undelete.
	* @returns void.
	*/
	public function undeletebyname($username)
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		$user = $this->model('UserModel');
		
		$userdata = $user->getUserByName($username);
		
		if (!$userdata || !isset($userdata[0]) || !isset($userdata[0]['user_id'])) App::error("Invalid username.");
		
		$this->undeletebyid($userdata[0]['user_id']);
		
	}
	
	/**
	* Undelete an account identified by user ID. Usable only by Superusers.
	* @param $user_id The ID of the account to undelete.
	* @returns void.
	*/
	public function undeletebyid($user_id)
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		//Sanitize.		
		$user_id = $user_id * 1;
		if ($user_id < 1) App::error("Invalid user ID.");
	
		$member = $this->model('MemberModel');
		
		if ($user_id == $_SESSION['user_id']) App::error("Cannot undelete yourself.");
		
		//Only superusers can delete other users.
		if (!$member->isSuperuser($_SESSION['user_id'])) App::error("Only superusers can undelete other users.");
		
		//Superusers cannot delete other superusers.
		if ($member->isSuperuser($user_id)) App::error("Cannot undelete another superuser account.");
		
		$user = $this->model('UserModel');
		
		//Check if user already deleted.
		if ($user->checkUser($user_id)) App::error("Can't undelete as user not currently deleted.");
		
		//UNmark user deleted.
		$user->undel($user_id);
		
		echo "OK";
		
	}
	
	/**
	* Delete an account identified by username. Usable only by Superusers.
	* @param $username The username of the account to delete.
	* @returns void.
	*/
	public function deletebyname($username)
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		$user = $this->model('UserModel');
		
		$userdata = $user->getUserByName($username);
		
		if (!$userdata || !isset($userdata[0]) || !isset($userdata[0]['user_id'])) App::error("Invalid username.");
		
		$this->deletebyid($userdata[0]['user_id']);
		
	}
	
	/**
	* Delete an account identified by user ID. Usable only by Superusers.
	* @param $user_id The ID of the account to delete.
	* @returns void.
	*/
	public function deletebyid($user_id)
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		//Sanitize.		
		$user_id = $user_id * 1;
		if ($user_id < 1) App::error("Invalid user ID.");
	
		$member = $this->model('MemberModel');
		
		if ($user_id == $_SESSION['user_id']) App::error("Cannot delete yourself.");
		
		//Only superusers can delete other users.
		if (!$member->isSuperuser($_SESSION['user_id'])) App::error("Only superusers can delete other users.");
		
		$user = $this->model('UserModel');
		
		//Check if user already deleted.
		if (!$user->checkUser($user_id)) App::error("User already deleted.");
		
		//Mark user deleted.
		$user->del($user_id);
		
		//Purge user from all channel memberships.
		$member->purge($user_id);
		
		echo "OK";
		
	}
	
	/**
	* Remove an account from a channel, identifying account by username.
	* @param $channel_id The ID of the channel.
	* @param $username The username of the account to remove from channel.
	* @returns void.
	*/
	public function removebyname($channel_id, $username)
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		if ($channel_id < 1) App::error("Invalid channel ID.");
		
		$user = $this->model('UserModel');
		
		$userdata = $user->getUserByName($username);
		
		if (!$userdata || !isset($userdata[0]) || !isset($userdata[0]['user_id'])) App::error("Invalid username.");
		
		$this->removebyid($channel_id, $userdata[0]['user_id']);
		
	}
	
	/**
	* Remove an account from a channel, identifying account by user ID.
	* @param $channel_id The ID of the channel.
	* @param $user_id The user ID of the account to remove from channel.
	* @returns void.
	*/
	public function removebyid($channel_id, $user_id)
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
		
		if ($channelData[0]['public']=='t') App::error("You can only remove members from a private channel. This channel is public.");
		
		$member = $this->model('MemberModel');
		
		if ($_SESSION['user_id'] != $channelData[0]['owner_id'] && !$member->checkRole($channel_id, $_SESSION['user_id'], 'admin') && !$member->isSuperuser($_SESSION['user_id'])) App::error("You do not have permission to remove users from this channel.");
		
		if ($user_id == $channelData[0]['owner_id']) App::error("Cannot remove channel owner.");
		
		if ($user_id == $_SESSION['user_id']) App::error("Cannot remove yourself.");
		
		//Admins can only remove mods and regular users from channel.
		if ($member->checkRole($channel_id, $_SESSION['user_id'], 'admin') && ((!$member->checkRole($channel_id, $user_id, 'mod') && !$member->checkRole($channel_id, $user_id, 'regular')) || $member->isSuperuser($user_id))) App::error("Admins can only remove moderators or regular users from a channel.");
		
		//Can't remove member not in channel.
		if (!$member->checkIfMember($channel_id, $user_id)) App::error("That user is not a member of this channel.");
		
		//Prevent users from manipulating system accounts.
		if ($member->checkIfSystemAccount($user_id)) App::error("Cannot remove system accounts.");
		
		//Remove member from the given channel.
		$member->removeMember($channel_id, $user_id);
		
		echo "OK";
		
	}
	
	/**
	* Ban an account from a channel, identifying account by username.
	* @param $channel_id The ID of the channel.
	* @param $username The username of the account to ban from channel.
	* @returns void.
	*/
	public function banbyname($channel_id, $username)
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		if ($channel_id < 1) App::error("Invalid channel ID.");
		
		$user = $this->model('UserModel');
		
		$userdata = $user->getUserByName($username);
		
		if (!$userdata || !isset($userdata[0]) || !isset($userdata[0]['user_id'])) App::error("Invalid username.");
		
		$this->banbyid($channel_id, $userdata[0]['user_id']);
		
	}
	
	/**
	* Ban an account from a channel, identifying account by user ID.
	* @param $channel_id The ID of the channel.
	* @param $user_id The user ID of the account to ban from channel.
	* @returns void.
	*/
	public function banbyid($channel_id, $user_id)
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
		
		if (!$member->isSuperuser($_SESSION['user_id']) && $_SESSION['user_id'] != $channelData[0]['owner_id'] && !$member->checkRole($channel_id, $_SESSION['user_id'], 'admin') && !$member->checkRole($channel_id, $_SESSION['user_id'], 'mod')) App::error("You do not have permission to ban users from this channel.");
		
		if ($user_id == $channelData[0]['owner_id']) App::error("Cannot ban channel owner.");
		
		if ($user_id == $_SESSION['user_id']) App::error("Cannot ban yourself.");
		
		//Prevent users from manipulating system accounts.
		if ($member->checkIfSystemAccount($user_id)) App::error("Cannot ban system accounts.");
		
		//Admins can only ban mods and regular users.
		if ($member->checkRole($channel_id, $_SESSION['user_id'], 'admin') && $member->checkIfMember($channel_id, $user_id) && ((!$member->checkRole($channel_id, $user_id, 'mod') && !$member->checkRole($channel_id, $user_id, 'regular')) || $member->isSuperuser($user_id)))
		{
			App::error("Admins can only ban regular users or moderators.");
		}
		
		//Mods can only ban regular users.
		if ($member->checkRole($channel_id, $_SESSION['user_id'], 'mod') && $member->checkIfMember($channel_id, $user_id) && (!$member->checkRole($channel_id, $user_id, 'regular') || $member->isSuperuser($user_id)))
		{
			App::error("Moderators can only ban regular users.");
		}
		
		//Remove and ban the given member from the given channel.
		$member->removeMember($channel_id, $user_id);
		$member->ban($channel_id, $user_id);
		
		//Return list of current channel members.
		$memberData = $member->getMembersByChannel($channel_id);
		
		//Return member data.
		if ($memberData && isset($memberData[0]) && isset($memberData[0]['member_id']))
		{
			echo json_encode($memberData);
		}
		else
		{
			echo "{}";
		}
		
	}
	
	/**
	* Unban an account from a channel, identifying account by username.
	* @param $channel_id The ID of the channel.
	* @param $username The username of the account to unban from channel.
	* @returns void.
	*/
	public function unbanbyname($channel_id, $username)
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		if ($channel_id < 1) App::error("Invalid channel ID.");
		
		$user = $this->model('UserModel');
		
		$userdata = $user->getUserByName($username);
		
		if (!$userdata || !isset($userdata[0]) || !isset($userdata[0]['user_id'])) App::error("Invalid username.");
		
		$this->unbanbyid($channel_id, $userdata[0]['user_id']);
		
	}
	
	/**
	* Unban an account from a channel, identifying account by user ID.
	* @param $channel_id The ID of the channel.
	* @param $user_id The user ID of the account to unban from channel.
	* @returns void.
	*/
	public function unbanbyid($channel_id, $user_id)
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
		
		if ($_SESSION['user_id'] != $channelData[0]['owner_id'] && !$member->checkRole($channel_id, $_SESSION['user_id'], 'admin') && !$member->checkRole($channel_id, $_SESSION['user_id'], 'mod') && !$member->isSuperuser($_SESSION['user_id'])) App::error("You do not have permission to unban users from this channel.");
		
		if ($user_id == $channelData[0]['owner_id']) App::error("Cannot unban channel owner.");
		
		if ($user_id == $_SESSION['user_id']) App::error("Cannot unban yourself.");
		
		//Check if this user is banned from this channel. Return error if not.
		if (!$member->checkIfBanned($channel_id, $user_id)) App::error("User is not banned from this channel.");
		
		//Prevent users from manipulating system accounts.
		if ($member->checkIfSystemAccount($user_id)) App::error("Cannot unban system accounts.");
		
		//Unban member from the given channel.
		$member->unban($channel_id, $user_id);
		
		echo "OK";
		
	}
	
	/**
	* List all banned members for a given channel and echo as JSON string.
	* @param $channel_id The ID of the channel.
	* @returns void.
	*/
	public function listbans($channel_id)
	{
		
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		//Sanitize channel ID.
		$channel_id = $channel_id * 1;
		if ($channel_id < 1) App::error("Invalid channel ID.");
		
		$channel = $this->model('ChannelModel');
		
		$channelData = $channel->getChannelById($channel_id);
		
		if (!$channelData || !isset($channelData[0]) || !isset($channelData[0]['channel_id']) || $channelData[0]['channel_id'] != $channel_id) App::error("Invalid channel ID.");
		
		$member = $this->model('MemberModel');
		
		if ($channelData[0]['owner_id'] != $_SESSION['user_id'] && !$member->checkRole($channel_id, $_SESSION['user_id'], 'admin') && !$member->checkRole($channel_id, $_SESSION['user_id'], 'mod') && !$member->isSuperuser($_SESSION['user_id'])) App::error("You do not have permission to list banned members for this channel.");
		
		$memberData = $member->getBannedMembersByChannel($channel_id);
		
		//Return member data.
		if ($memberData && isset($memberData[0]) && isset($memberData[0]['user_id']))
		{
			echo json_encode($memberData);
		}
		else
		{
			echo "{}";
		}
	}
	
	/**
	* Get all members for a given channel and echo as JSON string.
	* @param $channel_id int The ID of the channel.
	* @param $mode string The listing mode. Blank for all members. "rolesonly" to 
	* return only members that have a role set other than regular member.
	* @returns void.
	*/
	public function channel($channel_id, $mode="")
	{
		
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		//Sanitize channel ID.
		$channel_id = $channel_id * 1;
		if ($channel_id < 1) App::error("Invalid channel ID.");
		
		$member = $this->model('MemberModel');
		
		//Check if we are banned from that channel.
		if ($member->checkIfBanned($channel_id, $_SESSION['user_id'])) App::error("You have been banned from this channel.");
		
		$channel = $this->model('ChannelModel');
		
		//Check if we are member of private channel. Superusers can access private channels even when not a member.
		$channelData = $channel->getChannelById($channel_id, false);
		if ($channelData && isset($channelData[0]) && $channelData[0]['public'] != 't' && $channelData[0]['owner_id'] != $_SESSION['user_id'] && !$member->checkIfMember($channel_id, $_SESSION['user_id']) && !$member->isSuperuser($_SESSION['user_id'])) App::error("You are not a member of this private channel.");
		
		$channelData = $channel->getChannelById($channel_id);
		
		if (!$channelData || !isset($channelData[0]) || !isset($channelData[0]['channel_id']) || $channelData[0]['channel_id'] != $channel_id) App::error("Invalid channel ID.");
		
		//Check user is member of channel. If not, create a new regular member entry.
		$memberData = $member->getMemberByChannelAndUser($channel_id, $_SESSION['user_id']);
		
		if (!$memberData || !isset($memberData[0]) || !isset($memberData[0]['member_id']))
		{
			$member->createOrUpdateMember($channel_id, $_SESSION['user_id'], 'regular');
		}
		
		$memberData = $member->getMembersByChannel($channel_id, $mode=="rolesonly");
		
		//Return member data.
		if ($memberData && isset($memberData[0]) && isset($memberData[0]['member_id']))
		{
			echo json_encode($memberData);
		}
		else
		{
			echo "{}";
		}
		
	}
	
	/**
	* Set role of an account in a channel, identifying account by username.
	* @param $channel_id The ID of the channel.
	* @param $username The username of the account.
	* @param $role The role name to set.
	* @returns void.
	*/
	public function rolebyname($channel_id, $username, $role)
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		if ($channel_id < 1) App::error("Invalid channel ID.");
		
		if (!in_array($role, array('admin', 'mod', 'none', 'regular'))) App::error("Invalid role type.");
		
		//Role "none" is an alias for "regular".
		if ($role == "none") $role = "regular";
		
		$user = $this->model('UserModel');
		
		$userdata = $user->getUserByName($username);
		
		if (!$userdata || !isset($userdata[0]) || !isset($userdata[0]['user_id'])) App::error("Invalid username.");
		
		$this->rolebyid($channel_id, $userdata[0]['user_id'], $role);
		
	}
	
	/**
	* Set role of an account in a channel, identifying account by user ID.
	* @param $channel_id The ID of the channel.
	* @param $user_id The user ID of the account.
	* @param $role The role name to set.
	* @returns void.
	*/
	public function rolebyid($channel_id, $user_id, $role)
	{
		App::checkIsLoggedIn();
		
		App::checkToken();
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		if ($channel_id < 1) App::error("Invalid channel ID.");
		
		$user_id = $user_id * 1;
		if ($user_id < 1) App::error("Invalid user ID.");
		
		if (!in_array($role, array('admin', 'mod', 'none', 'regular'))) App::error("Invalid role type.");
		
		//Role "none" is an alias for "regular".
		if ($role == "none") $role = "regular";
		
		$channel = $this->model('ChannelModel');
		
		$channelData = $channel->getChannelById($channel_id);
		
		if (!$channelData || !isset($channelData[0]) || !isset($channelData[0]['channel_id']) || $channelData[0]['channel_id'] != $channel_id) App::error("Invalid channel ID.");
		
		$member = $this->model('MemberModel');
		
		//Only channel owner and channel admins can set roles.
		if ($_SESSION['user_id'] != $channelData[0]['owner_id'] && !$member->checkRole($channel_id, $_SESSION['user_id'], 'admin') && !$member->isSuperuser($_SESSION['user_id'])) App::error("You do not have permission to set roles for this channel.");
		
		if ($user_id == $channelData[0]['owner_id']) App::error("Cannot set roles of channel owner.");
		
		if ($user_id == $_SESSION['user_id']) App::error("Cannot set roles for yourself.");
		
		//Check that user is a member of this channel.
		if (!$member->checkIfMember($channel_id, $user_id)) App::error("That user is not a member of this channel.");
		
		//Prevent users from manipulating system accounts.
		if ($member->checkIfSystemAccount($user_id)) App::error("Cannot change roles for system accounts.");
		
		//Prevent admins from changing role of anyone but regular users or moderators.
		if ($member->checkRole($channel_id, $_SESSION['user_id'], 'admin') && ((!$member->checkRole($channel_id, $user_id, 'mod') && !$member->checkRole($channel_id, $user_id, 'regular')) || $member->isSuperuser($user_id)))
		{
			App::error("Admins can only change roles for moderators or regular users.");
		}
		
		//Only allow admins to set the mod role.
		if ($member->checkRole($channel_id, $_SESSION['user_id'], 'admin') && $role != "mod" && $role != "regular")
		{
			App::error("Admins can only assign or remove the moderator role.");
		}
		
		//Set role for user.
		$member->createOrUpdateMember($channel_id, $user_id, $role);
		
		echo "OK";
		
	}
	
}

?>
