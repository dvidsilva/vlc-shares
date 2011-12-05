<?php 

/**
 * 
 * Collect info about the current state of a thread
 * @author ximarx
 *
 */
class X_Threads_Thread_Info {
	
	const RUNNING = 'running';
	const STOPPED = 'stopped';
	const WAITING = 'waiting';
	
	
	protected $id = '';
	protected $status = array(self::STOPPED, array());
	
	public function __construct($id) {
		$this->id = $id;
	}
	
	public function setStatus($status, $infos = array()) {
		$this->status = array($status, $infos);
		return $this;
	}
	
	public function getStatus() {
		return $this->status;
	}
	
	public function getState() {
		return $this->status[0];
	}
	
	public function getInfo() {
		return $this->status[1];
	}
	
	public function getId() {
		return $this->id;
	}
	
	/**
	 * Fake function
	 */
	public function loop() {
		
	}
}
