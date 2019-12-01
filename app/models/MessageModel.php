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
* Message model.
* Provides functions for the manipulation of Message data.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
*/

include_once '../app/core/DB.php';

class MessageModel
{
	/**
	* Delete a message (marks message deleted).
	* @param $message_id The ID of the message to mark deleted.
	* @returns void.
	*/
	public function delete($message_id)
	{
		//Sanitize.
		$message_id = $message_id * 1;
		
		if ($message_id < 1) App::error("Invalid message ID.");
		
		$DB = new DB();
		
		//Only message owner can mark a message deleted.
		$DB->query("update messages set deleted = 't' where message_id = $1 and user_id = $2", $message_id, $_SESSION['user_id']);
	}
	
	/**
	* Create a new message.
	* @param $channel_id The ID of the channel to create the message in.
	* @param $message_text The message text.
	* @param $system_message bool Set true if this is a system message, false if it is a regular message.
	* @param $user_id The ID of the user posting the message. Set to 0 to use currently logged in user ID.
	* @param $stream_name The stream account name to use when advertising the stream.
	* @param $delay The number of minutes or hours to delay reposting the stream notice.
	* @param $delay_type One of "Minute", "Minutes", "Hour", "Hours".
	* @returns The new message data.
	*/
	public function create($channel_id, $message_text, $system_message=false, $user_id=0, $stream_name=null, $delay="", $delay_type="")
	{
		
		//Post message as logged in user if no user ID given.
		if ($user_id == 0) $user_id = $_SESSION['user_id'];
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		$user_id = $user_id * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID");
		if ($user_id < 1) App::error("Invalid user ID");
		
		if (!$message_text || $message_text == "") App::error("Cannot post a blank message.");
		
		if (mb_strlen($message_text) > 1024) App::error("Message too long.");
		
		if ($stream_name && $stream_name != "")
		{
			if (mb_strlen($stream_name) > 128) App::error("Stream account name too long.");
			if (!is_numeric($delay) || $delay < 0) App::error("Invalid delay number. Delay must be a positive numeric value, or zero.");
			if (!in_array(strtolower($delay_type), array("hour", "hours", "minute", "minutes"))) App::error("Invalid delay type. It must be one of \"hour\",\"hours\",\"minute\" or \"minutes\".");
		}
		
		$stream_advertised = ($stream_name && $stream_name != "" && $delay == 0 ? 't' : 'f');
		$stream_time = "0 minutes";
		
		if ($stream_name && is_numeric($delay) && $delay_type)
		{
			$stream_time = $delay . " " . $delay_type;
		}
		
		require_once '../app/models/ChannelModel.php';
		$channel = new ChannelModel();
		
		//Check user has permission to post in this channel.
		$channelData = $channel->getChannelById($channel_id);
		
		if (!$channelData || !isset($channelData[0]['channel_id'])) App::error("Invalid channel ID.");
		
		if ($channelData[0]['deleted'] == 't') App::error("Channel has been deleted.");
		
		require_once '../app/models/MemberModel.php';
		$member = new MemberModel();
		
		if ($channelData[0]['owner_id'] != $user_id && $channelData[0]['public'] != 't' && !$member->checkIfMember($channel_id, $user_id)) App::error("You do not have permission to post a message to that channel.");
		
		$DB = new DB();
		
		//Check message send rate limit has not been exceeded.
		$arr = $DB->query("select count(*) from messages where user_id = $1 and creation_date > now() - interval '1 minute'", $user_id);
		
		if ($arr && isset($arr[0]) && $arr[0]['count'] > Config::getConfigOption("MaxSendMessagesPerMinute")) die("Maximum message send rate limit reached.");
		
		//Get next message id.
		$arr = $DB->query("select nextval('messages_message_id_seq'::regclass)");
		$next_id = $arr[0]['nextval'];
		
		if (!is_numeric($next_id) || $next_id * 1 < 1) App::error("Cannot get next available message id from database when creating message.");
		
		//Check for commands in message text.
		$recipient_id = null;
		if (substr(strtolower($message_text), 0, 3) === "/pm")
		{
            $exploded = explode(" ", $message_text);
            if (isset($exploded[1]))
            {
                //Search for recipient by name.
                $exploded[1] = trim($exploded[1]);
                $results = $DB->query("select user_id from users where username ilike $1", $exploded[1]);
                if (isset($results) && isset($results[0]) && $results[0]['user_id'] > 0) 
                {
                    $recipient_id = $results[0]['user_id'];
                }
                else
                {
                    App::error("Unknown recipient for private message. Type /? for help.");
                }
                
                //Remove the "/pm" and "(username)" parts of the original message.
                unset($exploded[0]);
                unset($exploded[1]);
                
                //Rebuild the message text from the exploded array.
                $message_text = implode(" ", $exploded);
                $message_text = trim($message_text);
            }
			else
			{
				App::error("No recipient given for private message. Type /? for help.");
			}
		}
		
		if (!$message_text || $message_text == "") App::error("Cannot post a blank message.");
		
		//Insert new message.
		$DB->query("insert into messages (message_id, channel_id, user_id, message, system_message, recipient_id, stream_account, stream_at_time, stream_advertised) values($1, $2, $3, $4, $5, $6, $7, now() + $8, $9)", $next_id, $channelData[0]['channel_id'], $user_id, $message_text, $system_message ? "t" : "f", $recipient_id, $stream_name, $stream_time, $stream_advertised);
		
		$results = $DB->query("select m.message_id, m.channel_id, m.user_id, m.recipient_id, (select u2.username from users u2 where u2.user_id = m.recipient_id) as recipient_username, m.message, null as message_htmlsafe, extract(epoch from m.creation_date) as creation_date, to_char(m.creation_date, '".Config::getConfigOption("MessageDateDisplayFormat")."') as human_date, u.username, u.email, null as avatar_url from messages m, users u where m.user_id = u.user_id and m.deleted = 'f' and m.message_id = $1", $next_id);
		
		//Set typing time to null so we stop broadcasting that we are typing.
		$DB->query("update members set lastseen_typing = null where channel_id = $1 and user_id = $2", $channel_id, $user_id);
		
		require_once '../app/models/UserModel.php';
		$user = new UserModel();
		
		if ($results)
		{
			foreach ($results as &$r)
			{
				$r['avatar_url'] = $user->getAvatar($r['email']);
				unset($r['email']);
				
				$r['message_htmlsafe'] = $this->parseText($r['message']);
				unset($r['message']);
			}
		}
		
		return $results[0];
	}
	
	/**
	* Get $count most recent messages for channel $channel_id between now and $lastRequestTime (in epoch time). 
	* The current time in epoch time will be included * with each result as "request_time".
	* @param $channel_id The channel ID to get messages from.
	* @param $count The maximum number of messages to return.
	* @param $lastRequestTime The last time messages were checked, in epoch time.
	* @returns The selected recent messages.
	*/
	public function getMessages($channel_id, $count, $lastRequestTime)
	{
		
		//Sanitize.
		$channel_id = $channel_id * 1;
		$count = $count * 1;
		$lastRequestTime = $lastRequestTime * 1;
		
		if ($channel_id < 1) App::error("Invalid channel ID");
		
		require_once '../app/models/ChannelModel.php';
		$channel = new ChannelModel();
		
		//Check user has permission to post in this channel.
		$channelData = $channel->getChannelById($channel_id);
		
		if (!$channelData || !isset($channelData[0]['channel_id'])) App::error("Invalid channel ID.");
		
		if ($channelData[0]['deleted'] == 't') App::error("Channel has been deleted.");
		
		$DB = new DB();
		
		//Permissions rules.
		$permissions = "(c.public = 't' or (c.owner_id = $4) or (c.owner_id != $4 and ($4 in (select user_id from members m where channel_id = c.channel_id)))) and ($4 not in (select b.user_id from bans b where b.channel_id = c.channel_id)) and";
		
		$results = $DB->query("select extract(epoch from now()) as request_time, * from (select m.message_id, m.channel_id, m.user_id, m.recipient_id, (select u2.username from users u2 where u2.user_id = m.recipient_id) as recipient_username, m.message, null as message_htmlsafe, extract(epoch from m.creation_date) as creation_date, to_char(m.creation_date, '".Config::getConfigOption("MessageDateDisplayFormat")."') as human_date, m.system_message, u.username, u.email, null as avatar_url, avatar_override, u.game_points, u.steam, u.twitch, m.stream_account from messages m, users u, channels c where {$permissions} m.channel_id = c.channel_id and m.user_id = u.user_id and (m.recipient_id is null or m.recipient_id = $4 or m.user_id = $4) and m.deleted = 'f' and m.channel_id = $1 and extract(epoch from m.creation_date) > $3 order by m.creation_date desc limit $2) as results order by creation_date asc", $channelData[0]['channel_id'], $count, $lastRequestTime, $_SESSION['user_id']);
		
		require_once '../app/models/UserModel.php';
		$user = new UserModel();
		
		if ($results)
		{
			foreach ($results as &$r)
			{
				
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
				
				$r['message_htmlsafe'] = $this->parseText($r['message']);
				unset($r['message']);
			}
		}
		return $results;
		
	}
	
	/**
	* Check for any outstanding game streams that need advertising, and post them in the relevant channel.
	* @param $channel_id The channel ID to post in.
	* @returns void.
	*/
	public function advertiseOutstandingStreams($channel_id)
	{
		
		$DB = new DB();
		
		$DB->query("BEGIN");
		
		//Get all messages that have stream account set, stream not yet advertised, and stream time in the past.
		//LOCKS these table rows to prevent other processes from trying to advertise the same streams.
		$messages = $DB->query("select * from messages where channel_id = $1 and stream_advertised = 'f' and (stream_account is not null or stream_account != '') and now() > stream_at_time for update", $channel_id);
		
		//Repost all selected streams and mark streams as advertised.
		if ($messages) foreach ($messages as $row)
		{
			$message_text = "STREAMING at https://twitch.tv/{$row['stream_account']} now!";
			$this->create($row['channel_id'], $message_text, false, $row['user_id'], $row['stream_account'], 0, "minutes");
			
			$DB->query("update messages set stream_advertised = 't' where message_id = $1", $row['message_id']);
		}
		
		$DB->query("COMMIT");
	}
	
	/**
	* Parse text, parse Markdown, sanitize it and prepare it for output as HTML.
	* @param $text The text to parse.
	* @returns The parsed text.
	*/
	private function parseText($text) 
	{
	
		require_once '../app/core/Parsedown.php';
		$parsedown = new Parsedown();

		//Prevent Pasedown from adding images with the ![]() tag.
		$text = $text = str_replace("![",'?!?brak?', $text);

		//Parse Github-flavoured Markdown in the text. 
		//Sanitizes user input dangerous characters like < > " '
		//Includes auto linking of web addresses.
		$text = $parsedown->setUrlsLinked(true)->setSafeMode(true)->setBreaksEnabled(true)->line($text);
		
		$text = str_replace('?!?brak?', "![", $text);
		
		//Make multiple spaces significant to the browser (stops it collapsing multiple spaces into one when it displays them).
		$text = str_replace("  ", "&nbsp;&nbsp;", $text);
		
		//Make whitespace after carriage returns significant to the browser (stops it collapsing spaces after breaks when it displays them).
		//Note: Parsedown turns carriage returns (\n, \r\n, or \r) into <br />\n.
		$text = str_replace("<br />\n ", "<br />\n&nbsp;", $text);
		
		return $text;
	}
	
}

?>
