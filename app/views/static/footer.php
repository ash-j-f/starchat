<?php

/**
* Site footer, displayed with all standard HTML pages.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

include_once '../app/core/Config.php';

?>
		<div id='copyright'>
			<?=Config::getConfigOption("CopyrightNotice");?> 
		</div>
	</body>
</html>