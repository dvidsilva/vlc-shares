<?php 

/**
 * 
 * Threads Manager: allow to manage threads and log run processes
 * @author ximarx
 *
 */
class X_Threads_Manager {
	
	const ERROR_FORBIDDEN = 403;
	const ERROR_INVALID_RUNNABLE = 501;
	const ERROR_INVALID_STATE = 500;
	
	static protected $instance = null;
	
	/**
	 * @var X_Threads_Monitor
	 */
	protected $monitor = null;
	/**
	 * @var X_Threads_Messenger
	 */
	protected $messenger = null;
	/**
	 * @var X_Threads_Starter
	 */
	protected $starter = null;
	
	protected $loggerEnabled = false;
	
	/**
	 * 
	 * Get Manager instance
	 * @return X_Threads_Manager
	 */
	public static function instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	protected function __construct() {}
	
	/**
	 * Spawn a new thread
	 * 
	 * @param X_Threads_Thread_Info $thread
	 */
	protected function resume(X_Threads_Thread_Info $thread) {
		$this->getStarter()->spawn($thread->getId());
	}
	
	/**
	 * Append a stop message as last message in the queue
	 * 
	 * @param X_Threads_Thread_Info $thread
	 */
	public function stop(X_Threads_Thread_Info $thread) {
		$this->getMessenger()->enQueue($thread, new X_Threads_Message_Stop());
	}
	
	/**
	 * Clear the queue and append a stop message
	 * The thread will stop after the end of the current running job, if any
	 * 
	 * @param X_Threads_Thread_Info $thread
	 */
	public function halt(X_Threads_Thread_Info $thread) {
		$this->getMessenger()->clearQueue($thread);
		$this->getMessenger()->enQueue($thread, new X_Threads_Message_Stop());
	}
	
	/**
	 * Renew a waiting thread
	 * 
	 * @param X_Threads_Thread_Info $thread
	 */
	public function renew(X_Threads_Thread_Info $thread) {
		$this->getMessenger()->enQueue($thread, new X_Threads_Message_Renew());
	}
	
	/**
	 * Append a Wait message to the queue of the thread.
	 * When the thread execute this message, it will clear 
	 * any thread appended after this one and move in 
	 * wait state
	 * 
	 * @param X_Threads_Thread_Info $thread
	 */
	public function wait(X_Threads_Thread_Info $thread) {
		$this->getMessenger()->enQueue($thread, new X_Threads_Message_Wait());
	}
	
	/**
	 * Create a new thread if $threadId is not already running
	 * 
	 * @param string $threadId
	 * @return X_Threads_Thread_Info|X_Threads_Thread
	 */
	public function newThread($threadId) {
		$thread = $this->getMonitor()->getThread($threadId);
		
		if ( $thread->getState() == X_Threads_Thread_Info::STOPPED ) {
			// create a new thread and return it
			$thread = new X_Threads_Thread($threadId, $this);
			if ( !$this->isLogger() ) {
				$thread->setLogger(new X_Threads_Logger_Null());
			} else {
				$thread->setLogger(new X_Threads_Logger_File("vlcShares.thread-{$threadId}.log", sys_get_temp_dir()));
				// redirect standard debug too if enabled
				if ( X_Debug::isEnabled() ) {
					X_Debug::i("Forking debug log to {".sys_get_temp_dir()."/vlcShares.thread-{$threadId}.log");
					X_Debug::init(sys_get_temp_dir()."/vlcShares.thread-{$threadId}.log", X_Debug::getLevel());
				}
			}
		}
		
		return $thread;
	}
	
	public function getThreadInfo($threadId) {
		return $this->getMonitor()->getThread($threadId);
	}
	
	public function appendJob($runnableClass, $params, $threadId ) {
		$reflection = new ReflectionClass($runnableClass);
		if ( !$reflection->implementsInterface('X_Threads_Runnable') ) {
			throw new Exception("Invalid runnable class", self::ERROR_INVALID_RUNNABLE);
		}
		
		$thread = $this->getMonitor()->getThread($threadId);
		
		switch ( $thread->getState() ) {

			// if stopped, first resume, then append job
			case X_Threads_Thread_Info::STOPPED:
				$this->resume($thread);
			case X_Threads_Thread_Info::WAITING:
			case X_Threads_Thread_Info::RUNNING:
				$this->getMessenger()->enQueue($thread, new X_Threads_Message_Command($runnableClass, $params));
				break;
			
		}
		
	}
	
	
	/**
	 * Set the threads monitor
	 * @param X_Threads_Monitor $monitor
	 * @return X_Threads_Manager
	 */
	public function setMonitor(X_Threads_Monitor $monitor ) {
		$this->monitor = $monitor;
		return $this;
	}

	/**
	 * @throws Exception if no monitor available
	 * @return X_Threads_Monitor
	 */
	public function getMonitor() {
		if ( $this->monitor == null ) {
			throw new Exception('No monitor available', self::ERROR_INVALID_STATE);
		}
		return $this->monitor;
	}
	
	/**
	 * Set the threads messenger
	 * @param X_Threads_Messenger $messenger
	 * @return X_Threads_Manager
	 */
	public function setMessenger(X_Threads_Messenger $messenger) {
		$this->messenger = $messenger;
		return $this;
	}
	
	/**
	 * @throws Exception if no monitor available
	 * @return X_Threads_Messenger
	 */
	public function getMessenger() {
		if ( $this->messenger == null ) {
			throw new Exception('No messenger available', self::ERROR_INVALID_STATE);
		}
		return $this->messenger;
	}
	
	/**
	 * Set the threads starter
	 * @param X_Threads_Starter $starter
	 * @return X_Threads_Manager
	 */
	public function setStarter(X_Threads_Starter $starter) {
		$this->starter = $starter;
		return $this;
	}
	
	/**
	 * @throws Exception if no starter available
	 * @return X_Threads_Starter
	 */
	public function getStarter() {
		if ( $this->starter == null ) {
			throw new Exception('No starter available', self::ERROR_INVALID_STATE);
		}
		return $this->starter;
	}
	
	/**
	 * 
	 * Setup Manager (all in one solution)
	 * @param X_Threads_Monitor $monitor
	 * @param X_Threads_Messenger $messenger
	 * @param X_Threads_Starter $starter
	 */
	public function setup(X_Threads_Monitor $monitor, X_Threads_Messenger $messenger, X_Threads_Starter $starter, $loggerEnabled = false) {
		$this->setMessenger($messenger)
			->setMonitor($monitor)
			->setStarter($starter);
		$this->loggerEnabled = $loggerEnabled;
		return $this;
	}
	
	/**
	 * 
	 * Set logger status
	 * @param boolean $loggerEnabled
	 */
	public function setLogger($loggerEnabled = false) {
		$this->loggerEnabled = $loggerEnabled;
	}
	
	public function isLogger() {
		return $this->loggerEnabled;
	}
	
}
