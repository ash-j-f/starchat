<?php

/**
* New channel view.
* Displays the "New Channel" creation form.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

//View title.
$pg_title = "New Channel";

//Do not use wide view mode (stretch content to fill page width).
$pg_wide = false;

include_once '../app/views/static/header.php';

$channelname = "";
$channeldesc = "";
$channelpublic = "";

//Use incoming channel data if in edit mode.
if (isset($data['channel_data']))
{
	$channelname = $data['channel_data'][0]['channelname'];
	$channeldesc = $data['channel_data'][0]['channeldesc'];
	$channelpublic = $data['channel_data'][0]['public'];
}

//Override incoming channel data with returned GET strings (used when error message is displayed).
if (isset($_GET['channelname'])) $channelname = $_GET['channelname'];
if (isset($_GET['channeldesc'])) $channeldesc = $_GET['channeldesc'];
if (isset($_GET['channelpublic'])) $channelpublic = $_GET['channelpublic'];

?>

<script type="text/javascript" nonce="<?=$nonce?>">
	$(document).ready(function() 
	{ 
		$('#channelNameInput').keyup(function(){tools.errorCheck($('#channelNameInput'), 'channelnameHint');}); 
		$('#channelNameInput').click(function(){tools.errorCheck($('#channelNameInput'), 'channelnameHint');});
		$('#channelNameInput').focus(function(){tools.errorCheck($('#channelNameInput'), 'channelnameHint');});
		$('#cancelButton').click(function(){window.location='/channel/viewall/'; return false;});
	});
</script>

<div id="loginbox">

	<h1>
		<? if (isset($data['channel_data'])) { ?>
		Edit Channel
		<? } else { ?>
		New Channel
		<? } ?>
	</h1>

	<form method="POST" action="<?=isset($data['channel_data']) ? "/channel/save/".htmlentities($data['channel_data'][0]['channel_id']) : "/channel/create";?>">
		<input type="hidden" name="token" value="<?=$_SESSION['token'];?>" />

		<div class="loginrow withborder">
		
			<div class="celllabel">
				<label for="username">Channel Name</label>
			</div><!-- No carriage return here is deliberate. --><div class="cellinput">
				<input id="channelNameInput" type="text" name="channelname" maxlength="48" value="<?=htmlentities($channelname)?>" class="<? if (isset($data['error']) && ($data['error']=="invalid_channelname" || $data['error']=="channelname_used")) { ?>cellerror<? }?>" />
				<div id="channelnameHint" class="hint">
					<? if (isset($data['error']) && $data['error']=="invalid_channelname") { ?><span class="error">Invalid!</span><? } ?>
					<? if (isset($data['error']) && $data['error']=="channelname_used") { ?><span class="error">Already taken!</span><? } ?>
					<!--Alphanumeric and spaces only.-->
				</div>	
			</div>
			<div style="clear: both;"></div>
				
		</div>
		
		<div class="loginrow withborder">
			<div class="celllabel">
				<label for="username">Channel Description</label>
			</div><!-- No carriage return here is deliberate. --><div class="cellinput">
				<textarea name="channeldesc" maxlength="128" ><?=htmlentities($channeldesc)?></textarea>
			</div>
			<div style="clear: both;"></div>
		</div>
		
		<div class="loginrow withborder">
			<div class="celllabel">
				<label for="public">Public</label>
			</div><!-- No carriage return here is deliberate. --><div class="cellinput">
				<input type="checkbox" id="public" name="public" value="t" <?= $channelpublic == 't' ? "checked" : ""; ?> /><!--This invisible label is deliverate! Do not remove: --><label for="public"></label>
			</div>
			<div style="clear: both;"></div>
		</div>
	
		<div class="loginrow withborder">
			<input type="submit" name="Submit" value="<?=isset($data['channel_data']) ? "Save" : "Create";?>" class="button" style="float: right;" />
			<input id="cancelButton" type="button" value="Cancel" class="button" style="float: left;" />
			<div style="clear: both;"></div>
		</div>

	</form>

	
</div>


<?php

include_once '../app/views/static/footer.php';

?>
