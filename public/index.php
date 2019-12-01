<?php

/**
* Site main index.php. 
* This file receives all user input via URL rewrites in the .htaccess files.
* The core application and MVC framework is initialised here via init.php and user input is passed to the framework.
* @author Ashley Flynn - CIT214642 - AIE & CIT - 2019 - https://ajflynn.io/
*/

require_once '../app/init.php';

$app = new App;

?>