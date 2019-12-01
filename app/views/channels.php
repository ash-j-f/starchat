<?php

/**
* Channels view.
* Displays all chat channels that the user can access.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

//View title.
$pg_title = "Channels";

//Use wide view mode (stretch content to fill page width).
$pg_wide = true;

//Show channel sort options dropdown.
$pg_sort_channels = true;

include_once '../app/views/static/header.php';

?>

<script type="text/javascript" nonce="<?=$nonce?>">

	//Update time objects in page.
	function setIntervalRepeatUpdateTimes()
	{
		tools.updateTimes();
		setInterval(tools.updateTimes, chat.updateTimesIntervalMS);
	}
	
	$(document).ready(function() 
	{ 
		chat.updateTimesIntervalMS = <?=Config::getConfigOption("UpdateTimesIntervalMS");?>;
	});
	
	$(document).ready(setIntervalRepeatUpdateTimes);
	
</script>

<div id="widebox">

	<h1>
		Channels
	</h1>
	
	<?
	if ($data['channels']) foreach ($data['channels'] as $channel)
	{
	?>
		
	<div class="loginrow withborder plaintext lesspadding">
		<div class="channelAvatar">
			<img src="<?=$channel['avatar_url']?>" />
		</div>
		<div class="channelDetails">
			<div class="channelTitle">
				<?if ($channel['messages_waiting'] != 0):?>
					<span class="channelMessageWaiting" title="<?=$channel['messages_waiting'];?> New Message<?=$channel['messages_waiting'] != 1 ? "s" : "";?>">&bull;<?=$channel['messages_waiting'];?></span>
				<? endif; ?>
				<a href="/chat/<?=htmlentities($channel['channel_id'])?>"><?=htmlentities($channel['channelname']);?></a>
				<? if ($channel['public'] != "t"): ?>
					<span class="channelPrivate">(Private)</span>
				<? endif; ?>
				<div class="channelControls">
					<? if ($channel['owner_id'] == $_SESSION['user_id'] || $channel['is_admin'] == 't' || $data['is_superuser']=="yes") { ?>
						<a href="/channel/edit/<?=htmlentities($channel['channel_id'])?>">Edit</a>
						<? if ($data['is_superuser']=="yes" || $channel['owner_id'] == $_SESSION['user_id']) { /* Only show delete link to owner or superuser. */?>
						<form style="display: inline;" id="channel_delete_<?=htmlentities($channel['channel_id'])?>" method="POST" action="/channel/delete/<?=htmlentities($channel['channel_id'])?>">
							<input type="hidden" name="token" value="<?=$_SESSION['token'];?>" />
							<a id="delWarningAnchor-<?=htmlentities($channel['channel_id']);?>" class="warning" href="#">Delete</a>
						</form>
						<? } ?>
					<? } ?>
				</div>
			</div>
			<div class="channelDesc">
				<?=htmlentities($channel['channeldesc']);?>
			</div>
			<div class="channelInfo">
			
				<span style="font-weight: bold;">
					<?=htmlentities($channel['member_count']);?> member<?=$channel['member_count'] != 1 ? "s" : "";?>
				</span>
			
				<span>
					Owner: <?=htmlentities($channel['ownername']);?> 
				</span>
				
				<span>
					Created: <span class="timeObject" epoch="<?=htmlentities($channel['creation_date']);?>"></span>
				</span>
				
				<? if ($channel['joined_date'] != ""): ?>
				<span style="color: #CB410B;">
					Joined: <span class="timeObject" epoch="<?=htmlentities($channel['joined_date']);?>"></span>
				</span>
				<? endif; ?>
			</div>
		</div>
		<div style="clear: both;"></div>
	</div>
	
	<script type="text/javascript" nonce="<?=$nonce?>">
		$('#delWarningAnchor-<?=htmlentities($channel['channel_id']);?>').click(function() { if(confirm('Are you sure you want to delete this channel?')) $('#channel_delete_<?=htmlentities($channel['channel_id'])?>').submit(); });
	</script>
	
	<?
	} else {
	?>
	<div class="loginrow withborder plaintext">
		No channels to list. Create a new channel with the "New Channel" link above.
	</div>
	<?
	}
	?>
	
</div>


<?php

include_once '../app/views/static/footer.php';

?>
