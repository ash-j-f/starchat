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
* Profile view.
* Displays profile editing form to the user.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
*/

//View title.
$pg_title = "Profile";

include_once '../app/views/static/header.php';

?>

<script type="text/javascript" nonce="<?=$nonce?>">
	$(document).ready(function() 
	{ 
		$('#mainForm').submit(function(){return profile.submitCheck();});
		
		$('#changePasswordAnchor').click(function(){tools.showChangePassword();});
		
		var func = function() { tools.errorCheck($('#username'), 'usernameHint'); };
		$('#username').keyup(func); 
		$('#username').click(func);
		$('#username').focus(func);
		
		func = function() { tools.errorCheck($('#password'), 'currentpasswordHint'); };
		$('#password').keyup(func); 
		$('#password').click(func);
		$('#password').focus(func);
		
		func = function() { profile.passwordCheck(); tools.errorCheck($(this), 'newpasswordHint', 8); };
		$('#newpassword').keyup(func); 
		$('#newpassword').click(func);
		$('#newpassword').focus(func);
		
		func = function() { profile.passwordCheck(); };
		$('#newpassword2').keyup(func); 
		$('#newpassword2').click(func);
		$('#newpassword2').focus(func);
		
		func = function() { tools.errorCheck($(this), 'emailHint'); };
		$('#email').keyup(func); 
		$('#email').click(func);
		$('#email').focus(func);
	});
</script>

<div id="loginbox">

	<h1>
		Profile
	</h1>
	<? if (isset($data['error']) && $data['error']=="failed") { ?>
	<div class="error loginerror">
		Login failed - Please try again
	</div>
	<? } ?>
	<form id="mainForm" method="POST" action="/profilesave" autocomplete="off">
		<input type="hidden" name="token" value="<?=$_SESSION['token'];?>" />
		<div class="loginrow withborder">
			<div class="celllabel">
				<label for="username">Username</label>
			</div><!-- No carriage return here is deliberate. --><div class="cellinput">
				<input type="text" autocomplete="off" name="username" id="username" maxlength="16" value="<?=isset($_GET['username']) ? htmlentities($_GET['username']) :  htmlentities($data['user_data']['username']);?>" class="<? if (isset($data['error']) && ($data['error']=="invalid_username" || $data['error']=="username_used")) { ?>cellerror<? }?>" />
				<div id="usernameHint" class="hint">
					<? if (isset($data['error']) && $data['error']=="invalid_username") { ?><span class="error">Invalid! - </span><? } ?>
					<? if (isset($data['error']) && $data['error']=="username_used") { ?><span class="error">Already taken! - </span><? } ?>
					At least 3 characters and alphanumeric only.
				</div>	
			</div>
			<div style="clear: both;"></div>
		</div>
		<div class="loginrow withborder">
			<div class="celllabel">
				<label for="password">Current Password</label>
			</div><!-- No carriage return here is deliberate. --><div class="cellinput">
				<input type="password" autocomplete="off" name="password" id="password" value="" maxlength="128" class="<? if (isset($data['error']) && $data['error']=="authentication_fail") { ?>cellerror<? }?>" />
				<div id="currentpasswordHint" class="hint">
					<? if (isset($data['error']) && $data['error']=="authentication_fail") { ?><span class="error">Incorrect! - </span><? } ?>
					<strong>Current password to confirm your identity.</strong>
				</div>
			</div>
			<div style="clear: both;"></div>
		</div>
		
		<div id="newPasswordShow">
			<div class="loginrow withborder">
				<div class="celllabel">
					&nbsp;
				</div><!-- No carriage return here is deliberate. --><div class="cellinput" style="text-align: right;">
					<a id="changePasswordAnchor" href="#">Change Password</a>
				</div>
				<div style="clear: both;"></div>
			</div>
					
		</div>
		
		<div id="newPasswordHider" style="display: none;">
			<div class="loginrow withborder passwordChange">
				<div class="celllabel">
					<label for="newpassword">New Password</label>
				</div><!-- No carriage return here is deliberate. --><div class="cellinput">
					<input type="password" autocomplete="off" name="newpassword" id="newpassword" value="" maxlength="128" class="<? if (isset($data['error']) && $data['error']=="invalid_password") { ?>cellerror<? }?>" />
					<div id="newpasswordHint" class="hint">
						<? if (isset($data['error']) && $data['error']=="invalid_password") { ?><span class="error">Invalid! - </span><? } ?>At least 8 characters. <strong>Blank for no change.</strong>
					</div>
				</div>
				<div style="clear: both;"></div>
			</div>
			<div class="loginrow withborder passwordChange">
				<div class="celllabel">
					<label for="password">New Password <small>(again)</small></label>
				</div><!-- No carriage return here is deliberate. --><div class="cellinput">
					<input type="password" autocomplete="off" name="newpassword2" id="newpassword2" maxlength="128" />
					<div class="hint">
						Type your new password again to verify it.
					</div>
				</div>
				<div style="clear: both;"></div>
			</div>
		</div>
		<div class="loginrow withborder">
			<div class="celllabel">
				<label for="email">Email</label>
			</div><!-- No carriage return here is deliberate. --><div class="cellinput">
				<input type="text" autocomplete="off" name="email" id="email" maxlength="128" value="<?=isset($_GET['email']) ? htmlentities($_GET['email']) :  htmlentities($data['user_data']['email']);?>" class="<? if (isset($data['error']) && ($data['error']=="invalid_email" || $data['error']=="email_used")) { ?>cellerror<? }?>" />
				<div id="emailHint" class="hint">
					<? if (isset($data['error']) && $data['error']=="invalid_email") { ?><span class="error">Invalid email address!</span><? } ?>
					<? if (isset($data['error']) && $data['error']=="email_used") { ?><span class="error">This email is already in use by another account!</span><? } ?>
				</div>
			</div>
			<div style="clear: both;"></div>
		</div>
		
		<? if (isset($data['error']) && $data['error']=="invalid_password"): ?>
		<script type="text/javascript" nonce="<?=$nonce?>">
			//There was an error with the new password, so show the change password fields.
			$(document).ready(tools.showChangePassword());
		</script>
		<? endif;?> 
		
		<div class="loginrow withborder">
			<div class="celllabel">
				<label for="">Avatar Image</label>
			</div><!-- No carriage return here is deliberate. --><div class="cellinput moreheight">
				<div style="width: 40px; float: left; padding: 5px 0 0 0;">
					<div class="avatar_box"><img class="avatar" src="<?=htmlentities($data['user_data']['avatar_url'])?>" /></div>
				</div>
				<div style="width: 350px; float: left; padding: 0 0 0 20px;">
					<div class="profile_avatartext">Upload your avatar image to <a href="https://en.gravatar.com/connect/">Gravatar</a>, using the email you linked to this profile.</div>
				</div>
				<div style="clear: both;"></div> 
			</div>
			<div style="clear: both;"></div>
		</div>
		
		<div class="loginrow withborder">
			<div class="celllabel">
				<label for="steam">Steam Account</label>
			</div><!-- No carriage return here is deliberate. --><div class="cellinput">
				<input type="text" name="steam" id="steam" maxlength="128" value="<?=isset($_GET['steam']) ? htmlentities($_GET['steam']) :  htmlentities($data['user_data']['steam']);?>" />
				<div class="hint">
					Your user account at <a href="https://store.steampowered.com/">https://store.steampowered.com/</a>.
				</div>
			</div>
			<div style="clear: both;"></div>
		</div>
		
		<div class="loginrow withborder">
			<div class="celllabel">
				<label for="twitch">Twitch Account</label>
			</div><!-- No carriage return here is deliberate. --><div class="cellinput">
				<input type="text" name="twitch" id="twitch" maxlength="128" value="<?=isset($_GET['twitch']) ? htmlentities($_GET['twitch']) :  htmlentities($data['user_data']['twitch']);?>" />
				<div class="hint">
					Your user account at <a href="https://www.twitch.tv/">https://www.twitch.tv/</a>.
				</div>
			</div>
			<div style="clear: both;"></div>
		</div>
		
		<div class="loginrow withborder" style="height: 110px;">
			<div class="celllabel">
				<label for="bio">Profile Message</label>
			</div><!-- No carriage return here is deliberate. --><div class="cellinput">
				<textarea name="bio" id="bio" maxlength="128" ><?=isset($_GET['bio']) ? htmlentities($_GET['bio']) :  htmlentities($data['user_data']['bio']);?></textarea>
				<div class="hint">
					A short message to display with your profile.
				</div>
			</div>
			<div style="clear: both;"></div>
		</div>
		
		<div class="loginrow withborder">
			<input type="submit" name="Submit" value="Save" class="button" style="float: right;" />
			<div style="clear: both;"></div>
		</div>
		<div class="loginrow">
			<div class="loginhelp">
				<a href="/channel/viewall">Channels</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="/help">Need Help?</a>
			</div>
		</div>
	</form>
</div>


<?php

include_once '../app/views/static/footer.php';

?>
