<?php

/**
* Channel model.
* Provides functions for the manipulation of Channel data.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

include_once '../app/core/DB.php';

class ChannelModel
{
	/**
	* Delete a channel.
	* @param $channel_id The ID of the channel to delete.
	* @returns void.
	*/
	public function delete($channel_id)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID.");
		
		$DB = new DB();
		
		//Only channel owner can mark a channel deleted.
		$DB->query("update channels set deleted = 't' where channel_id = $1", $channel_id);
	}
	
	/**
	* Create a channel.
	* @param $channelName The name of the channel.
	* @param $channelDescription A description of the channel.
	* @param $public bool True for public channel, false for private channel.
	* @param $owner_id The user ID of the channel owner.
	* @returns "OK" on success, error type on failure.
	*/
	public function create($channelName, $channelDescription, $public, $owner_id)
	{
		
		//Sanitize.
		$owner_id = $owner_id * 1;
		
		$DB = new DB();
		
		//Channel names may only be alphanumeric, or contain space.
		//if (!$channelName || $channelName == "" || !preg_match('/^[a-zA-Z0-9 ]+$/', $channelName)) return "invalid_channelname";
		
		$channelName = trim($channelName);
		$channelDescription = trim($channelDescription);
		
		if (mb_strlen($channelName) > 48) App::error("Channel name too long.");
		if (mb_strlen($channelDescription) > 128) App::error("Channel description too long.");
		
		if ($owner_id < 1) App::error("Invalid owner ID.");
		
		if (!$channelName) return "invalid_channelname";
		
		//Check if cheannel name already exists.
		$existingChannel = $DB->query("select channelname from channels where channelname ilike $1", $channelName);
		if (strtolower($channelName) == strtolower($existingChannel[0]['channelname'])) return "channelname_used";
		
		$public = $public=="t" ? "t" : "f";
		
		//Get next channel id.
		$arr = $DB->query("select nextval('channels_channel_id_seq'::regclass)");
		$next_id = $arr[0]['nextval'];
		
		if (!is_numeric($next_id) || $next_id * 1 < 1) App::error("Cannot get next available channel id from database when creating channel.");
		
		//Insert new channel.
		$DB->query("insert into channels (channel_id, channelname, channeldesc, public, owner_id) values($1, $2, $3, $4, $5)", $next_id, $channelName, $channelDescription, $public, $owner_id);
		
		return "OK";
	}
	
	/**
	* Edit an existing channel.
	* @param $channel_id the ID of the channel to edit.
	* @param $channelName The name of the channel.
	* @param $channelDescription A description of the channel.
	* @param $public bool True for public channel, false for private channel.
	* @returns "OK" on success, error type on failure.
	*/
	public function edit($channel_id, $channelName, $channelDescription, $public)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		
		//Channel names may only be alphanumeric, or contain space.
		//if (!$channelName || $channelName == "" || !preg_match('/^[a-zA-Z0-9 ]+$/', $channelName)) return "invalid_channelname";
		
		$channelName = trim($channelName);
		$channelDescription = trim($channelDescription);
		
		if (mb_strlen($channelName) > 48) App::error("Channel name too long.");
		if (mb_strlen($channelDescription) > 128) App::error("Channel description too long.");
		
		if (!$channelName) return "invalid_channelname";
		
		if ($channel_id < 1) App::error("Invalid channel ID.");
		
		//Check user has permission to edit this channel.
		$channelData = $this->getChannelById($channel_id);
		
		require_once '../app/models/MemberModel.php';
		$member = new MemberModel;
		
		if (!$channelData || !isset($channelData[0]['channel_id']) || ($channelData[0]['owner_id'] != $_SESSION['user_id'] && !$member->checkRole($channel_id, $_SESSION['user_id'], 'admin') && !$member->isSuperuser($_SESSION['user_id']))) App::error("You do not have permission to edit that channel.");
		
		$DB = new DB();
		
		//Check if cheannel name already exists.
		$existingChannel = $DB->query("select channelname from channels where channelname ilike $1 and channel_id != $2", $channelName, $channelData[0]['channel_id']);
		if (strtolower($channelName) == strtolower($existingChannel[0]['channelname'])) return "channelname_used";
		
		$public = $public=="t" ? "t" : "f";
		
		//Edit channel.
		$DB->query("update channels set channelname = $1, channeldesc = $2, public = $3 where channel_id = $4", $channelName, $channelDescription, $public, $channel_id);
		
		return "OK";
	}
	
	/**
	* Get channel data by channel name.
	* @param $channelName The name of the channel.
	* @param $check_permissions bool True check if logged in user has permission to access channel, False to ignore permissions.
	* @returns The channel data.
	*/
	public function getChannelByName($channelName, $check_permissions=true)
	{
		
		$DB = new DB();
		
		$result = $DB->query("select channel_id from channels where channelname ilike $1", $channelName);
		
		if (!$result || !isset($result[0]) || !isset($result[0]['channel_id'])) App::error("That channel name not found.");
		
		return $this->getChannelById($result[0]['channel_id'], $check_permissions);
	}
	
	/**
	* Get channel data by channel ID.
	* @param $channel_id The ID of the channel.
	* @param $check_permissions bool True check if logged in user has permission to access channel, False to ignore permissions.
	* @param $show_deleted bool True show deleted channels in results, False to hide deleted channels.
	* @returns The channel data.
	*/
	public function getChannelById($channel_id, $check_permissions=true, $show_deleted = false)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID.");
		
		$DB = new DB();
		
		//Permissions rules.
		
		require_once '../app/models/MemberModel.php';
		$member = new MemberModel;
		
		$isSU = ($member->isSuperuser($_SESSION['user_id']) ? "t" : "f");
		
		$permissions = $check_permissions ? "('{$isSU}' = 't' or c.public = 't' or (c.owner_id = $2) or (c.owner_id != $2 and ($2 in (select user_id from members m where channel_id = c.channel_id)))) and ($2 not in (select b.user_id from bans b where b.channel_id = c.channel_id)) and" : "($2=$2) and";
		
		$deletedQ = $show_deleted ? "" : "c.deleted != 't' and";
		
		$result = $DB->query("select c.channel_id, c.owner_id, $2 in (select user_id from members where user_id = $2 and channel_id = c.channel_id and role = 'admin') as is_admin, $2 in (select user_id from members where user_id = $2 and channel_id = c.channel_id and role = 'mod') as is_mod, u.username as ownername, c.channelname, c.channeldesc, c.public, c.deleted, extract(epoch from c.creation_date) as creation_date, to_char(c.creation_date, '".Config::getConfigOption("MessageDateDisplayFormat")."') as human_date, null as avatar_url, u.email from channels c, users u where {$permissions} {$deletedQ} c.owner_id = u.user_id and c.channel_id = $1 order by c.creation_date desc", $channel_id, $_SESSION['user_id']);
		
		if (!$result || !isset($result[0]) || !isset($result[0]['channel_id'])) App::error("That channel ID not found.");
		
		require_once '../app/models/UserModel.php';
		$user = new UserModel;
		
		$result[0]['avatar_url'] = $user->getAvatar($result[0]['email']);
		
		unset($result[0]['email']);
		
		return $result;
	}
	
	/**
	* Get all channels in the system.
	* @param $show_deleted bool True show deleted channels in results, False to hide deleted channels.
	* @param $sort_order The sort order for the channels list.
	* @returns The channel data list.
	*/
	public function getChannels($show_deleted = false, $sort_order = "")
	{
		$DB = new DB();
		
		$showDeletedQ = $show_deleted ? "" : "c.deleted = 'f' and";
	
		$orderBy = "";
		
		switch ($sort_order)
		{
            default:
            case "":
                $orderBy = "c.channel_id in (select channel_id from members m2 where m2.user_id = $1) desc, c.creation_date desc";
                break;
            case "alpha":
                $orderBy = "c.channelname asc";
                break;
            case "date":
                $orderBy = "c.creation_date desc";
                break;
			case "owner":
                $orderBy = "u.username asc, c.creation_date desc";
                break;
		}

		//Permissions rules.
		
		require_once '../app/models/MemberModel.php';
		$member = new MemberModel;
		
		$isSU = ($member->isSuperuser($_SESSION['user_id']) ? "t" : "f");
		
		$permissions = "('{$isSU}' = 't' or c.public = 't' or (c.owner_id = $1) or (c.owner_id != $1 and ($1 in (select user_id from members m where channel_id = c.channel_id)))) and ($1 not in (select b.user_id from bans b where b.channel_id = c.channel_id)) and";

		//Count of messages since user was last seen in channel.
		$messagesWaiting = "(select count(*) from messages mes where mes.channel_id = c.channel_id and mes.user_id != $1 and mes.system_message != 't' and mes.creation_date > (select mem.lastseen from members mem where mem.user_id = $1 and mem.channel_id = c.channel_id)) as messages_waiting";

		$results = $DB->query("select {$messagesWaiting}, (select count(*) from members where channel_id = c.channel_id) as member_count, extract(epoch from (select creation_date from members m where m.user_id = $1 and m.channel_id = c.channel_id)) as joined_date, to_char((select creation_date from members m where m.user_id = $1 and m.channel_id = c.channel_id), '".Config::getConfigOption("MessageDateDisplayFormat")."') as joined_human_date, c.channel_id, c.owner_id, $1 in (select user_id from members where user_id = $1 and channel_id = c.channel_id and role = 'admin') as is_admin, $1 in (select user_id from members where user_id = $1 and channel_id = c.channel_id and role = 'mod') as is_mod, u.username as ownername, null as avatar_url, u.email, c.channelname, c.channeldesc, c.public, extract(epoch from c.creation_date) as creation_date, to_char(c.creation_date, '".Config::getConfigOption("MessageDateDisplayFormat")."') as human_date from channels c, users u where {$showDeletedQ} {$permissions} c.owner_id = u.user_id order by {$orderBy}", $_SESSION['user_id']);
		
		require_once '../app/models/UserModel.php';
		$user = new UserModel;
		
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
	* Set the owner of a channel.
	* @param $channel_id The channel ID.
	* @param $user_id The user ID of the new channel owner.
	* @returns void.
	*/
	public function setChannelOwner($channel_id, $user_id)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		$user_id = $user_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID.");
		if ($user_id < 1) App::error("Invalid user ID.");
		
		$DB = new DB();
		
		$DB->query("update channels set owner_id = $2 where channel_id = $1", $channel_id, $user_id);
	}
	
	/**
	* Check if a user owns a channel.
	* @param $channel_id The channel ID.
	* @param $user_id The user ID.
	* @returns True if the user is channel owner, false if not.
	*/
	public function checkIfOwner($channel_id, $user_id)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		$user_id = $user_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID");
		if ($user_id < 1) App::error("Invalid user ID");
		
		$DB = new DB();
		
		$result = $DB->query("select owner_id from channels where channel_id = $1 and owner_id = $2", $channel_id, $user_id);
		
		return ($result && isset($result[0]) && isset($result[0]['owner_id']));
	}
}

?>
