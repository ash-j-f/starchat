/**
* StarChat message objects.
* @author Ashley Flynn - Academy of Interactive Entertainment and the Canberra Institute of Technology - 2019.
*/

/**
* Object for adding messages to the message list.
*/
var messageObject =
{
	/**
	* Scroll message panel to bottom after a new message has come in.
	* Will only scroll to bottom if panel was already scrolled to bottom before new message came in,
	* or if the "force" option is set to true.
	* @param force bool True to force scrolling to bottom, false to only scroll to bottom if panel was already scrolled to bottom before new message came in.
	* @returns void.
	*/
	scrollToBottom : function(force=false)
	{
		//Scroll message box to bottom.
		if (chat.scrollIsAtBottom || force) $('#messagelist').scrollTop($('#messagelist')[0].scrollHeight);
		messageObject.updateMessageWidthScrollToBottom();
	},
	
	/**
	* Check message list scroll position.
	* If message panel is not scrolled to bottom, display the "showing older messages" notice.
	* @returns void.
	*/
	checkScroll : function (e) {
		var elem = $(e.currentTarget);
		if (elem[0].scrollHeight - elem.scrollTop() == elem.innerHeight()) {
			chat.scrollIsAtBottom = true;
		}
		else
		{
			chat.scrollIsAtBottom = false;
		}
		messageObject.updateMessageWidthScrollToBottom();
	},
	
	/**
	* Display or hide the "showing older messages" notice, depending on message panel scroll status.
	* @returns void.
	*/
	updateMessageWidthScrollToBottom : function()
	{
		$('#messagelistScrollToBottom').innerWidth($('#messagelist').prop("scrollWidth") - 3);
		if (chat.scrollIsAtBottom)
		{
			$('#messagelistScrollToBottom').hide();
		}
		else
		{
			$('#messagelistScrollToBottom').show();
		}
	},
	
	/**
	* Create a new message object in the message list.
	* NOTE: All parameters match the Starchat user table columns, except...
	* @param error bool set true means message is error message type (will appear in a different colour and format). False for regular message. Default false.
	* @param html bool Message should be output as raw HTML without sanitisation. False to sanitize message as raw text. Default true.
	* @returns void.
	*/
	createMessageObject : function(message_id, username, message, system_message, recipient_id, recipient_username, creation_date, human_date, avatar_url, error=false, html=true, game_points=0, steam="", twitch="", stream_account="")
	{
		
		//Detach spacer object.
		var spacer = $('#messagespacer').detach();
		
		//Create message div.
		msgClass = system_message == 't' ? 'system' : "";
        msgClass += recipient_id != null ? ' pm' : "";
		
		showIconAndName = messageSender.lastMessageBy != username && system_message!='t';
		
		msgContainerClassFloat = showIconAndName ? " message_content_float_float" : "";	
		
		msgErrorColor = error ? " messageError" : "";
		
		msgStreamColor = stream_account ? " stream_message" : "";
		
		$('#messagelist').append('<div id="message_'+message_id +'" class="' + msgClass + ' message_toplevel" ><div id="message_'+message_id +'_content" class="message_content'+msgContainerClassFloat+msgErrorColor+'"><div class="message_text'+msgStreamColor+'"></div></div><div style="clear: both;"></div></div>');

		if (html)
		{
			$('#message_'+message_id+'_content .message_text').html(message);
		}
		else
		{
			$('#message_'+message_id+'_content .message_text').text(message);
		}
		
		//Insert user message as TEXT (to encode html entities).
		if (system_message == 't')
		{
			//System message.
			
			messageSender.lastMessageBy = "";
			messageSender.lastMessageTime = 0;
			
			messageSender.lastMessageID = null;
			messageSender.lastMessageRawConent = message;
		}
		else
		{
            
			//Message by user.
			
			//Private message.
			privateMessageText = "";
            if (recipient_id)
            {
                privateMessageText = "[to <strong></strong>] ";
				$('#message_'+message_id+'_content .message_text').prepend('<span id="message_'+message_id+'_recipientname"></span>');
				$('#message_'+message_id+'_recipientname').html(privateMessageText);
				$('#message_'+message_id+'_recipientname strong').text(recipient_username);
            }
			
			//Username.
			if (showIconAndName)
			{
				$('#message_'+message_id+'_content').prepend('<span id="message_'+message_id+'_username" class="messageUsername"></span> <div class="messageHumanDate timeObject" style="display: inline; margin-left: 5px;" epoch="'+creation_date+'" humanDate="'+human_date+'">'+tools.humanDateElapsed(creation_date, tools.getEpochNow()) +' ago ('+human_date+')</div>');
				$('#message_'+message_id+'_username').text(username);
				
				//Register click event handler to open profile when member name is clicked.
				$('#message_'+message_id+'_username').click(function(){members.showProfile(username, avatar_url, game_points, steam, twitch); });
				
				$('#message_'+message_id+'_content .message_text').addClass("message_padleft");
				//$('#message_'+message_id+'_content').prepend('<div class="messageHumanDate timeObject" epoch="'+creation_date+'" humanDate="'+human_date+'">'+tools.humanDateElapsed(creation_date, tools.getEpochNow()) +' ago ('+human_date+')</div>');
			}
			
			if (showIconAndName) 
			{
				$('#message_'+message_id).prepend('<div class="avatar_box"><img class="avatar topalign messageicon" src="'+avatar_url+'" /></div>');
				$('#message_'+message_id).append('<div style="clear: both;"></div>');
				$('#message_'+message_id).addClass('topspacer');
				messageSender.lastMessageID = message_id;
				
				//Register click event handler to open profile when member icon is clicked.
				$('#message_'+message_id+' .avatar_box').click(function(){members.showProfile(username, avatar_url, game_points, steam, twitch); });
			}
			else
			{
				if (messageSender.lastMessageID) $('#message_'+messageSender.lastMessageID).addClass('magneticBottomMargin');
				$('#message_'+message_id+'_content').addClass('extraLeftMargin');
				
				if (creation_date - messageSender.lastMessageTime > chat.showNextDateTimeoutSeconds)
				{
					$('#message_'+message_id+'_content').prepend('<div class="messageHumanDate extraTopMargin timeObject" epoch="'+creation_date+'" humanDate="'+human_date+'">'+tools.humanDateElapsed(creation_date, tools.getEpochNow()) +' ago ('+human_date+')</div>');
				}
				
				messageSender.lastMessageID = null;
			}
						
			messageSender.lastMessageBy = username;
			messageSender.lastMessageTime = creation_date;
			messageSender.lastMessageRawConent = message;
		}
		
		//Reinsert spacer.
		spacer.appendTo('#messagelist');

		//Fade-in effect for new message.
		//Must use the following methods or IE flickers.
		$('#message_'+message_id).css("opacity", "0");
		$('#message_'+message_id).fadeTo(500,1);

	}
}


/**
* Object for sending messages to channels.
*/
var messageSender = 
{
	/**
	* Update message box and help text. Send message if enter was pressed.
	* @returns void.
	*/
	updateMessage : function ()
	{
		//As we need to know what the value of the textarea WILL be after this event 
		//(such as a keypress that could be a new character or a backspace, etc), to know if it will be empty or not,
		//we need to check the value of the textarea a very short time after this event ocurred.
		setTimeout(function(){
			if ($('#usermessage').val() == "") $('#usermessage').css("background-color", "transparent");
			if ($('#usermessage').val() != "") $('#usermessage').css("background-color", "white");
		}, 10);
		
		//Send message if enter was pressed. Do not send if shift is help (then it will just insert a carriage return).
		if (window.event.keyCode == 13 && !window.event.shiftKey) { chat.isTyping = false; messageSender.sendMessage(chat.current_channel_id, chat.token, this); return false; } else { if ($(this).val() != '' && $(this).val().trim().charAt(0) != '/' && window.event.keyCode != 191) chat.isTyping = true; }
	},
	
	/**
	* Send a message by currently logged in user for a given channel.
	* Clears the contents of the given form object on send.
	* Reports errors if send failed.
	* @param channel_id int The channel ID.
	* @param sessionToken string The session token.
	* @param textareaObj object The textarea object.
	* @returns void.
	*/
	sendMessage : function (channel_id, sessionToken, textareaObj)
	{
		if (!chat.enableChecks) 
		{
			tools.sendHelpMessage('Unable to send message. You are leaving the channel.', true);
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//Trim whitespace from message text.
		$('#usermessage').val($.trim($('#usermessage').val()));
		
		//Do not allow blank messages to be sent.
		if ($('#usermessage').val() == "")
		{
			$('#usermessage').val('');
			return false;
		}
		
		//If message is "/?" print out user help.
		if ($('#usermessage').val().substr(0,2) == "/?")
		{	
			tools.sendHelpMessage("<strong>***System Help***</strong>\
			<br /><br /><strong>General</strong>\
			<br />Leave channel (remove self as member): <strong>/leave</strong>\
			<br />Send private messages: <strong>/pm Username Message</strong>\
			<br />Set \"playing\" status (blank GameName to clear): <strong>/playing GameName</strong>\
			<br />Advertise Twitch stream: <strong>/stream TwitchAccountName StartTimeDelay Hours/Minutes</strong>\
			<br />&nbsp;&nbsp;&nbsp;&nbsp;eg: /stream Myaccountname 2 hours\
			<br />&nbsp;&nbsp;&nbsp;&nbsp;Stream will be advertised immediately, and then automatically at time specified\
			<br /><br /><strong>Games</strong>\
			<br />List available games: <strong>/game list</strong>\
			<br />To see game rules type: <strong>/game rules GameName</strong>\
			<br />To start a game type: <strong>/game start GameName</strong>\
			<br />To stop a running game type: <strong>/game stop</strong>\
			<br />To interact with a game type: <strong>/game Command</strong>\
			<br />(You can type <strong>/g</strong> instead of <strong>/game</strong>)\
			<br /><br /><strong>Administration</strong>\
			<br />List users banned from channel: <strong>/list bans</strong>\
			<br />Ban user from channel: <strong>/ban Username</strong>\
			<br />Unban user from channel: <strong>/unban Username</strong>\
			<br />Add member to channel (private channels only): <strong>/add Username</strong>\
			<br />Remove member from channel (private channels only): <strong>/remove Username</strong>\
			<br />Set role administrator in channel: <strong>/role Username admin</strong>\
			<br />Set role moderator in channel: <strong>/role Username mod</strong>\
			<br />Remove user role in channel: <strong>/role Username none</strong>\
			<br />List user roles in channel: <strong>/list roles</strong>\
			<br />Change channel owner: <strong>/owner Username</strong>\
			<br /><br /><strong>Superusers</strong>\
			<br />Delete user from site: <strong>/delete Username</strong>\
			<br />UNdelete user: <strong>/undelete Username</strong>\
			<br />Change user password: <strong>/password Username NewPassword</strong>\
			<br />Account details: <strong>/details Username</strong>\
			", false, true);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		// "/g" is an alias of "/game".
		if ($('#usermessage').val().toLowerCase().substr(0,3) == "/g ")
		{
			dataArray = $('#usermessage').val().split(" ");
			dataArray[0] = "/game";
			$('#usermessage').val(dataArray.join(" "));
		}
		
		//Delete a user.
		if ($('#usermessage').val().toLowerCase().substr(0,7) == "/delete")
		{
			dataArray = $('#usermessage').val().split(" ");
			if (dataArray.length != 2 )
			{
				tools.sendHelpMessage('Invalid delete command. Type /? for help.', true);
				return false;
			}
			members.del(dataArray[1], chat.token);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//Advertise Twitch stream
		if ($('#usermessage').val().toLowerCase().substr(0,7) == "/stream")
		{
			dataArray = $('#usermessage').val().split(" ");
			//Input must be in form /stream accountname number hour/hours/minute/minutes eg: /stream Leetgamer 12 minutes
			if (dataArray.length != 4 || !$.isNumeric(dataArray[2].trim()) || (dataArray[3].toLowerCase().trim() != "hour" &&  dataArray[3].toLowerCase().trim() != "hours" && dataArray[3].toLowerCase().trim() != "minute" && dataArray[3].toLowerCase().trim() != "minutes"))
			{
				tools.sendHelpMessage('Invalid stream command. Type /? for help.', true);
				return false;
			}
			if ((dataArray[2].trim() * 1) < 0)
			{
				tools.sendHelpMessage('Invalid stream command. Delay must be 0 or a positive number. Type /? for help.', true);
				return false;
			}
			
			members.stream(chat.current_channel_id, dataArray[1], dataArray[2], dataArray[3], chat.token);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//Set playing status
		if ($('#usermessage').val().toLowerCase().substr(0,8) == "/playing")
		{

			inputString = $('#usermessage').val();

			firstSpaceIndex = inputString.indexOf(' ');

			//Game name is blank by defau;t. If no name is given, the playing status is cleared.
			gameName = '';	
			
			//Game name can contain spaces, so get everything after the first space and use that as the game name.
			if (firstSpaceIndex != -1) gameName = inputString.substr(firstSpaceIndex + 1, inputString.length - 1);
			
			members.playing(gameName, chat.token);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//UNdelete a user.
		if ($('#usermessage').val().toLowerCase().substr(0,9) == "/undelete")
		{
			dataArray = $('#usermessage').val().split(" ");
			if (dataArray.length != 2 )
			{
				tools.sendHelpMessage('Invalid undelete command. Type /? for help.', true);
				return false;
			}
			members.undel(dataArray[1], chat.token);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//If message is /owner then attempt to set new channel owner.
		if ($('#usermessage').val().toLowerCase().substr(0,6) == "/owner")
		{
			dataArray = $('#usermessage').val().split(" ");
			if (dataArray.length != 2 )
			{
				tools.sendHelpMessage('Invalid owner command. Type /? for help.', true);
				return false;
			}
			members.owner(chat.current_channel_id, dataArray[1], chat.token);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//If message is /password then attempt to set user password.
		if ($('#usermessage').val().toLowerCase().substr(0,9) == "/password")
		{
			dataArray = $('#usermessage').val().split(" ");
			//A username and password name must be given.
			if (dataArray.length != 3)
			{
				tools.sendHelpMessage('Invalid password command. Type /? for help.', true);
				return false;
			}
			members.password(dataArray[1], dataArray[2], chat.token);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//If message is /details then attempt to get member details.
		if ($('#usermessage').val().toLowerCase().substr(0,8) == "/details")
		{
			dataArray = $('#usermessage').val().split(" ");
			if (dataArray.length != 2 )
			{
				tools.sendHelpMessage('Invalid details command. Type /? for help.', true);
				return false;
			}
			members.details(dataArray[1], chat.token);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//If message is /role then attempt to set user role.
		if ($('#usermessage').val().toLowerCase().substr(0,5) == "/role")
		{
			dataArray = $('#usermessage').val().split(" ");
			//A username and role name must be given.
			//Role must be one of the allowed role names.
			if (dataArray.length != 3 || $.inArray(dataArray[2], ['admin', 'mod', 'none']) === -1)
			{
				tools.sendHelpMessage('Invalid role command. Type /? for help.', true);
				return false;
			}
			members.role(chat.current_channel_id, dataArray[1], dataArray[2], chat.token);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//If message is /ban then attempt to ban user.
		if ($('#usermessage').val().toLowerCase().substr(0,4) == "/ban")
		{
			dataArray = $('#usermessage').val().split(" ");
			if (dataArray.length != 2)
			{
				tools.sendHelpMessage('Invalid ban command. Type /? for help.', true);
				return false;
			}
			members.ban(chat.current_channel_id, dataArray[1], chat.token);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//If message is /unban then attempt to unban user.
		if ($('#usermessage').val().toLowerCase().substr(0,6) == "/unban")
		{
			dataArray = $('#usermessage').val().split(" ");
			if (dataArray.length != 2)
			{
				tools.sendHelpMessage('Invalid unban command. Type /? for help.', true);
				return false;
			}
			members.unban(chat.current_channel_id, dataArray[1], chat.token);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//If message is /add then attempt to add user.
		if ($('#usermessage').val().toLowerCase().substr(0,4) == "/add")
		{
			dataArray = $('#usermessage').val().split(" ");
			if (dataArray.length != 2)
			{
				tools.sendHelpMessage('Invalid add command. Type /? for help.', true);
				return false;
			}
			members.add(chat.current_channel_id, dataArray[1], chat.token);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//If message is /remove then attempt to remove user.
		if ($('#usermessage').val().toLowerCase().substr(0,7) == "/remove")
		{
			dataArray = $('#usermessage').val().split(" ");
			if (dataArray.length != 2)
			{
				tools.sendHelpMessage('Invalid remove command. Type /? for help.', true);
				return false;
			}
			members.remove(chat.current_channel_id, dataArray[1], chat.token);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//If message is /list bans then list banned users
		if ($('#usermessage').val().toLowerCase().substr(0,10) == "/list bans")
		{
			members.listbans(chat.current_channel_id, chat.token);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//If message is /list roles then list user roles.
		if ($('#usermessage').val().toLowerCase().substr(0,11) == "/list roles")
		{
			members.listroles(chat.current_channel_id, chat.token);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//List games.
		if ($('#usermessage').val().toLowerCase().substr(0,10) == "/game list")
		{
			game.listGames();
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//List game rules.
		if ($('#usermessage').val().toLowerCase().substr(0,11) == "/game rules")
		{
			
			dataArray = $('#usermessage').val().split(" ");
			if (dataArray.length != 3)
			{
				tools.sendHelpMessage('Invalid game rules command. Type /? for help.', true);
				return false;
			}
			
			game.listRules(dataArray[2]);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//Start game
		if ($('#usermessage').val().toLowerCase().substr(0,11) == "/game start")
		{
			
			dataArray = $('#usermessage').val().split(" ");
			if (dataArray.length != 3)
			{
				tools.sendHelpMessage('Invalid game start command. Type /? for help.', true);
				return false;
			}
			
			game.start(chat.current_channel_id, dataArray[2], chat.token);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//Stop game
		if ($('#usermessage').val().toLowerCase().substr(0,11) == "/game stop")
		{
			
			dataArray = $('#usermessage').val().split(" ");
			if (dataArray.length != 2)
			{
				tools.sendHelpMessage('Invalid game stop command. Type /? for help.', true);
				return false;
			}
			
			game.stop(chat.current_channel_id, chat.token);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//Issue game command
		if ($('#usermessage').val().toLowerCase().substr(0,6) == "/game ")
		{
			
			dataArray = $('#usermessage').val().split(" ");
			if (dataArray.length != 2)
			{
				tools.sendHelpMessage('Invalid game interaction command. Type /? for help.', true);
				return false;
			}
			
			game.command(chat.current_channel_id, dataArray[1], chat.token);
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//If message is /leave then leave current channel (remove self as member).
		if ($('#usermessage').val().toLowerCase().substr(0,6) == "/leave")
		{
			
			members.leave(chat.current_channel_id, chat.token);
			
			$('#usermessage').val('');
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//Catch all unknown commands. Allow "/pm" to be sent through to the server as it deals with private messages directly.
		if ($('#usermessage').val().substr(0,1) == "/" && $('#usermessage').val().toLowerCase().substr(0,3) != "/pm")
		{
			tools.sendHelpMessage('Unknown command. Type /? for help.', true);
			
			//Scroll message box to bottom.
			messageObject.scrollToBottom(true);
			
			return false;
		}
		
		//Some basic sanitisation.
		channel_id = channel_id * 1;
		
		//AJAX query
		$.post( "/message/post/"+channel_id, 
			{ 
				usermessage: $('#usermessage').val(),
				token: sessionToken
			})
			.done(function( data ) {
			
				var obj;
				//Check the incoming data is a valid JSON object.
				try {
					obj = JSON.parse(data);
				} catch (e) {
					//Content error. Response was not JSON.
					
					tools.sendHelpMessage(data, true);
				
					return false;
				}
				
				//Success status received from application. Returned number is message ID.
				
				//Insert div message object.
				//Abort if message has already been added ot the message list.
				if( $('#message_'+obj.message_id).length ) return false;
				messageObject.createMessageObject(obj.message_id, obj.username, obj.message_htmlsafe, obj.system_message, obj.recipient_id, obj.recipient_username, obj.creation_date, obj.human_date, obj.avatar_url, false, true, obj.game_points, obj.steam, obj.twitch, obj.stream_account);

				//Scroll message box to bottom.
				messageObject.scrollToBottom(true);

			})
			.fail(function() {
				//Failed to send request from browser to client (or server returned an HTTP error, etc).
				tools.serverErrorMessage();
			});
		 
		//Clear the message box contents.
        $('#usermessage').val('');
		messageSender.updateMessage();

	}
}

/**
* Object for receiving messages for channels.
*/
var messageReceiver = 
{
	/**
	* Who was the last message received from?
	*/
	lastMessageBy : "",
	
	/**
	* Last message object in message list.
	*/
	lastMessageID : null,
	
	/**
	* Last message creation time in epoch seconds.
	*/
	lastMessageTime : 0,
	
	/**
	* Last message raw content (may be text or html).
	*/
	lastMessageRawConent : "",
	
	/**
	* Check for new messages. Function wrapper for use with setTimeout and setInterval.
	* @returns void.
	*/
	repeatCheckMessages : function() 
	{ 
		messageReceiver.getMessagesByCount(chat.current_channel_id, chat.token, chat.maxMessages);
	},
	
	/**
	* Set timeout to repeat checking for new messages.
	* @returns void.
	*/
	setTimeoutRepeatCheckMessages : function()
	{
		setTimeout(messageReceiver.repeatCheckMessages, chat.messageCheckIntervalMS);
	},
	
	/**
	* Get the previous N messages starting from current time.
	* @param channel_id int The channel ID.
	* @param sessionToken string The session token.
	* @param messageCount int The number of messages to get.
	* @returns void.
	*/
	getMessagesByCount : function(channel_id, sessionToken, messageCount)
	{
		channel_id = channel_id * 1;
		
        if (chat.enableChecks)
        {
        
            //AJAX query
            $.post( "/message/receive/"+channel_id, 
            { 
                count: messageCount,
                token: sessionToken,
                lastRequestTime: chat.lastRequestTimeEpoch
            })
            .done(function( data ) {
                var obj;
                //Check the incoming data is a valid JSON object.
                try {
                    obj = JSON.parse(data);
                } catch (e) {
                    //Content error. Response was not JSON.
					
					tools.sendHelpMessage(data, true);
					
                    return false;
                }

                //Skip empty data.
                if (!data || data === false || data === null) return false;
				
				//Update typing members.
				members.membersTyping = [];
				jQuery.each(obj.members_typing, function(i, val)
				{
					members.membersTyping.push(val.username);
				});
				members.updateIsTypingReceive();
                
				//Update messages list.
				jQuery.each(obj.messages, function(i, val) 
				{
                    
                    //Sanitise.
                    val.message_id = val.message_id * 1;
                    val.request_time = val.request_time * 1;
                    
                    //Set latest request time in epoch time.
                    if (val.request_time > chat.lastRequestTimeEpoch) chat.lastRequestTimeEpoch = val.request_time;
                    
                    //Abort if message has already been added ot the message list.
                    if( $('#message_'+val.message_id).length ) 
                    {
                        //Message already exists.
                        //Do nothing.
                    }
                    else
                    {
                        //Create message div.
                        messageObject.createMessageObject(val.message_id, val.username, val.message_htmlsafe, val.system_message, val.recipient_id, val.recipient_username, val.creation_date, val.human_date, val.avatar_url, false, true, val.game_points, val.steam, val.twitch, val.stream_account);
                        
                        //Scroll message box to bottom.
                        messageObject.scrollToBottom();
                    }
                });
                
                messageReceiver.setTimeoutRepeatCheckMessages();
            
            })
            .fail(function() {
                //Failed to send request from browser to client (or server returned an HTTP error, etc).
				tools.serverErrorMessage();
                messageReceiver.setTimeoutRepeatCheckMessages();
            });
        }
        else
        {
            messageReceiver.setTimeoutRepeatCheckMessages();
        }
	}
	
}
