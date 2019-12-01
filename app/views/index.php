<?php

/**
* Home view.
* Displays site home page (front page) to the user.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

//View title.
$pg_title = "Home";

include_once '../app/views/static/header.php';

?>


<div id="loginbox">

	<h1>
		Welcome to StarChat
	</h1>

	<div class="loginrow withborder plaintext">
		
		StarChat is a simple secure chat service designed <strong>specially for gamers</strong>. Login or sign up with a new account using the links below.
		
	</div>
	
	<div class="loginrow">
		<div class="loginhelp">
			<? if (isset($_SESSION) && isset($_SESSION['username'])) { ?>
			<a href="/channels">Channels</a>
			<? } else {?>
			<a href="/login">Login</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="/signup">Sign Up</a> 
			<? } ?>
			
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="/help">Need Help?</a>
		</div>
	</div>
	
</div>


<?php

include_once '../app/views/static/footer.php';

?>
