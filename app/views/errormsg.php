<?php

/**
* Error message view.
* Displays error messages to the user.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

//View title.
$pg_title = "Error";

include_once '../app/views/static/header.php';

?>

<div id="loginbox">

	<h1>
		Error
	</h1>

	<div class="loginrow withborder plaintext">
			<?=htmlentities($_GET['msg'])?>
	</div>
	
	<div class="loginrow">
		<div class="loginhelp">
			<? if (isset($_SESSION) && isset($_SESSION['username'])) { ?>
			<a href="/channel/viewall">Channels</a>
			<? } else {?>
			<a href="/login">Login</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="/signup">Sign Up</a>
			<? } ?>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="/help">Help</a>
		</div>
	</div>
	
</div>


<?php

include_once '../app/views/static/footer.php';

?>
