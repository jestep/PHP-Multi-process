<?php

require_once('mp_config.php');

class multi_process
{
	
	private static $db;
	private static $envelope;
	
	/**
	* Construct our multi-process class
	* This will create our envelope
	*/
	public function __construct()
	{
		
		switch(CACHE_METHOD):
			
			case 'sqlite':
			
				$this->db = new SQLiteDatabase(SQLITE_DIRECTORY.'/'.DB_NAME.'.db');
				
			break;
			
			case 'mysql':
				
				$this->db = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
			
			break;
			
		endswitch;
		
		if($this->db):
		
			if(!$this->db->query('SELECT COUNT(*) FROM '.DB_NAME.'')):
	
				$this->db->query("
					CREATE TABLE ".DB_NAME." (
						id 			INTEGER default '1',
						envelope 	CHAR (10),
						pid 		INTEGER,
						status 		INTEGER default '0',
						variables 	TEXT,
						time_limit 	INT,
						output		TEXT,
						PRIMARY KEY (id)
					);
				");
				
			endif;
		
		endif;
		
		$this->envelope = $this->createEnvelopeName();
		
	}
	
	/**
	* Add a process to the database and start it
	*
	* @param array $processes
	*/
	public function createChildren($processes = array()){
		
		foreach($processes as $process):
			
			//Only run the process if the file exists
			if (file_exists($process['path'])):
			
				$query = "
				INSERT INTO " . DB_NAME . "
				(
					envelope, 
					variables,
					time_limit
				)
				VALUES 
				(
					'" . $this->envelope . "', 
					'" . base64_encode(serialize($process['variables'])) . "',
					" . DEFAULT_TIMELIMIT . "
				)";
				
				$this->db->query($query);
				
				switch(CACHE_METHOD):
					case 'sqlite':
					
						$id = $this->db->lastInsertRowid(); 
					
					break;
					
					case 'mysql':
					
						$id = $this->db->insert_id;
					
					break;
				endswitch;
	
				exec("nohup /usr/local/bin/php -f " . $process['path'] . " id=" . $id . " > /dev/null &");
				
				/**
				* The following is for debugging only
				* 
				exec("nohup /usr/local/bin/php -f " . $process['path'] . " id=" . $id . "  &", $output);
				
				echo "<pre>";
				print_r($output);
				echo "</pre>";
				
				*/
				
			endif;
			
		endforeach;

	}
	
	/**
	* Check the status of our multi-process
	*
	* @param int $sleep
	* @return boolian
	*/
	public function checkStatus($sleep = 100000)
	{
		$cur = 0;
		
		while( $cur < DEFAULT_TIMELIMIT ) {
			
			// wait for 100000 microseconds, or .1 seconds by default
			usleep($sleep);
			
			//usleep is microseconds, and we need to convert it to seconds
			$cur += ($sleep/1000000);
			
			if($this->returnStatus($this->envelope)){
				
				return true;
			}
		}
		/**
		* If process is still running after timeout return false
		*/
		return false;
	}
	
	/**
	* Check to see if there are any uncompleted processes
	* Return true if we're done, false if we're still processing
	*
	* @return boolian
	*/
	public function returnStatus()
	{
		$query = "
		SELECT id 
		FROM " . DB_NAME . " 
		WHERE envelope = '" . $this->envelope . "' AND status = 0";
		
		$result = $this->db->query($query);
		
		switch(CACHE_METHOD):
			case 'sqlite':
			
				$rows = $result->numRows(); 
			
			break;
			
			case 'mysql':
			
				$rows = $result->num_rows;
			
			break;
		endswitch;
		
		if($rows > 0)
		{
			return false;
		}
		
		return true;
	}
	
	/**
	* Returns output from the children
	*
	* @return array
	*/
	public function returnOutput()
	{
		$output = array();
		
		$query = "
		SELECT id, output 
		FROM " . DB_NAME . " 
		WHERE envelope = '" . $this->envelope . "'";
		
		$result = $this->db->query($query);
		
		switch(CACHE_METHOD):
			case 'sqlite':
			
				while($row = $result->fetch(SQLITE_ASSOC)):
					
					$output[$row['id']] = base64_decode($row['output']);
				
				endwhile;
				
			break;
			
			case 'mysql':
				
				
				while($row = $result->fetch_assoc()):
					
					$output[$row['id']] = base64_decode($row['output']);
				
				endwhile;
			
			break;
		endswitch;
		
		return $output;
		
	}
	
	/**
	* Generate a random string name for our envelope
	*
	* @param int $length
	* @return string
	*/
	public function createEnvelopeName($length=10)
	{
		$envelope = '';
		$possible = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		
		for($i=1;$i<$length;$i++):
			$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
			$envelope .= $char;
		endfor;
		
		/**
		* Check to make sure this envelope doesn't already exist
		* The probability of this happening is essentially zero
		*/
		$query = "
		SELECT id 
		FROM " . DB_NAME . " 
		WHERE envelope = '" . $envelope . "'";
		
		$result = $this->db->query($query);
		
		switch(CACHE_METHOD):
			case 'sqlite':
			
				$rows = $result->numRows(); 
			
			break;
			
			case 'mysql':
			
				$rows = $result->num_rows;
			
			break;
		endswitch;
		
		if($rows > 0):
		
			$this->createEnvelopeName($length);
		
		endif;
		
		return $envelope;
	}
	
	/**
	* Manually destroy everything
	*/
	function cleanup()
	{
		
		$query = "
		SELECT id 
		FROM " . DB_NAME . " 
		WHERE envelope = '" . $this->envelope . "'";
		
		$result = $this->db->query($query);
		
		switch(CACHE_METHOD):
			case 'sqlite':
			
				while($child = $result->fetch(SQLITE_ASSOC)):
					
					$this->kill($child['pid']);
				
				endwhile;
				
			break;
			
			case 'mysql':
				
				while($child = $result->fetch_assoc()):
					
					$this->kill($child['pid']);
				
				endwhile;
			
			break;
		endswitch;
		
		$this->db->query("DELETE FROM " . DB_NAME . " WHERE envelope = '" . $this->envelope . "'");
	}
	
	/**
	* Check if the process is still running !
	*
	* @param int $pid
	* @return boolen
	*/
	public function isRunning($pid)
	{
	   exec("ps $pid", $ProcessState);
	   
	   return(count($ProcessState) >= 2);
	}
	
	/**
	* Kill Application pid
	*
	* @param int $pid
	* @return boolen
	*/
	public function kill($pid)
	{
		if($this->isRunning($pid)):
			exec("kill -KILL $pid");
			return true;
		else:
			return false;
		endif;
	}
}