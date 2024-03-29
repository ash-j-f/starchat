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
* Help view.
* Displays site help information to the user.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
*/

//View title.
$pg_title = "Help";

include_once '../app/views/static/header.php';

/**
* Generate markdown-parsed versions of formatting example text.
* @param $s The string to parse.
* @returns The markdown-parsed version of the text as HTML.
*/
function p($s)
{
	require_once '../app/core/Parsedown.php';
	$parsedown = new Parsedown();
	return $parsedown->setUrlsLinked(true)->setSafeMode(true)->setBreaksEnabled(true)->line($s);
}

?>


<div id="loginbox">

	<h1>
		Help
	</h1>

	<div class="loginrow withborder plaintext">
		
			To see a list of chat commands, type /? into the message box in any channel.
			<br />
			<br />
			Make sure you have Javascript and Cookies enabled to view this site.
			<br />
			<br />
			For assistance, including password resets, please contact us at <a href="mailto:info@ajflynn.io">info@ajflynn.io</a>.
		
	</div>
	
	<div class="loginrow withborder plaintext">
		
			<Strong>To add style to your message text:</strong>
			<br />
			<br />
			<table class="helpTable">
				<tr>
					<td>*emphasis*</td> <td><?=p('*emphasis*');?></td>
				</tr>
				<tr>
					<td>**bold**</td> <td><?=p('**bold**');?></td>
				</tr>
				<tr>
					<td>~~strikethrough~~</td> <td><?=p('~~strikethrough~~');?></td>
				</tr>
				<tr>
					<td>`code($foo);`</td> <td><?=p('`code($foo);`');?></td>
				</tr>
				<tr>
					<td>http://autolink.etc/</td> <td><?=p('http://autolink.etc/');?></td>
				</tr>
				<tr>
					<td>[Named Link](https://google.com)</td> <td><?=p('[Named Link](https://google.com)');?></td>
				</tr>
			</table>
		
	</div>
	
	<div class="loginrow">
		<div class="loginhelp">
			<? if (isset($_SESSION) && isset($_SESSION['username'])) { ?>
			<a href="/channel/viewall">Channels</a>
			<? } else {?>
			<a href="/login">Login</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <a href="/signup">Sign Up</a>
			<? } ?>
		</div>
	</div>
	
</div>


<?php

include_once '../app/views/static/footer.php';

?>
