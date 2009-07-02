<?php 

require_once('mp_config.php');

class childProcess
{
	public $child = array();
	
	/**
	* Construct our child
	* The child will pass the header arguments to this class
	* We then get the timelimit, envelope, and stored variables
	*/
	private function __construct($args)
	{
		
		for($i=1;$i<count($args);$i++):
		
			$request = split('=',$args[$i]);
			$this->request[$request[0]] = $request[1];
		
		endfor;
		
		$id = (int)$this->request['id'];
		
		$child_query = "
		SELECT `envelope`, `variables`, `time_limit` 
		FROM `".DB_NAME."`
		WHERE `id` = ".$id." LIMIT 1";
		
		switch(CACHE_METHOD):
			case 'sqlite':
			
				$this->db = new SQLiteDatabase(SQLITE_DIRECTORY.'/'.DB_NAME.'.db');
				
				$result = $this->db->query($child_query);
				
				$this->child = $result->fetch(SQLITE_ASSOC);
				
			break;
			
			case 'mysql':
				
				$this->db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
				
				$result = $this->db->query($child_query);
				
				$this->child = $result->fetch_assoc();
			
			break;
		endswitch;
		
	}
	
	/**
	* Returns our variables that were stored in the cache database
	* @return unknown
	*/
	public function getVariables()
	{
		return unserialize(base64_decode($this->child['variables']));
	}
	
	/**
	* When we're done, set the process complete add any output we want to send to the parent
	* We also set the actual PID so that the parent can verify it is destroyed
	* Then we kill the process using exit()
	*/
	public function setProcessComplete($output = NULL)
	{
		$query = "
		UPDATE `" . DB_NAME . "`
		SET `pid` = " . getmypid() . " ,`status` = 1, `output` = '" . base64_encode($output) . "'
		WHERE `envelope` = '" . $this->child['envelope'] . "' AND `id` = " . $this->child['id'];
		
		$this->db->query($query);
		
		$this->cleanup();
	}
	
	/**
	* Kill this process
	*/
	public function cleanup()
	{
		exit();
	}
}