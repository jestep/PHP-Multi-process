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
* Add 5 seconds total so that the children always timeout first
* Worst case should only take a few milliseconds once the children are finished
*/
ini_set("max_execution_time",DEFAULT_TIMELIMIT+1);

$mp->checkStatus();

/**
* Here you can get the output from the children
* $output = $mp->returnOutput();
*
* @return array((int),(string)); --array('child_id','output');
*/

$mp->cleanup();