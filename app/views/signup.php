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
* Signup view.
* Displays account signup form to the user.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
*/

//View title.
$pg_title = "Sign Up";

include_once '../app/views/static/header.php';

?>

<script type="text/javascript" nonce="<?=$nonce?>">
	$(document).ready(function() 
	{ 
		$('#mainForm').submit(function(){return signup.submitCheck();});
	
		var func = function() { tools.errorCheck($('#username'), 'usernameHint'); };
		$('#username').keyup(func); 
		$('#username').click(func);
		$('#username').focus(func);
		
		func = function() { signup.passwordCheck(); tools.errorCheck($('#password'), 'passwordHint', 8); };
		$('#password').keyup(func); 
		$('#password').click(func);
		$('#password').focus(func);
		
		func = function() { signup.passwordCheck(); };
		$('#password2').keyup(func); 
		$('#password2').click(func);
		$('#password2').focus(func);
		
		func = function(){tools.errorCheck($('#email'), 'emailHint');};
		$('#email').keyup(func); 
		$('#email').click(func);
		$('#email').focus(func);
		
	});
</script>

<div id="loginbox">

	<h1>
		Sign Up
	</h1>
	<form id="mainForm" method="POST" action="/createuser">
		<input type="hidden" name="token" value="<?=$_SESSION['token'];?>" />
	
		<div class="loginrow withborder">
			<div class="celllabel">
				<label for="username">Username</label>
			</div><!-- No carriage return here is deliberate. --><div class="cellinput">
				<input type="text" autocomplete="off" name="username" id="username" maxlength="16" value="<?=isset($_GET['username']) ? htmlentities($_GET['username']) : "";?>" class="<? if (isset($data['error']) && ($data['error']=="invalid_username" || $data['error']=="username_used")) { ?>cellerror<? }?>" />
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
				<label for="password">Password</label>
			</div><!-- No carriage return here is deliberate. --><div class="cellinput">
				<input type="password" autocomplete="off" name="password" id="password" maxlength="128" class="<? if (isset($data['error']) && $data['error']=="invalid_password") { ?>cellerror<? }?>" />
				<div class="hint" id="passwordHint">
					<? if (isset($data['error']) && $data['error']=="invalid_password") { ?><span class="error">Invalid! - </span><? } ?>At least 8 characters.
				</div>

			</div>
			<div style="clear: both;"></div>
		</div>
		<div class="loginrow withborder">
			<div class="celllabel">
				<label for="password">Password <small>(again)</small></label>
			</div><!-- No carriage return here is deliberate. --><div class="cellinput">
				<input type="password" autocomplete="off" name="password2" id="password2" maxlength="128" />
				<div class="hint">
					Type your password again to verify it.
				</div>
			</div>
			<div style="clear: both;"></div>
		</div>
		<div class="loginrow withborder">
			<div class="celllabel">
				<label for="email">Email</label>
			</div><!-- No carriage return here is deliberate. --><div class="cellinput">
				<input type="text" name="email" id="email" maxlength="128" value="<?=isset($_GET['email']) ? htmlentities($_GET['email']) : "";?>" class="<? if (isset($data['error']) && ($data['error']=="invalid_email" || $data['error']=="email_used")) { ?>cellerror<? }?>" />
				<div class="hint" id="emailHint">
					<? if (isset($data['error']) && $data['error']=="invalid_email") { ?><span class="error">Invalid email address!</span><? } ?>
					<? if (isset($data['error']) && $data['error']=="email_used") { ?><span class="error">This email is already in use by another account!</span><? } ?>
				</div>
			</div>
			<div style="clear: both;"></div>
		</div>
		<? if (Config::getConfigOption("EnableCaptcha")): ?>
		<div class="loginrow withborder plaintext">
			<div style="float: right;" class="g-recaptcha" data-sitekey="<?=htmlentities(Config::getConfigOption("GoogleCaptchaClientPublicKey"));?>"></div>
			<div style="clear: both;"></div>
			<div class="hint">
				<? if (isset($data['error']) && $data['error']=="recaptcha_fail") { ?><span class="error">Please tick the box to confirm you are not a robot.</span><? } ?>
			</div>
		</div>
		<? endif; ?>
		<div class="loginrow withborder">
			<input type="submit" name="Submit" value="Sign Up" class="button" style="float: right;" />
			<div style="clear: both;"></div>
		</div>
	</form>
	<div class="loginrow">
		<div class="loginhelp">
			<a href="/help">Need Help?</a>
		</div>
	</div>
	
</div>


<?php

include_once '../app/views/static/footer.php';

?>
