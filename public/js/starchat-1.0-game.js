/**
* StarChat game objects.
* @author Ashley Flynn - Academy of Interactive Entertainment and the Canberra Institute of Technology - 2019.
*/

/**
* Game controls.
*/
var game =
{
	/**
	* Issue a command to a game running in a given channel.
	* @param channel_id The channel ID to use.
	* @param command The command to issue.
	* @param sessionToken The form validation token for this session.
	* @returns void.
	*/
	command : function(channel_id, command, sessionToken)
	{
		channel_id = channel_id * 1;
		
		//AJAX query
		$.post( "/game/command/"+channel_id, 
		{ 
			command: command,
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
			
			if (!obj.message_htmlsafe)
			{
				tools.sendHelpMessage("An unknown error occurred while trying to issue that game command.", true);
			}
		
			tools.sendHelpMessage(obj.message_htmlsafe, false, true);
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
	},
	
	/**
	* List all available game types.
	* @returns void.
	*/
	listGames : function()
	{
		//AJAX query
		$.post( "/game/listgames", 
		{ 
		})
		.done(function( data ) {
			var obj;
			//Check the incoming data is a valid JSON object.
			try {
				obj = JSON.parse(data);
			} catch (e) {
				//Content error. Response was not JSON.
				//alert(data);
				
				tools.sendHelpMessage(data, true);
					
				return false;
			}
			
			//Skip empty data.
			if (!data || data === false || data === null) return false;
			
			if (!obj.message_htmlsafe)
			{
				tools.sendHelpMessage("An unknown error occurred while trying to list games.", true);
			}
		
			tools.sendHelpMessage(obj.message_htmlsafe, false, true);
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
	},
	
	/**
	* List rules for a given game type.
	* @param gameType The game type to list rules for.
	* @returns void.
	*/
	listRules : function(gameType)
	{
		 //AJAX query
		$.post( "/game/listrules/"+gameType, 
		{ 
		})
		.done(function( data ) {
			var obj;
			//Check the incoming data is a valid JSON object.
			try {
				obj = JSON.parse(data);
			} catch (e) {
				//Content error. Response was not JSON.
				//alert(data);
				
				tools.sendHelpMessage(data, true);
					
				return false;
			}
			
			//Skip empty data.
			if (!data || data === false || data === null) return false;
			
			if (!obj.message_htmlsafe)
			{
				tools.sendHelpMessage("An unknown error occurred while trying to list game rules.", true);
			}
		
			tools.sendHelpMessage(obj.message_htmlsafe, false, true);
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
	},
	
	/**
	* Start a given game type in a given channel.
	* @param channel_id The channel ID to use.
	* @param gameName The name of the game type to start.
	* @param sessionToken The form validation token for this session.
	* @returns void.
	*/
	start : function (channel_id, gameName, sessionToken)
	{
		
		channel_id = channel_id * 1;
	
		//AJAX query
		$.post( "/game/start/"+channel_id+"/"+gameName, 
		{ 
			token: sessionToken
		})
		.done(function( data ) {
			//Skip empty data.
			if (!data || data === false || data === null)
			{
				tools.sendHelpMessage('Unknown error attempting to start game', true);
				return false;
			}
		
			//Display errors.
			if (data.trim() != 'OK')
			{
				tools.sendHelpMessage(data, true);
				return false;
			}
		
			tools.sendHelpMessage('Game started.');
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
	},
	
	/**
	* Stop a game running in a given channel.
	* @param channel_id The channel ID to use.
	* @param sessionToken The form validation token for this session.
	* @returns void.
	*/
	stop : function (channel_id, sessionToken)
	{
		
		channel_id = channel_id * 1;
	
		//AJAX query
		$.post( "/game/stop/"+channel_id, 
		{ 
			token: sessionToken
		})
		.done(function( data ) {
			//Skip empty data.
			if (!data || data === false || data === null)
			{
				tools.sendHelpMessage('Unknown error attempting to stop game', true);
				return false;
			}
		
			//Display errors.
			if (data.trim() != 'OK')
			{
				tools.sendHelpMessage(data, true);
				return false;
			}
		
			tools.sendHelpMessage('Game stopped.');
		
		})
		.fail(function() {
			//Failed to send request from browser to client (or server returned an HTTP error, etc).
			tools.serverErrorMessage();
		});
	},
}
