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
* Site main index.php. 
* This file receives all user input via URL rewrites in the .htaccess files.
* The core application and MVC framework is initialised here via init.php and user input is passed to the framework.
* @author Ashley Flynn - AIE & CIT - 2019 - https://ajflynn.io/
*/

require_once '../app/init.php';

$app = new App;

?>