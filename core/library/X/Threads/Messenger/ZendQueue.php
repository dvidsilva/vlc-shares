<?php 

class X_Threads_Messenger_ZendQueue extends X_Threads_Messenger {
	
	protected $dbAdapter = null;
	
	public function __construct($dbAdapter) {
		$this->dbAdapter = $dbAdapter;
	}
	
	/* (non-PHPdoc)
	 * @see X_Threads_Messenger::enQueue()
	 */
	public function enQueue(X_Threads_Thread_Info $thread, X_Threads_Message $message) {
		$queue = $this->getZendQueue($thread->getId());
		$queue->send(serialize($message));
	}

	/* (non-PHPdoc)
	 * @see X_Threads_Messenger::deQueue()
	 */
	public function deQueue(X_Threads_Thread $thread) {
		$queue = $this->getZendQueue($thread->getId());
		$message = $queue->receive(1, 0);
		if ( count($message) > 0 ) {
			$message = $message->current();
			$queue->deleteMessage($message);
			return @unserialize($message->body);
		} else {
			// invoke queue removal
			return new X_Threads_Message_Wait();
		}
	}

	/* (non-PHPdoc)
	 * @see X_Threads_Messenger::hasMessage()
	 */
	public function hasMessage(X_Threads_Thread_Info $thread) {
		$queue = $this->getZendQueue($thread->getId());
		return (count($queue) > 0);
		
	}

	/* (non-PHPdoc)
	 * @see X_Threads_Messenger::clearQueue()
	 */
	public function clearQueue(X_Threads_Thread_Info $thread) {
		$queue = $this->getZendQueue($thread->getId());
		$queue->deleteQueue();
	}

	/* (non-PHPdoc)
	 * @see X_Threads_Messenger::showQueue()
	 */
	public function showQueue(X_Threads_Thread_Info $thread) {
		$queue = $this->getZendQueue($thread->getId());
		$received = $queue->receive(25, 0);
		$messages = array();
		foreach ( $received as $message ) {
			/* @var $message Zend_Queue_Message */
			$messages[] = @unserialize($message->body);
		}
		return $messages;
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $name
	 * @return Zend_Queue
	 */
	private function getZendQueue($name) {
		$queue = new Zend_Queue('Db', array(
			'name' => $name,
			'dbAdapter' => $this->dbAdapter,
			Zend_Queue::TIMEOUT => 0 // make queue available for everyone soon 
		));
		return $queue;
	}
	
	
}

