<?php

/**
* Chat view.
* Displays the main chat window.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

//View title.
$pg_title = "Channels";

//Use wide view mode (stretch content to fill page width).
$pg_wide = true;

include_once '../app/views/static/header.php';

?>

<script type="text/javascript" nonce="<?=$nonce?>">
	
	/**
	* Run these actions when page is ready.
	* @returns void.
	*/
	$(document).ready(function() 
	{ 
		$('#delWarningAnchor').click(function(){if(confirm('Are you sure you want to delete this channel?')) $('#channel_delete_<?=htmlentities($data['channel_data'][0]['channel_id'])?>').submit();});
	
		$('#scrollToLatestAnchor').click(function(){messageObject.scrollToBottom(true);});
	
		//Set page constants.
		chat.maxMessages = <?=Config::getConfigOption("MaxDisplayMessages");?>; 
		chat.showNextDateTimeoutSeconds = <?=Config::getConfigOption("ShowNextDateTimeoutSeconds");?>;
		chat.current_channel_id = <?=htmlentities($data['channel_data'][0]['channel_id']);?>;
		chat.token = '<?=$_SESSION['token']?>';
		chat.messageCheckIntervalMS = <?=Config::getConfigOption("MessageCheckIntervalMS");?>;
		chat.memberCheckIntervalMS = <?=Config::getConfigOption("MemberCheckIntervalMS");?>;
		chat.updateTimesIntervalMS = <?=Config::getConfigOption("UpdateTimesIntervalMS");?>;
		chat.updateIsTypingIntervalMS = <?=Config::getConfigOption("UpdateIsTypingIntervalMS");?>;
		
		//Check for messages regularly.
		messageReceiver.repeatCheckMessages();
		
		//Check for changes to member list regularly.
		members.repeatCheckMembers();
		
		//Update elapsed times in page regularly.
		setInterval(tools.updateTimes, chat.updateTimesIntervalMS);
		
		//Transmit user typing status regularly.
		setInterval(members.updateIsTypingSend, chat.updateIsTypingIntervalMS, chat.current_channel_id, chat.token);
		
		//Set events for user message input box.
		$('#usermessage').on('paste', messageSender.updateMessage);
		$('#usermessage').on('cut', messageSender.updateMessage);
		$('#usermessage').keydown(messageSender.updateMessage);
		
		//Scroll message list to bottom if window is resized.
		//Stops message window scrolling to a random point.
		$(window).on('resize', function(){
			messageObject.scrollToBottom();
			messageObject.updateMessageWidthScrollToBottom();
		});

		//Scroll message list to bottom on page load.
		$('#messagelist').bind('scroll', messageObject.checkScroll);
		messageObject.updateMessageWidthScrollToBottom();
		
		//Focus on message input box on page load.
		$('#usermessage').focus();
		
		//Flash the "is typing" message if the typing class is set.
		setInterval(function() {
			if ($('#messagespacer').hasClass('typing') && !chat.isTypingMessageFlash) 
			{
				chat.isTypingMessageFlash = true;
				//Fade message in, then out, then set flash effect flag back to false.
				$('#messagespacer').fadeTo(500, 1.0, function() { $('#messagespacer').fadeTo(500, 0.5, function() { chat.isTypingMessageFlash = false; }); })
			}
		}, 50);
	});
	
</script>

<div id="profileBox" style="display: none;">
	<div id="closeButton">
		<a href="#">close</a>
	</div>
	<div id="profileTitle">
		Profile
	</div>
	<div id="profileLine">
	</div>
	<div class="avatar_box"><img class="avatar" src="https://www.gravatar.com/avatar/bd5789d233e4f3985d29e2d53d0fc0c7?r=x&d=identicon&s=128" /></div>
	
	<div id="profileUsername">
		Username
	</div>
	<div id="profileSteamIcon">
		<img src="/images/steam.png" />
	</div>
	<div id="profileSteam">
		Steam
	</div>
	<div id="profileTwitchIcon">
		<img src="/images/twitch.png" />
	</div>
	<div id="profileTwitch">
		Twitch
	</div>
	<div id="profileBio">
		Profile Message
	</div>
	
</div>

<div id="widebox">

	<h2>
		<?=htmlentities($data['channel_data'][0]['channelname'])?>
		<? if ($data['channel_data'][0]['public'] != "t"): ?>
			<span class="channelPrivate">(Private)</span>
		<? endif; ?>
		
		<? if ($data['channel_data'][0]['owner_id'] == $_SESSION['user_id'] || $data['channel_data'][0]['is_admin'] == 't' || $data['is_superuser']=="yes") { ?>
		<div class="channelControls">
			
			<a href="/channel/edit/<?=htmlentities($data['channel_data'][0]['channel_id'])?>">Edit</a>
			
			<? if ($data['channel_data'][0]['owner_id'] == $_SESSION['user_id'] || $data['is_superuser']=="yes") { /* Only show delete link to owner or superuser. */?>
			<form style="display: inline;" id="channel_delete_<?=htmlentities($data['channel_data'][0]['channel_id'])?>" method="POST" action="/channel/delete/<?=htmlentities($data['channel_data'][0]['channel_id'])?>">
				<input type="hidden" name="token" value="<?=$_SESSION['token'];?>" />
				<a id="delWarningAnchor" class="warning" href="#">Delete</a>
			</form>
			<? } ?>
		
		</div>
		<? } ?>
		
	</h2>
	
	<div id="chatbox">
		
		<div id="messages">
			<div id="messagelistScrollToBottom">
				Viewing older messages. <a id="scrollToLatestAnchor" href="#">Scroll to latest</a>
			</div>
			<div id="messagelist">
				<div id="messagespacer" class="typing"></div>
			</div>
			<div id="newmessageBG">
				Type something... and press Enter to send. Or type /? for help.
			</div>
			<div id="newmessage">
				<form>
					<textarea maxlength="1024" id="usermessage" name="usermessage"></textarea>
				</form>
			</div>
		</div>
		
		<div id="memberlist">
			<div id="memberspacer">&nbsp;</div>
		</div>
		
		<div style="clear: both;"></div>
		
	</div>
	
</div>


<?php

include_once '../app/views/static/footer.php';

?>
