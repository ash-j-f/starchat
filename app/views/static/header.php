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
* Site header, displayed with all standard HTML pages.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
*/

include_once '../app/core/Config.php';

//Generate nonce to enable authorised inline scripts to identify themselves and execute.
$nonce = bin2hex(random_bytes(24));

?>
<!DOCTYPE html> 
<html>
	<head>
		<meta charset="UTF-8" /> 
		<meta http-equiv="Content-Security-Policy" content="script-src 'self' 'nonce-<?=$nonce?>' https://www.google.com/ https://www.gstatic.com/" />
		<title><?=Config::getConfigOption("SiteTitle") . ($pg_title ? " - " . $pg_title : "");?></title>
		<link rel="stylesheet" href="/css/style.css">
		<script type="text/javascript" src="https://www.google.com/recaptcha/api.js" async defer></script>
		<script type="text/javascript" src="/js/jquery-3.4.0.min.js"></script>
		<script type="text/javascript" src="/js/starchat-1.0-misc.js"></script>
		<script type="text/javascript" src="/js/starchat-1.0-member.js"></script>
		<script type="text/javascript" src="/js/starchat-1.0-message.js"></script>
		<script type="text/javascript" src="/js/starchat-1.0-game.js"></script>
		<link rel="shortcut icon" href="<?=Config::getConfigOption("FavIcon")?>" type="image/x-icon">
	</head>
	<body>
	
	<form id="logoutform" method="POST" action="/logout">
		<input type="hidden" name="token" value="<?=htmlentities($_SESSION['token']);?>" />
	</form>
	
	<script type="text/javascript" nonce="<?=$nonce?>">
		$(document).ready(function() 
		{ 
			$('#channel_sort').change(function() { window.location = '/channel/viewall' + $('#channel_sort').val(); });
			$('#logoutLink').click(function () { if (confirm('Are you sure you want to log out?')) { $('#logoutform').submit(); return false; } else { return false; } });
		});
	</script>
	
    <div class="logo <?=isset($pg_wide) && $pg_wide ? "logowide" : ""; ?>">
        <a href="/" class="nodecoration">StarChat</a>
        
        <? if ($_SESSION && isset($_SESSION['username'])) { ?>
        <div class="loggedinmessage <?=isset($pg_wide) && $pg_wide ? "loggedinmessagewide" : ""; ?>">
            <span style="<?=(isset($pg_sort_channels) && $pg_sort_channels) ? "" : "visibility: hidden;"; ?>">
            Sort by: <select id="channel_sort" class="nav">
                <? if (isset($pg_sort_channels) && $pg_sort_channels) { ?>
                <option value="" <?=$data['sort_order']=="joined" ? "selected" : ""; ?>>Recently Joined</option>
                <option value="/alpha" <?=$data['sort_order']=="alpha" ? "selected" : ""; ?>>Channel Name</option>
                <option value="/date" <?=$data['sort_order']=="date" ? "selected" : ""; ?>>Recently Created</option>
				<option value="/owner" <?=$data['sort_order']=="owner" ? "selected" : ""; ?>>Owner Name</option>
                <? } ?>
            </select>
            </span>
            <? if (isset($pg_wide) && $pg_wide) { ?>
            <a class="nav" href="/channel/viewall">All Channels</a>
            <a class="nav" href="/channel/new">New Channel</a>
			<a class="nav" href="/help">Help</a>
            <? } ?>
            <a href="/profile" style="text-decoration: none">Logged&nbsp;in&nbsp;as&nbsp;<span style="text-decoration: underline" class="headerUsername"><?=htmlentities($_SESSION['username']);?></span>.</a> &nbsp;&nbsp;<a id="logoutLink" href="#">Logout</a>&nbsp;
			<span class="headerPointsContainer"><div title="Game Points" class="headerPointsContainerText">♖<?=htmlentities($_SESSION['game_points'])?></div></span>
        </div>
        <? } ?>
    </div>
		
		
			
