<?php

/**
* Login view.
* Displays login form to the user.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

//View title.
$pg_title = "Login";

include_once '../app/views/static/header.php';

?>


<div id="loginbox">

	<h1>
		Login
	</h1>
	<? if (isset($data['error']) && $data['error']=="failed") { ?>
	<div class="error loginerror">
		Login failed - Please try again
	</div>
	<? } ?>
	<form method="POST" action="/loginuser">
		<input type="hidden" name="token" value="<?=$_SESSION['token'];?>" />
		<div class="loginrow withborder">
			<div class="celllabel">
				<label for="username">Username</label>
			</div><!-- No carriage return here is deliberate. --><div class="cellinput">
				<input type="text" autocomplete="on" name="username" id="username" maxlength="16" value="<?=isset($_GET['username']) ? htmlentities($_GET['username']) : "";?>" />
			</div>
			<div style="clear: both;"></div>
		</div>
		<div class="loginrow withborder">
			<div class="celllabel">
				<label for="password">Password</label>
			</div><!-- No carriage return here is deliberate. --><div class="cellinput">
				<input type="password" autocomplete="off" name="password" id="password" maxlength="128" />
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
			<input type="submit" name="Submit" value="Login" class="button" style="float: right;" />
			<div style="clear: both;"></div>
		</div>
		<div class="loginrow">
			<div class="loginhelp">
				<a href="/signup">Sign Up</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="/help">Forgot Password?</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="/help">Need Help?</a>
			</div>
		</div>
	</form>
	
</div>


<?php

include_once '../app/views/static/footer.php';

?>
