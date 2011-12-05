<?php 

class X_Threads_Logger_File extends X_Threads_Logger {

	private $resource = null; 
	
	function __construct($file, $directory = null) {
		
		if ( $directory === null ) $directory = sys_get_temp_dir();
		
		//$file = realpath("$directory/$file");
		$file = "$directory/$file";
		
		$this->resource = fopen($file, "a");
		
		register_shutdown_function(array($this, 'close'));
		
	}
	
	/* (non-PHPdoc)
	 * @see X_Threads_Logger::log()
	 */
	function log($message) {
		$time = date("[d/m/Y H:i:s]");
		fwrite($this->resource, "{$time} {$message}".PHP_EOL);
	}
	
	function close() {
		if ( $this->resource ) {
			$this->log("Shutting down log...");
			fclose($this->resource);
		}
	}
	
}
