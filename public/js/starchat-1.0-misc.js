/**
* StarChat miscelaneous objects.
* @author Ashley Flynn - Academy of Interactive Entertainment and the Canberra Institute of Technology - 2019.
*/

/**
* Constants and settings specifically for the chat page.
* Many of these are set by the Chat page on page load.
*/
var chat = 
{
	
	/**
	* Is the user currently typing into the message box?
	*/
	isTyping : false,
	
	/**
	* Is flash effect for flash "(name) is typing..." message at the bottom of the messages list active?
	*/
	isTypingMessageFlash : false,
	
	/**
	* Last request time in epoch time.
	*/
	lastRequestTimeEpoch : 0,

	/**
	* Max number of messages to display in message list. Set by chat page on page load.
	*/
	maxMessages : 0,

	/**
	 * Enable or disable timed ajax calls to check members, messages, etc.
	 */
	enableChecks : true,

	/**
	* Time before next message by same user must show the date and time.
	*/
	showNextDateTimeoutSeconds : 60,

	/**
	* Current channel ID for this page. Set by chat on page load.
	*/
	current_channel_id : 0,

	/**
	* A counter to assign client-side help messages a unique ID.
	*/
	helpMessageID : 0,

	/**
	* Is the message list scroll bar at the bottom-most position?
	*/
	scrollIsAtBottom : true,

	/**
	* Max messages back in time to display at once.
	*/
	maxMessages : 0, 
	
	/**
	* Min time between messages from same member before showing timestamp again.
	*/
	showNextDateTimeoutSeconds : 0,
	
	/**
	* Channel ID of the current channel.
	*/
	current_channel_id : 0,
	
	/**
	* Form security token.
	*/
	token : '',
	
	/**
	* Interval for checking for new messages.
	*/
	messageCheckIntervalMS : 0,
	
	/**
	* Interval for checking for member list updates.
	*/
	memberCheckIntervalMS : 0,
	
	/**
	* How often to update elapsed time objects in the page.
	*/
	updateTimesIntervalMS : 0,
	
	/**
	* How often to update logged in member's "is typing" status.
	*/
	updateIsTypingIntervalMS : 0
}

/**
* Miscelaneous helper tools.
*/
var tools = 
{	
	/**
	* Show the "change password" fields on the profile page.
	* @returns void.
	*/
	showChangePassword : function() 
	{
		$('#newPasswordHider').show(); 
		$('#newPasswordShow').hide();
	},

	/**
	* Remove error classes and hint error for a field.
	* Optionally, check if field length is over a required minimum.
	* @param jQObj HTML object.
	* @param hintID The CSS ID of the hint object.
	* @param lengthCheck bool True to check the length of the incoming string matches password minimum length, false to ignore length.
	* @returns void.
	*/
	errorCheck : function (jQObj, hintID, lengthCheck=0)
	{
		if (jQObj.hasClass('cellerror') && (lengthCheck ? jQObj.val().length >= 8 || jQObj.val().length == 0: true)) 
		{ 
			jQObj.removeClass('cellerror'); 
			$('#'+hintID+' .error').hide();
		}
	},

	/**
	* Send help message.
	* @returns void.
	*/
	sendHelpMessage : function(message, error = false, html = false)
	{
		
		//Don't repeat help messages.
		if (messageSender.lastMessageRawConent != message || messageSender.lastMessageBy != null)
		{
			messageObject.createMessageObject('helpmessage' + chat.helpMessageID, null, message, 't', null, null, 0, 0, null, error, html);
			
			messageSender.lastMessageID = 'helpmessage' + chat.helpMessageID;
			messageSender.lastMessageBy = null;
			messageSender.lastMessageTime = null;
			messageSender.lastMessageRawConent = message;

			chat.helpMessageID++;
		
			//Scroll message box to bottom.
			messageObject.scrollToBottom();
		}
		else
		{
			flashSpeed = 150;
			$('#message_'+messageSender.lastMessageID+ ' .message_content').fadeOut(flashSpeed).fadeIn(flashSpeed).fadeOut(flashSpeed).fadeIn(flashSpeed);
		}
		
	},
	
	/**
	* General server connection error message.
	* @returns void.
	*/
	serverErrorMessage : function()
	{
		tools.sendHelpMessage('There was an error while attempting to contact the server.', true);
	},
	
	/**
	* Get the number of seconds since epoch, based on client system time.
	* @returns The number of seconds since epoch, based on client system time.
	*/
	getEpochNow : function()
	{
		return Math.floor(Date.now()/1000);
	},

	/**
	* Update all elapsed time objects in page.
	* Objects must be spans or divs with class "timeObject".
	* They must have the property "epoch" with epoch in seconds.
	* The may have an optional property "humanDate" which is the exact date and time in a
	* human readable format.
	* @returns void.
	*/
	updateTimes : function()
	{
		allTimeObjects = $('.timeObject');
		allTimeObjects.each(function(i, val) 
		{
			epoch_date = $(val).attr('epoch');
			human_date = $(val).attr('humanDate');
			
			if (human_date) 
			{
				human_date = ' ('+human_date+')';
			}
			else
			{
				human_date = '';
			}
			
			$(val).text(tools.humanDateElapsed(epoch_date, tools.getEpochNow()) +' ago'+human_date);
		});
	},

	/**
	* Return a human-friendly version of the an epoch time as number of days, minutes, seconds etc from the current time.
	* Eg: "1 day, 2 hours".
	* @param targettime int The EPOCH seconds of the target time (a time in the past) to measure.
	* @param curtime int The current time in EPOCH seconds.
	* @returns A human-friendly version of the an epoch time as number of days, minutes, seconds etc from the current time.
	*/
	humanDateElapsed : function (targettime, curtime) 
	{
		
		//Array of time period chunks
		var a = new Array(60 * 60 * 24 * 365 , 'year');
		var b = new Array(60 * 60 * 24 * 30 , 'month');
		var c = new Array(60 * 60 * 24 * 7, 'week');
		var d = new Array(60 * 60 * 24 , 'day');
		var e = new Array(60 * 60 , 'hr');
		var f = Array(60 , 'min');
		
		var chunks = new Array(a,b,c,d,e,f);
		
		today = curtime;
		since = today - targettime;

		//Less than a minute ago?
		if (since < 60) {
			return "a moment";
		}
		
		//j saves performing the count function each time around the loop
		for (i = 0, j = chunks.length; i < j; i++) {
			
			seconds = chunks[i][0];
			name1 = chunks[i][1];
			
			//Finding the biggest chunk (if the chunk fits, break)
			if ((count = Math.floor(since / seconds)) != 0) {
				break;
			}
		}
		
		printx = (count == 1) ? '1 '+name1 : count+' '+name1+'s';
		
		if (i + 1 < j) {
			//Now getting the second item
			seconds2 = chunks[i + 1][0];
			name2 = chunks[i + 1][1];
			
			//Add second item if it's greater than 0
			if ((count2 = Math.floor((since - (seconds * count)) / seconds2)) != 0) {
			printx += (count2 == 1) ? ', 1 '+name2 : ', '+count2+' '+name2+'s';
			}
		}
		
		return printx;
	}
}

/**
 * Signup page functions.
 */
var signup = 
{
	
	/**
	* Check that passwords in the two password fields match.
	* If they match, clear error colour. If they do not match, mark the second password field red.
	* @returns void.
	*/
    passwordCheck: function ()
    {
        if ($('#password').val() == $('#password2').val())
        {
            $('#password2').removeClass("cellerror");
        }
        else
        {
            $('#password2').addClass("cellerror");
        }
    },
    
	/**
	* Check if passwords in the two password fields match.
	* @returns bool True if they match, false if not.
	*/
    submitCheck: function()
    {
        if ($('#password').val() != $('#password2').val())
        {
            alert("The two passwords do not match.");
            return false;
        }
        return true;
    }
}

/**
 * Profile page functions.
 */
var profile = 
{
	/**
	* Check that passwords in the two password fields match.
	* If they match, clear error colour. If they do not match, mark the second password field red.
	* @returns void.
	*/
    passwordCheck: function ()
    {
        if ($('#newpassword').val() == $('#newpassword2').val())
        {
            $('#newpassword2').removeClass("cellerror");
        }
        else
        {
            $('#newpassword2').addClass("cellerror");
        }
    },
    
	/**
	* Check if passwords in the two password fields match.
	* @returns bool True if they match, false if not.
	*/
    submitCheck: function()
    {
        if ($('#newpassword').val() != $('#newpassword2').val())
        {
            alert("The two new passwords do not match.");
            return false;
        }
        return true;
    }
}