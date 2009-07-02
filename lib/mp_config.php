<?php 

/* Global settings 
THE FIRST 3 SETTINGS ARE NEEDED REGARDLESS OF THE DATABASE CACHING MECHANISM BEING USED
*/

define('DEFAULT_TIMELIMIT',30);				// Sets the default timeout for the parent and all children
define('CACHE_METHOD','sqlite');			// Accepts either mysql or sqlite
define('DB_NAME', 'cache');					// The name of the database for either sqlite or MySQL

/* Sqlite settings 
ONLY NEEDED IF CACHE_METHOD IS sqlite
DEFAULT DIRECTORY IS sqlite
*/
define('SQLITE_DIRECTORY','sqlite');

/* MySQL settings 
ONLY NEEDED IF CACHE_METHOD IS mysql
*/
define('DB_USER', 'username');				// The name of the database user
define('DB_PASSWORD', 'password'); 			// The database user's password
define('DB_HOST', 'localhost');				// The database host. Anything but localhost would be a bad idea for PHP-multi process