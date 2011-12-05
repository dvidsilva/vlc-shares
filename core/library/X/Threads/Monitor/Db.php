<?php 


class X_Threads_Monitor_Db extends X_Threads_Monitor {
	
	/**
	 * @var X_Threads_Monitor_Db_Mapper
	 */
	protected $mapper = null;
	
	public function __construct(X_Threads_Monitor_Db_Mapper $mapper) {
		$this->mapper = $mapper;
	}
	
	
	/* (non-PHPdoc)
	 * @see X_Threads_Monitor::storeStatus()
	 */
	protected function storeStatus($threadId, $state, $info) {
		
		$this->mapper->update($threadId, $state, serialize($info));
		
	}

	/* (non-PHPdoc)
	 * @see X_Threads_Monitor::retrieveStatus()
	 */
	protected function retrieveStatus($threadId) {
		
		$thread = $this->mapper->find($threadId);
		
		if ( !$thread ) {
			throw new Exception("Thread id '{$threadId}' not found");
		}
		
		return array(
			$thread['state'],
			$thread['info'] != '' ? @unserialize($thread['info']) : array()
		);
	}

	/* (non-PHPdoc)
	 * @see X_Threads_Monitor::retrieveAllStatus()
	 */
	protected function retrieveAllStatus() {
		
		$threads = $this->mapper->fetchAll();
		$status = array();
		foreach ($threads as $thread) {
			$status[] = array(
				$thread['id'],
				$thread['state'],
				$thread['info'] != '' ? @unserialize($thread['info']) : array()
			);
		}
		return $status;
	}
	
	/* (non-PHPdoc)
	 * @see X_Threads_Monitor::removeStatus()
	 */
	protected function removeStatus($threadId) {
		
		$this->mapper->delete($threadId);
		
	}
}
