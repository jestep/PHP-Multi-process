<?php 

require_once('lib/mp_config.php');
require_once('lib/mp_child.class.php');

$mp = new childProcess($argv);

/**
* Set the execution time limit for this script to process
*/

ini_set("max_execution_time",$mp->child['time_limit']);

/*-----------------BEGIN YOUR OWN CODE HERE------------------------*/

/*

This is a normal PHP script so you can do whatever you want.
But, don't output anything because this is running in the background.

Variables you passed are available through
$variables = $mp->getVariables();

*/

/*-----------------END YOUR OWN CODE HERE---------------------------*/

/**
* When we're done make sure to update the status for this process
* You can also send output to the parent through the same function
* $mp->setProcessComplete($output);
*/

$mp->setProcessComplete();