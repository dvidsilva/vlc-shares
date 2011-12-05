<?php 

final class X_Threads_Thread extends X_Threads_Thread_Info {

	const PHASE_PRE = 'pre';
	const PHASE_POST = 'post';
	
	const EXIT_NORMAL = 0;
	
	const RETURN_INVALID_CLASS = 'Invalid command class';
	const RETURN_INVALID_PARAMS = 'Invalid params for command';

	//{{{ THREAD CONFIGS

	/**
	 * Time waiting after thread resume for new jobs
	 * is tickleft * tick (sec)
	 * After this time, is no job found
	 * thread go back in STOP state
	 */
	
	/**
	 * Time sleeping between message check
	 */
	protected $tick = 5;
	/**
	 * Number of message queue checking while waiting
	 * before shutdown
	 * @var unknown_type
	 */
	protected $tickleft = 12;
	//}}}
	
	protected $spawned = 0;
	
	/**
	 * @var X_Threads_Logger
	 */
	protected $logger = null;
	
	/**
	 * 
	 * Ref to Threads manager
	 * @var X_Threads_Manager
	 */
	protected $manager = null;
	
	
	public function __construct($threadId, X_Threads_Manager $manager) {
		$this->manager = $manager;
		parent::__construct($threadId);
	}
	
	
	public function loop() {
		set_time_limit(0);
		$this->spawned = time();
		$tickleft = $this->tickleft;
		$exitStatus = self::EXIT_NORMAL;
		
		$this->log(sprintf("Thread resumed"));
		
		$this->manager->getMonitor()->updateStatus($this, self::WAITING, array(
			"tickleft" => $tickleft,
			"max_tickleft" => $this->tickleft,
			"tick" => $this->tick,
			"spawned" => $this->spawned
		));
		
		try {
		
			while( --$tickleft >= 0) {

				$this->log("Thread is waiting. {$tickleft} ticks left");
				
				while ( $this->manager->getMessenger()->hasMessage($this) ) {
					
					$commandReturn = null;
					// reset max_wait counter
					$tickleft = $this->tickleft;
				
					$message = $this->manager->getMessenger()->deQueue($this);

					$this->log("Message found: $message");
					
					$this->manager->getMonitor()->updateStatus($this, self::RUNNING, array(
						"tickleft" => $tickleft,
						"max_tickleft" => $this->tickleft,
						"tick" => $this->tick,
						"spawned" => $this->spawned,
						"phase" => self::PHASE_PRE,
						"message_type" => $message->getType(),
						"message_sign" => (string) $message
					));
					
					switch ( $message->getType() ) {
						case X_Threads_Message::TYPE_STOP:
							// stop the thread
							throw new Exception("Stop command received", self::EXIT_NORMAL);
							break;
						
						case X_Threads_Message::TYPE_VOID:
							// dummy message, just reset the tick counter if thread was waiting
							break;
							
						case X_Threads_Message::TYPE_WAIT:
							// reset the message queue
							$this->manager->getMessenger()->clearQueue($this);
							break;
							
						case X_Threads_Message::TYPE_COMMAND:
							/* @var $message X_Threads_Message_Command */
							$commandReturn = $this->execute($message->getRunnableClass(), $message->getParams());
							// execut the command
							break;
					}
					
					$this->manager->getMonitor()->updateStatus($this, self::RUNNING, array(
						"tickleft" => $tickleft,
						"max_tickleft" => $this->tickleft,
						"tick" => $this->tick,
						"spawned" => $this->spawned,
						"phase" => self::PHASE_POST,
						"message_type" => $message->getType(),
						"message_sign" => (string) $message,
						"command_return" => $commandReturn
					));
					
				}
				
				$this->manager->getMonitor()->updateStatus($this, self::WAITING, array(
					"tickleft" => $tickleft,
					"max_tickleft" => $this->tickleft,
					"tick" => $this->tick,
					"spawned" => $this->spawned
				));
				
				sleep($this->tick);
			}
			
		} catch ( Exception $e ) {
			// log failure
			$this->log("Exception in runnable: {$e->getMessage()}");
		}
		
		$this->manager->getMonitor()->updateStatus($this, self::STOPPED, array(
			"tickleft" => $tickleft,
			"max_tickleft" => $this->tickleft,
			"tick" => $this->tick,
			"spawned" => $this->spawned,
			"exit_status" => $exitStatus
		));
		
		$this->log("Thread stopped");
		
	}
	
	/**
	 * Change the tick time (tick = time waiting between each check for messages
	 * while sleeping)
	 * 
	 * @param int $tick
	 */
	public function setTick($tick) {
		$tick = intval($tick);
		if ( $tick < 1 ) $tick = 1;
		$this->tick = $tick;
		$this->log("Tick sleep changed to $tick");
	}
	
	
	public function executeNext($class, $params = array()) {
		$this->manager->getMessenger()->enQueue($this, new X_Threads_Message_Command($class, $params) );
		$this->log("Appending execution of $class to the queue");
	}
	
	public function stopNext() {
		$this->manager->getMessenger()->enQueue($this, new X_Threads_Message_Stop() );
		$this->log("Appending stop to the queue");
	}
	
	public function waitNext() {
		$this->manager->getMessenger()->enQueue($this, new X_Threads_Message_Wait() );
		$this->log("Appending wait to the queue");
	}
	
	public function renewNext() {
		$this->manager->getMessenger()->enQueue($this, new X_Threads_Message_Renew() );
		$this->log("Appending renew to the queue");
	}
	
	protected function execute($class, $params = array()) {
		$reflection = new ReflectionClass($class);
		if ( class_exists($class, true) && $reflection->implementsInterface('X_Threads_Runnable') ) {
			/* @var $runnable X_Threads_Runnable */
			$runnable = new $class();
			$return = $runnable->run($params, $this);
		} else {
			$this->log("Invalid class $class");
			$return = self::RETURN_INVALID_CLASS;
		}
		$this->log("Job completed with status $return");
		return $return;
	}
	
	public function log($msg) {
		if ( $this->logger == null ) {
			// initialize default logger
			$this->setLogger(new X_Threads_Logger_File("vlcShares.thread-{$this->getId()}.log", sys_get_temp_dir()));
		}
		$this->logger->log($msg);
	}
	
	public function setLogger(X_Threads_Logger $logger) {
		$this->logger = $logger;
	}
	
}
