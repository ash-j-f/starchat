/**
* StarChat member objects.
* @author Ashley Flynn - Academy of Interactive Entertainment and the Canberra Institute of Technology - 2019.
*/


/**
* Object for adding members to member list.
*/
var memberObject = 
{
	
	/**
	* Create or update a member object in the members list on the chat page.
	* NOTE: Parameters all match Starchat member table columns.
	* @returns void.
	*/
	createMemberObject : function(member_id, user_id, username, creation_date, online, avatar_url, is_owner, role, system_account, game_points, steam, twitch, bio, is_superuser, playing)
	{
		
		onlineClass = online == 't' ? 'online' : 'offline';
		
		game_points_icon = '♖';
		//Update game points.
		if (username == $('.headerUsername').text()) $('.headerPointsContainerText').text(game_points_icon+game_points);
		
		
		//Does an entry for this member already exist?
		if ($('#member_'+member_id).length )
		{
			if ($('#member_'+member_id+' .avatar_box img').attr("src") != avatar_url) $('#member_'+member_id+' .avatar_box img').attr("src",avatar_url);
			
			$('#member_'+member_id+' .onlinestatus').removeClass('online offline');
			
			$('#member_'+member_id+' .onlinestatus').addClass(onlineClass);
			
			if ($('#member_'+member_id+' .memberUsername').text() != username) $('#member_'+member_id+' .memberUsername').text(username);
			
			if ($('#member_'+member_id+' .memberPointsText').text() != game_points_icon+game_points) $('#member_'+member_id+' .memberPointsText').text(game_points_icon+game_points);
		}
		else
		{			
			//Detach spacer object.
			var spacer = $('#memberspacer').detach();
			
			//Create member div.
			$('#memberlist').append('<div id="member_'+member_id +'" class="member"></div>');
			
			$('#member_'+member_id).append('<div class="avatar_box"><img class="avatar" src="'+avatar_url+'" /></div>');
			
			$('#member_'+member_id).append('<span class="fakeborder"></span>');
			
			$('#member_'+member_id).append('<span class="onlinestatus '+onlineClass+'"></span>');
			
			$('#member_'+member_id).append('<span class="memberOwnerIcon" style="display: none;"><img title="Channel Owner" src="/images/StarChatLogo.png" /></span>');
			
			$('#member_'+member_id).append('<span class="memberRole"><span class="memberRoleSuperuser" style="display: none;">superuser</span></span>');
			$('#member_'+member_id).append('<span class="memberRole"><span class="memberRoleOwner" style="display: none;">owner</span></span>');
			$('#member_'+member_id).append('<span class="memberRole"><span class="memberRoleAdmin" style="display: none;">admin</span></span>');
			$('#member_'+member_id).append('<span class="memberRole"><span class="memberRoleMod" style="display: none;">mod</span></span>');
			$('#member_'+member_id).append('<span class="memberRole"><span class="memberRoleRobot" style="display: none;">robot</span></span>');
			
			$('#member_'+member_id).append('<span class="memberPoints"><span class="memberPointsText" style="display: none;">'+game_points_icon+game_points+'</span></span>');
			
			$('#member_'+member_id).append('<span class="memberPlaying"><span class="memberPlayingText" style="display: none;"></span></span>');
			
			$('#member_'+member_id).append('<span class="memberUsername"></span>');
			
			//Insert data as TEXT (to encode html entities).
			$('#member_' + member_id + ' .memberUsername').text(username);
			
			//Reinsert spacer.
			spacer.appendTo('#memberlist');
			
			//Fade-in effect for new member.
			$('#member_'+member_id).css("opacity", "0");
			$('#member_'+member_id).fadeTo(500,1);
		}
		
		//Register click event handler to open profile when member entry is clicked.
		$('#member_'+member_id).click(function(){members.showProfile(username, avatar_url, game_points, steam, twitch, bio); });
		
		//show or hide member playing status.
		if (playing != '')
		{
			$('#member_'+member_id+' .memberPlayingText').text(playing);
			$('#member_'+member_id+' .memberPlayingText').show();
		}
		else
		{
			$('#member_'+member_id+' .memberPlayingText').hide();
		}
		
		//Show member points.
		if (game_points > 0)
		{
			$('#member_'+member_id+' .memberPointsText').show();
		}
		else
		{
			$('#member_'+member_id+' .memberPointsText').hide();
		}
		
		//Show channel ownership icon separately to role, so channel owner that is superuser can still be identified.
		if (is_owner == 't')
		{
			$('#member_'+member_id+' .memberOwnerIcon').show();
		}
		else
		{
			$('#member_'+member_id+' .memberOwnerIcon').hide();
		}
		
		//Hide all role indicators, then show only the active ones.
		$('#member_'+member_id+' .memberRoleSuperuser').hide();
		$('#member_'+member_id+' .memberRoleOwner').hide();
		$('#member_'+member_id+' .memberRoleAdmin').hide();
		$('#member_'+member_id+' .memberRoleMod').hide();
		$('#member_'+member_id+' .memberRoleRobot').hide();
		
		//Apply role icon/text logic.
		if (is_superuser == 't')
		{
			$('#member_'+member_id+' .memberRoleSuperuser').show();
		}
		else if (is_owner == 't')
		{
			$('#member_'+member_id+' .memberRoleOwner').show();
		}
		else if (role == 'admin') 
		{
			$('#member_'+member_id+' .memberRoleAdmin').show();
		}
		else if (role == 'mod')
		{
			$('#member_'+member_id+' .memberRoleMod').show();
		}
		else if (system_account == 't')
		{
			$('#member_'+member_id+' .memberRoleRobot').show();
		}
	}
}

/**
* Object for getting members of a channel.
*/
var members = 
{
	/**
	* Array of user data for those members currently typing.
	*/
	membersTyping : [],

	/**
	* Check for updates to members list. Wrapper to allow check members function to be called by setInterval and setTimeout.
	* @prarm repeat bool True to continue to repeat this check after each time it completes. False to run the check once only.
	* @returns void.
	*/
	repeatCheckMembers : function(repeat=true) 
	{ 
		members.getMembersForChannel(chat.current_channel_id, chat.token, repeat);
	},
	
	/**
	* Set timeout to check for updates to members list.
	* @returns void.
	*/
	setTimeoutRepeatCheckMembers : function()
	{
		setTimeout(members.repeatCheckMembers, chat.memberCheckIntervalMS);
	},

	/**
	* Show or hide the "is typing..." message that shows if other chat members are typing.
	* @return void.
	*/
	updateIsTypingReceive : function()
	{

		var arrayLength = members.membersTyping.length;
		var namestring = "";
		for (var i = 0; i < arrayLength; i++) {
			if (i > 0) namestring += ", ";
			namestring += members.membersTyping[i];
		}
		if (arrayLength > 1)
		{
			namestring += " are typing...";
			$('#messagespacer').addClass('typing');
		}
		else if (arrayLength == 1)
		{
			namestring += " is typing...";
			$('#messagespacer').addClass('typing');
			
			//Scroll message box to bottom (don't force, so only scroll if it was already at bottom).
			messageObject.scrollToBottom(false);
		}	
		else
		{
			//namestring = '';
			$('#messagespacer').removeClass('typing');
		}
		
		$('#messagespacer').text(namestring);
	},

	/**
	* send the "is typing" status for the currenly logged in user.
	* @return void.
	*/
	updateIsTypingSend : function(channel_id, sessionToken)
	{
		if (chat.isTyping === true)
		{
			chat.isTyping = false;
			
			//AJAX query
			$.post( "/istyping/"+channel_id, 
			{ 
				token: sessionToken
			})
			.done(function( data ) {
					
				//Skip empty data.
				if (!data || data === false || data === null)
				{
					tools.sendHelpMessage('Unknown error attempting to update typing status', true);
					return false;
				}
			
				//Display errors.
				if (data.trim() != 'OK')
				{
					tools.sendHelpMessage(data, true);
					return false;
				}
			
			})
			.fail(function() {
				//Failed to send request from browser to client (or server returned an HTTP error, etc).
				tools.serverErrorMessage();
			});
		}
		
	},
	
	/**
	* Show the profile popup for a selected member.
	* NOTE: Parameters match Starchat user table columns.
	* @returns void.
	*/
	showProfile : function(username, avatar_url, game_points, steam, twitch, bio)
	{
		//Set event for close button.
		$('#profileBox #closeButton a').click(function()
		{
			$('#profileBox').hide();
		});
		
		//Fade-in effect for new message.
		//Must use the following methods or IE flickers.
		$('#profileBox').css("opacity", "0");
		$('#profileBox').fadeTo(250,1);
		
		$('#profileBox .avatar_box .avatar').attr('src',avatar_url);
		
		$('#profileBox #profileUsername').text(username);
		
		$('#profileBox #profileSteam').text(steam);
		if (steam != "" && steam != null)
		{
			$('#profileBox #profileSteamIcon').show();
		}
		else
		{
			$('#profileBox #profileSteamIcon').hide();
		}
		
		$('#profileBox #profileTwitch').text(twitch);
		if (twitch != "" && twitch != null)
		{
			$('#profileBox #profileTwitchIcon').show();
		}
		else
		{
			$('#profileBox #profileTwitchIcon').hide();
		}
		
		$('#profileBox #profileBio').text(bio);
	},
	
	/**
	* Get member data for the given channel.
	* @param channel_id The channel ID to get the members list for.
	* @param sessionToken The form validation token for this session.
	* @param repeat bool True to repeat this check after each time it completes. False to run this check once only.
	* @returns void.
	*/
	getMembersForChannel : function(channel_id, sessionToken, repeat=true)
	{
		channel_id = channel_id * 1;
		
        if (chat.enableChecks)
        {
        
            //AJAX query
            $.post( "/member/channel/"+channel_id, 
            { 
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
                
                //Skip empty data.
                if (!data || data === false || data === null) return false;
                
				//Add or modify member entries.
                jQuery.each(obj, function(i, val) {
                    memberObject.createMemberObject(val.member_id, val.user_id, val.username, val.creation_date, val.online, val.avatar_url, val.is_owner, val.role, val.system_account, val.game_points, val.steam, val.twitch, val.bio, val.is_superuser, val.playing)
                });
                
				//Remove member entries no longer present in channel.
				$('#memberlist .member').each( function(i, val2) {
                    found = false;
					jQuery.each(obj, function(i, val) {
						if ('member_'+val.member_id == $(val2).attr('id'))
						{
							found = true;
						}
					});
					//If the element wasn't found in the incoming list and isn't already fading out...
					if (!found && !$(val2).hasClass('fading')) 
					{
						//Fade-out, then remove member.
						$(val2).addClass('fading');
						$(val2).fadeOut(500, function() {
							$(val2).remove();
						});
					}
                });
				
                if (repeat) members.setTimeoutRepeatCheckMembers();
            
            })
            .fail(function() {
                //Failed to send request from browser to client (or server returned an HTTP error, etc).
				tools.serverErrorMessage();
                if (repeat) members.setTimeoutRepeatCheckMembers();
            });
            
        }
        else
        {
           if (repeat) members.setTimeoutRepeatCheckMembers();
        }
	},
	
	/**
	* List roles for members of this channel.
	* @param channel_id The channel ID use.
	* @param sessionToken The form validation token for this session.
	* @returns void.
	*/
	listroles : function (channel_id, sessionToken)
	{
		
		channel_id = channel_id * 1;
	
		//AJAX query
		$.post( "/member/channel/"+channel_id+"/rolesonly", 
		{ 
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
			
			//Skip empty data.
			if (!data || data === false || data === null) return false;
			
			rolelist = "";
			
			if (data.trim() == "{}")
			{
				rolelist = "No roles to list.";
			}
			else
			{
				jQuery.each(obj, function(i, val) {
					if (val.is_owner == "t") 
					{
						rolelist += (i > 0 ? ', ' : ' ') + val.username + '(owner)';
					}
					else if (val.role != "regular") 
					{
						rolelist += (i > 0 ? ', ' : ' ') + val.username + '(' + val.role + ')';
					}
				});
			}
		
			tools.sendHelpMessage('Member roles: ' + rolelist);
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
		
	},
	
	/**
	* List bans for members of this channel.
	* @param channel_id The channel ID use.
	* @param sessionToken The form validation token for this session.
	* @returns void.
	*/
	listbans : function (channel_id, sessionToken)
	{
		
		channel_id = channel_id * 1;
	
		//AJAX query
		$.post( "/member/listbans/"+channel_id, 
		{ 
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
			
			//Skip empty data.
			if (!data || data === false || data === null) return false;
			
			banslist = "";
			
			if (data.trim() == "{}")
			{
				banslist = " None.";
			}
			else
			{
				jQuery.each(obj, function(i, val) {
					banslist += (i > 0 ? ', ' : ' ') + val.username;
				});
			}
		
			tools.sendHelpMessage('Banned users:' + banslist);
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
		
	},
	
	/**
	* Ban a member from this channel.
	* @param channel_id The channel ID use.
	* @param username Username of the user to ban.
	* @param sessionToken The form validation token for this session.
	* @returns void.
	*/
	ban : function (channel_id, username, sessionToken)
	{
		
		channel_id = channel_id * 1;
	
		//AJAX query
		$.post( "/member/banbyname/"+channel_id+"/"+username, 
		{ 
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
			
			//Skip empty data.
			if (!data || data === false || data === null) return false;
			
			//Remove member entries no longer present in channel.
			$('#memberlist .member').each( function(i, val2) {
				found = false;
				jQuery.each(obj, function(i, val) {
					if ('member_'+val.member_id == $(val2).attr('id'))
					{
						
						found = true;
					}
				});
				if (!found) $(val2).remove();
			});
		
			tools.sendHelpMessage('Banned user ' + username);
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
	},
	
	/**
	* Unban a member from this channel.
	* @param channel_id The channel ID use.
	* @param username Username of the user to unban.
	* @param sessionToken The form validation token for this session.
	* @returns void.
	*/
	unban : function (channel_id, username, sessionToken)
	{
		
		channel_id = channel_id * 1;
	
		//AJAX query
		$.post( "/member/unbanbyname/"+channel_id+"/"+username, 
		{ 
			token: sessionToken
		})
		.done(function( data ) {
				
			//Skip empty data.
			if (!data || data === false || data === null)
			{
				tools.sendHelpMessage('Unknown error attempting to unban ' + username, true);
				return false;
			}
		
			//Display errors.
			if (data.trim() != 'OK')
			{
				tools.sendHelpMessage(data, true);
				return false;
			}
			
			//Display success message.
			tools.sendHelpMessage('Unbanned user ' + username);
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
	},
	
	/**
	* Add a member to this channel.
	* @param channel_id The channel ID use.
	* @param username Username of the user to add.
	* @param sessionToken The form validation token for this session.
	* @returns void.
	*/
	add : function (channel_id, username, sessionToken)
	{
		
		channel_id = channel_id * 1;
	
		//AJAX query
		$.post( "/member/addbyname/"+channel_id+"/"+username, 
		{ 
			token: sessionToken
		})
		.done(function( data ) {
			
			//Skip empty data.
			if (!data || data === false || data === null)
			{
				tools.sendHelpMessage('Unknown error attempting to add user.', true);
				return false;
			}
		
			//Display errors.
			if (data.trim() != 'OK')
			{
				tools.sendHelpMessage(data, true);
				return false;
			}
		
			//Immediately update the members list without invoking an ongoing repeat.
			members.repeatCheckMembers(false); 
		
			tools.sendHelpMessage('Added user ' + username);
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
	},
	
	/**
	* Remove a member from this channel.
	* @param channel_id The channel ID use.
	* @param username Username of the user to remove.
	* @param sessionToken The form validation token for this session.
	* @returns void.
	*/
	remove : function (channel_id, username, sessionToken)
	{
		
		channel_id = channel_id * 1;
	
		 //AJAX query
		$.post( "/member/removebyname/"+channel_id+"/"+username, 
		{ 
			token: sessionToken
		})
		.done(function( data ) {
				
			//Skip empty data.
			if (!data || data === false || data === null)
			{
				tools.sendHelpMessage('Unknown error attempting to remove ' + username, true);
				return false;
			}
		
			//Display errors.
			if (data.trim() != 'OK')
			{
				tools.sendHelpMessage(data, true);
				return false;
			}
			
			//Immediately update the members list without invoking an ongoing repeat.
			members.repeatCheckMembers(false); 
			
			//Display success message.
			tools.sendHelpMessage('Removed user ' + username);
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
	},
	
	/**
	* Leave this channel.
	* @param channel_id The channel ID use.
	* @param sessionToken The form validation token for this session.
	* @returns void.
	*/
	leave : function (channel_id, sessionToken)
	{
		
		channel_id = channel_id * 1;
	
		 //AJAX query
		$.post( "/member/leave/"+channel_id+'/ajax', 
		{ 
			token: sessionToken
		})
		.done(function( data ) {
				
			//Skip empty data.
			if (!data || data === false || data === null)
			{
				tools.sendHelpMessage('Unknown error attempting to leave channel', true);
				return false;
			}
		
			//Display errors.
			if (data.trim() != 'OK')
			{
				tools.sendHelpMessage(data, true);
				return false;
			}
			
			tools.sendHelpMessage('Leaving channel. Removing you as a channel member.');
			//Leave channel after brief pause to give user a chance to see the leave message.
			chat.enableChecks = false;
			setTimeout(function(){
				window.location = '/channel/viewall';
			}, 2000);
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
	},
	
	/**
	* Set role for a member in this channel.
	* @param channel_id The channel ID use.
	* @param username The username of the member.
	* @param role The role to set.
	* @param sessionToken The form validation token for this session.
	* @returns void.
	*/
	role : function (channel_id, username, role, sessionToken)
	{
		
		channel_id = channel_id * 1;
	
		 //AJAX
		$.post( "/member/rolebyname/"+channel_id+"/"+username+"/"+role, 
		{ 
			token: sessionToken
		})
		.done(function( data ) {
			//Skip empty data.
			if (!data || data === false || data === null)
			{
				tools.sendHelpMessage('Unknown error attempting to set role for user ' + username, true);
				return false;
			}
		
			//Display errors.
			if (data.trim() != 'OK')
			{
				tools.sendHelpMessage(data, true);
				return false;
			}
			
			//Immediately update the members list without invoking an ongoing repeat.
			members.repeatCheckMembers(false); 
		
			tools.sendHelpMessage('User ' + username + ' role set to ' + role);
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
	},
	
	/**
	* Set game name for the "playing" string for the currently logged in user.
	* @param gamename The name of the game to set.
	* @param sessionToken The form validation token for this session.
	* @returns void.
	*/
	playing : function (gamename, sessionToken)
	{
		 //AJAX query
		$.post( "/member/playing/", 
		{ 
			token: sessionToken,
			gamename: gamename
		})
		.done(function( data ) {
			//Skip empty data.
			if (!data || data === false || data === null)
			{
				tools.sendHelpMessage('Unknown error attempting to set playing game name', true);
				return false;
			}
		
			//Display errors.
			if (data.trim() != 'OK')
			{
				tools.sendHelpMessage(data, true);
				return false;
			}
			
			//Immediately update the members list without invoking an ongoing repeat.
			members.repeatCheckMembers(false); 
		
			tools.sendHelpMessage('Playing status updated');
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
	},
	
	/**
	* Advertise a gaming stream.
	* @param channel_id The channel ID of the channel to advertise in.
	* @param streamname The name of the streaming account to use.
	* @param delay The length of delay (minutes or hours) before re-posting the stream advertisement.
	* @param delaytype The delay type (one of: "minute", "minutes", "hour", "hours"). 
	* @param sessionToken The form validation token for this session.
	* @returns void.
	*/
	stream : function (channel_id, streamname, delay, delaytype, sessionToken)
	{
		 //AJAX query
		$.post( "/message/post/"+channel_id, 
		{ 
			token: sessionToken,
			streamname: streamname,
			delay: delay,
			delaytype, delaytype
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
		
			tools.sendHelpMessage('Stream announcement added');
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
	},
	
	/**
	* Delete an account.
	* @param username The user name of the account to delete.
	* @param sessionToken The form validation token for this session.
	* @returns void.
	*/
	del : function (username, sessionToken)
	{
		 //AJAX query
		$.post( "/member/deletebyname/"+username, 
		{ 
			token: sessionToken
		})
		.done(function( data ) {
			//Skip empty data.
			if (!data || data === false || data === null)
			{
				tools.sendHelpMessage('Unknown error attempting to delete account', true);
				return false;
			}
		
			//Display errors.
			if (data.trim() != 'OK')
			{
				tools.sendHelpMessage(data, true);
				return false;
			}
			
			//Immediately update the members list without invoking an ongoing repeat.
			members.repeatCheckMembers(false); 
		
			tools.sendHelpMessage('User ' + username + ' deleted');
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
	},
	
	/**
	* Undelete an account.
	* @param username The user name of the account to undelete.
	* @param sessionToken The form validation token for this session.
	* @returns void.
	*/
	undel : function (username, sessionToken)
	{
		 //AJAX query
		$.post( "/member/undeletebyname/"+username, 
		{ 
			token: sessionToken
		})
		.done(function( data ) {
			//Skip empty data.
			if (!data || data === false || data === null)
			{
				tools.sendHelpMessage('Unknown error attempting to undelete account', true);
				return false;
			}
		
			//Display errors.
			if (data.trim() != 'OK')
			{
				tools.sendHelpMessage(data, true);
				return false;
			}
		
			tools.sendHelpMessage('User ' + username + ' UNdeleted');
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
	},
	
	/**
	* Set the password for an account.
	* @param username The user name of the account to set password for.
	* @param password The new password to use.
	* @param sessionToken The form validation token for this session.
	* @returns void.
	*/
	password : function (username, password, sessionToken)
	{
		 //AJAX query
		$.post( "/member/setpassword", 
		{ 
			token: sessionToken,
			username: username,
			password: password
		})
		.done(function( data ) {
			//Skip empty data.
			if (!data || data === false || data === null)
			{
				tools.sendHelpMessage('Unknown error attempting to set that account\'s password', true);
				return false;
			}
		
			//Display errors.
			if (data.trim() != 'OK')
			{
				tools.sendHelpMessage(data, true);
				return false;
			}
		
			tools.sendHelpMessage('User ' + username + ' password changed');
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
	},
	
	/**
	* Set the owner of the given channel.
	* @param channel_id The ID of the channel.
	* @param username The username of the new owner of the channel.
	* @param sessionToken The form validation token for this session.
	* @returns void.
	*/
	owner : function (channel_id, username, sessionToken)
	{
		
		channel_id = channel_id * 1;
	
		 //AJAX query
		$.post( "/channel/setownerbyname/"+channel_id+"/"+username, 
		{ 
			token: sessionToken
		})
		.done(function( data ) {
			//Skip empty data.
			if (!data || data === false || data === null)
			{
				tools.sendHelpMessage('Unknown error attempting to set new channel owner', true);
				return false;
			}
		
			//Display errors.
			if (data.trim() != 'OK')
			{
				tools.sendHelpMessage(data, true);
				return false;
			}
			
			//Immediately update the members list without invoking an ongoing repeat.
			members.repeatCheckMembers(false); 
		
			tools.sendHelpMessage('User ' + username + ' set to channel owner');
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
	},
	
	/**
	* Get user account details.
	* @param username The username of the new account to get details for.
	* @param sessionToken The form validation token for this session.
	* @returns void.
	*/
	details : function (username, sessionToken)
	{

		//AJAX query
		$.post( "/member/details/"+username, 
		{ 
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
			
			//Skip empty data.
			if (!data || data === false || data === null) return false;
			
			details = obj.details;
		
			tools.sendHelpMessage(details);
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
	},
}
