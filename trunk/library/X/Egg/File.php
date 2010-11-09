<?php 

class X_Egg_File {

	private $srcBasePath;
	private $path;
	private $destBasePath;
	
	function __construct($path, $srcBasePath, $destBasePath) {
		
		$this->path = $path;
		$this->srcBasePath = rtrim($srcBasePath, '\\/').'/';
		$this->destBasePath = rtrim($destBasePath, '\\/').'/'; 
		
	}
	
	public function getSource() {
		return $this->srcBasePath.$this->path;
	}
	
	public function getDestination() {
		return $this->destBasePath.$this->path;
	}
	
	
}
