<?php

require_once('lib/mp_config.php');
require_once('lib/mp_parent.class.php');

/**
* Enter the file names of every child process you want to execute here
* For some servers, you will need to enter the full path for each child
* Ex: /home/myuser/public_html/processes/process_1.php
*/
$processes = array(
	'process_1.php',					   
	'process_2.php',
	'process_3.php'
);

$mp = new multi_process();
$mp->createChildren($processes);

/**
* Reset the time limit after we spawn our background processes
* Add 1 second so that the children always timeout first
*/
ini_set("max_execution_time",DEFAULT_TIMELIMIT+1);

$mp->checkStatus();

$mp->cleanup();