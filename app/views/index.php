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
* Home view.
* Displays site home page (front page) to the user.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
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
