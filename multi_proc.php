<?php

require_once('lib/mp_config.php');
require_once('lib/mp_parent.class.php');

/**
* Add an array to the $processes array for each child
* The processes array is an associative array('path'=>'filename.php','variables'=>array());
* You can add variables to send to the process through the same array
* For some servers, you will need to enter the full path for each child
* Ex: /home/myuser/public_html/processes/process_1.php
*/

$processes[] = array('path'=>'process_1.php',
					 'variables'=>array('some_value'=>5,
										'another_value'=>10)
					 );

$processes[] = array('path'=>'process_2.php',
					 'variables'=>array('other_value'=>7,
										'get'=>$_GET)
					 );

$mp = new multi_process();
$mp->createChildren($processes);

/**
* Reset the time limit after we spawn our background processes
* Add 5 seconds total so that the children always timeout first
* Worst case should only take a few milliseconds once the children are finished
*/

ini_set("max_execution_time",DEFAULT_TIMELIMIT+5);

$mp->checkStatus();

/**
* Here you can get the output from the children
* $output = $mp->returnOutput();
*
* @return array((int),(string)); --array('child_id','output');
*/

$mp->cleanup();