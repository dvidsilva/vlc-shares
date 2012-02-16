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
	
	/**
	 * @var array
	 */
	protected $status = array(self::STOPPED, array());
	
	public function __construct($id) {
		$this->id = $id;
	}
	
	/**
	 * Set a new status (state + info)
	 * @param string $state thread state
	 * @param array $infos last infos about the current state
	 */
	public function setStatus($state, $infos = array()) {
		$this->status = array($state, $infos);
		return $this;
	}
	
	/**
	 * Return an array of state (index 0) + info (index 1)
	 * 
	 * @return array
	 */
	public function getStatus() {
		return $this->status;
	}
	
	/**
	 * Return the currest state of the thread
	 * 
	 * @return string
	 */
	public function getState() {
		return $this->status[0];
	}
	
	/**
	 * Return the info about the current thread
	 * @return array
	 */
	public function getInfo() {
		return $this->status[1];
	}
	
	/**
	 * Get the thread id
	 * 
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * Fake function
	 */
	public function loop() {
		
	}
}
