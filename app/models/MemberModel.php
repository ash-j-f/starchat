<?php

/**
* Member model.
* Provides functions for the manipulation of Member data.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

include_once '../app/core/DB.php';

class MemberModel
{
	/**
	* Create or update a member for a given channel.
	* @param $channel_id The channel ID.
	* @param $user_id The user ID to update or add as a member to the channel.
	* @param $role User role to assign to this user in the channel: one of "admin", "mod" or "regular".
	* @returns The member ID of the created or updated entry.
	*/
	public function createOrUpdateMember($channel_id, $user_id, $role)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		$user_id = $user_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID");
		if ($user_id < 1) App::error("Invalid user ID");
		if (!in_array($role, array('admin', 'mod','regular'))) App::error("Invalid role passed to MemberModel.");
		
		$DB = new DB();
		
		$result = $DB->query("select m.member_id from members m where m.channel_id = $1 and m.user_id = $2", $channel_id, $user_id);
		
		if ($result && isset($result[0]) && isset($result[0]['member_id']))
		{
			$result = $DB->query("update members set role = $1 where channel_id = $2 and user_id = $3", $role, $channel_id, $user_id);
			
			return $result[0]['member_id'];
		}
		else
		{
			
			//Get next member id.
			$arr = $DB->query("select nextval('members_member_id_seq'::regclass)");
			$next_id = $arr[0]['nextval'];
		
			if (!is_numeric($next_id) || $next_id * 1 < 1) App::error("Cannot get next available mmeber id from database when creating member entry.");
		
			//Insert new member entry.
			$DB->query("insert into members (member_id, channel_id, user_id, role) values($1, $2, $3, $4);", $next_id, $channel_id, 
			$user_id, $role);
			
			//Insert "joined channel" message.
			require_once '../app/models/MessageModel.php';
			$message = new MessageModel();
			$result = $DB->query("select u.username from users u where u.user_id = $1", $user_id);
			$message->create($channel_id, $result[0]['username'] . " has joined the channel.", true);
			
			return $next_id;	
		}
		
	}
	
	/**
	* Purge user memeberships for all channels.
	* @param $user_id The user ID to purge.
	* @returns void.
	*/
	public function purge($user_id)
	{
		//Sanitize.
		$user_id = $user_id * 1;
		
		if ($user_id < 1) App::error("Invalid user ID");
		
		$DB = new DB();
		
		$DB->query("delete from members where user_id = $1", $user_id);
	}
	
	/**
	* Remove a member from a given channel.
	* @param $channel_id The channel ID to remove the user from.
	* @param $user_id The user ID to remove.
	* @returns void.
	*/
	public function removeMember($channel_id, $user_id)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		$user_id = $user_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID");
		if ($user_id < 1) App::error("Invalid user ID");
		
		$DB = new DB();
		
		$DB->query("delete from members where channel_id = $1 and user_id = $2;", $channel_id, $user_id);
	}
	
	/**
	* Get member data for user $user_id in channel $channel_id.
	* @param $channel_id The channel ID.
	* @param $user_id The user ID.
	* @returns void.
	*/
	public function getMemberByChannelAndUser($channel_id, $user_id)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		$user_id = $user_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID");
		if ($user_id < 1) App::error("Invalid user ID");
		
		$DB = new DB();
		
		$results = $DB->query("select mem.member_id, mem.channel_id, mem.user_id, u.username, mem.role, mem.creation_date, mem.lastseen from members mem, users u where mem.user_id = u.user_id and mem.channel_id = $1 and mem.user_id = $2", $channel_id, $user_id);
		
		return $results;
		
	}
	
	/**
	* Get all channel members.
	* @param $channel_id int The ID of the channel to get members for.
	* @param $members_with_roles_only bool False to return all members. True to return
	* only members that have a role other than regular member set.
	* @returns Member data as an array of database rows.
	*/
	public function getMembersByChannel($channel_id, $members_with_roles_only = false)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID");
		
        $DB = new DB();
		
		$rolesQ = $members_with_roles_only ? "and (mem.role != 'regular' or c.owner_id = u.user_id)" : "";
		
		$results = $DB->query("select mem.member_id, mem.channel_id, mem.user_id, u.username, u.email, u.game_points, null as is_superuser, null as avatar_url, avatar_override, mem.role, mem.creation_date, mem.lastseen, (mem.lastseen > now() - interval '".Config::getConfigOption("OnlineTimeoutMinutes")." minutes') as online, (c.owner_id = u.user_id) as is_owner, system_account, u.steam, u.twitch, u.bio, u.playing from members mem, users u, channels c where mem.channel_id = c.channel_id and mem.user_id = u.user_id and mem.channel_id = $1 {$rolesQ} order by u.username asc", $channel_id);
		
		require_once '../app/models/MemberModel.php';
		$member = new MemberModel();

		require_once '../app/models/UserModel.php';
		$user = new UserModel();

		if ($results)
		{
			foreach ($results as &$r)
			{
				//Check which users are superuser.
				$r['is_superuser'] = ($member->isSuperuser($r['user_id']) ? "t" : "f");
				
				if ($r['avatar_override'] == "")
				{
					$r['avatar_url'] = $user->getAvatar($r['email']);
				}
				else
				{
					$r['avatar_url'] = $r['avatar_override'];
				}
				
				unset($r['avatar_override']);
				unset($r['email']);
				
			}
		}
		
		return $results;
	}
	
	/**
	* Get a list of members banned from a channel.
	* @param $channel_id The channel ID.
	* @returns The list of banned members.
	*/
	public function getBannedMembersByChannel($channel_id)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID");
		
		require_once '../app/models/UserModel.php';
		$user = new UserModel();
		
        $DB = new DB();
		
		$results = $DB->query("select b.ban_id, u.user_id, u.username, u.email, null as avatar_url from users u, bans b where b.channel_id = $1 and b.user_id = u.user_id order by u.username asc", $channel_id);
		
		if ($results)
		{
			foreach ($results as &$r)
			{
				$r['avatar_url'] = $user->getAvatar($r['email']);
				unset($r['email']);
			}
		}
		
		return $results;
	}
	
	/**
	* Check if a user is a system account (such as Gamebots, etc).
	* @param $user_id The user ID to check.
	* @returns True if the user is a system account, false if not.
	*/
	public function checkIfSystemAccount($user_id)
	{
		
		//Sanitize.
		$user_id = $user_id * 1;
		
		if ($user_id < 1) App::error("Invalid user ID");
		
		$DB = new DB();
		
		$result = $DB->query("select user_id from users where user_id = $1 and system_account = 't'", $user_id);
		
		return ($result && isset($result[0]) && isset($result[0]['user_id']));
	}
	
	/**
	* Check if a user is banned from a channel.
	* @param $channel_id The channel ID to check.
	* @param $user_id The user ID to check.
	* @returns True if the user is banned from that channel, false if not.
	*/
	public function checkIfBanned($channel_id, $user_id)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		$user_id = $user_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID");
		if ($user_id < 1) App::error("Invalid user ID");
		
		$DB = new DB();
		
		$result = $DB->query("select user_id from bans where channel_id = $1 and user_id = $2", $channel_id, $user_id);
		
		return ($result && isset($result[0]) && isset($result[0]['user_id']));
	}
	
	/**
	* Check if a user is a member of a channel.
	* @param $channel_id The channel ID to check.
	* @param $user_id The user ID to check.
	* @returns True if the user is a member of that channel, false if not.
	*/
	public function checkIfMember($channel_id, $user_id)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		$user_id = $user_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID");
		if ($user_id < 1) App::error("Invalid user ID");
		
		$DB = new DB();
		
		$result = $DB->query("select user_id from members where channel_id = $1 and user_id = $2", $channel_id, $user_id);
		
		return ($result && isset($result[0]) && isset($result[0]['user_id']));
	}
	
	/**
	* Check if user has a given role in a channel.
	* @param $channel_id The channel ID to check.
	* @param $user_id The user ID to check.
	* @param $role The role to check.
	* @returns True if user $user_id has role $role in channel $channel_id, false if not.
	*/
	public function checkRole($channel_id, $user_id, $role)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		$user_id = $user_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID");
		if ($user_id < 1) App::error("Invalid user ID");
		
		if (!in_array($role, array('admin', 'mod', 'regular'))) App::error("Invalid role name.");
		
		$DB = new DB();
		
		$result = $DB->query("select user_id from members where channel_id = $1 and user_id = $2 and role = $3", $channel_id, $user_id, $role);
		
		return ($result && isset($result[0]) && isset($result[0]['user_id']));
	}
	
	/**
	* Check config to see if user with this ID is a superuser.
	* @param $user_id int The user ID to check.
	* @returns True if user is a superuser, false if not.
	*/
	public function isSuperuser($user_id)
	{	
		
		require_once '../app/models/UserModel.php';
		$user = new UserModel();
	
		$userData = $user->getUserById($user_id);
		
		if (!$userData || !isset($userData[0]) || !isset($userData[0]['username'])) return false;
		
		$namesArray = explode(",", Config::getConfigOption('SuperUsers'));
		foreach($namesArray as &$nameEntry)
		{
			$nameEntry = strtolower(trim($nameEntry));
		}
		
		return in_array(strtolower(trim($userData[0]['username'])), $namesArray);
	}
	
	/**
	* Update the last time a user was seen accessing a channel (sets time to "now").
	* @param $channel_id The channel ID to use.
	* @param $user_id The user ID to use.
	* @returns void.
	*/
	public function updateLastSeen($channel_id, $user_id)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		$user_id = $user_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID");
		if ($user_id < 1) App::error("Invalid user ID");
		
		$DB = new DB();
		
		return $DB->query("update members set lastseen = now() where channel_id = $1 and user_id = $2", $channel_id, $user_id);
	}
	
	/**
	* Add entry to bans table for channel channel_id and use user_id. 
	* If entry already exists, it will update the ban creation_date to current time.
	* @param $channel_id The channel ID to use.
	* @param $user_id The user ID to use.
	* @returns void.
	*/
	public function ban($channel_id, $user_id)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		$user_id = $user_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID");
		if ($user_id < 1) App::error("Invalid user ID");
		
		$DB = new DB();
		
		return $DB->query("insert into bans (channel_id, user_id, banned_by_user_id) values($1, $2, $3) ON CONFLICT (channel_id, user_id) DO UPDATE set banned_by_user_id = $3, creation_date = now()", $channel_id, $user_id, $_SESSION['user_id']);
	}
	
	/**
	* Remove entry from bans table for channel channel_id and use user_id. 
	* @param $channel_id The channel ID to use.
	* @param $user_id The user ID to use.
	* @returns void.
	*/
	public function unban($channel_id, $user_id)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		$user_id = $user_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID");
		if ($user_id < 1) App::error("Invalid user ID");
		
		$DB = new DB();
		
		return $DB->query("delete from bans where channel_id = $1 and user_id = $2", $channel_id, $user_id);
	}
	
	/**
	* Update "is typing" status for a user in a given channel.
	* @param $channel_id The channel ID to use.
	* @param $user_id The user ID to use.
	* @returns void.
	*/
	public function updateIsTyping($channel_id, $user_id)
	{
	
		//Sanitize.
		$channel_id = $channel_id * 1;
		$user_id = $user_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID");
		if ($user_id < 1) App::error("Invalid user ID");
		
		require_once '../app/models/ChannelModel.php';
		$channel = new ChannelModel();
		
		//Check user has permission to post in this channel.
		$channelData = $channel->getChannelById($channel_id);
		
		if (!$channelData || !isset($channelData[0]['channel_id'])) App::error("Invalid channel ID.");
		
		if ($channelData[0]['deleted'] == 't') App::error("Channel has been deleted.");
		
		if ($channelData[0]['owner_id'] != $user_id && $channelData[0]['public'] != 't' && !$this->checkIfMember($channel_id, $user_id)) App::error("You do not have permission to post a message to that channel.");
		
		$DB = new DB();
		
		$DB->query("update members set lastseen_typing = now() where channel_id = $1 and user_id = $2", $channel_id, $user_id);
		
	}
	
	/**
	* Get the "is typing" status for all users in a given channel.
	* @param $channel_id The channel ID to use.
	* @param $user_id int A user ID to exclude from the returned list (usually the currently logged in user).
	* @returns A list of users that are currently typing in that channel.
	*/
	public function getIsTyping($channel_id, $user_id="")
	{
		//Sanitize.
		$channel_id = $channel_id * 1;
		$user_id = $user_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID");
		if ($user_id < 1) App::error("Invalid user ID");
		
		require_once '../app/models/ChannelModel.php';
		$channel = new ChannelModel();
		
		//Check user has permission to post in this channel.
		$channelData = $channel->getChannelById($channel_id);
		
		if (!$channelData || !isset($channelData[0]['channel_id'])) App::error("Invalid channel ID.");
		
		if ($channelData[0]['deleted'] == 't') App::error("Channel has been deleted.");
		
		$DB = new DB();
		
		$exclude_user = $user_id != 0 ? "and u.user_id != {$user_id}" : "";
		
		return $DB->query("select m.user_id, u.username from members m, users u where m.user_id = u.user_id and m.channel_id = $1 and m.lastseen_typing > now() - interval '".Config::getConfigOption("LastSeenTypingSeconds")." seconds' {$exclude_user} order by u.username asc", $channel_id);
	}
	
}

?>
