<?php 

abstract class X_Threads_Monitor {

	abstract protected function removeStatus($threadId);
	abstract protected function storeStatus($threadId, $state, $info);
	abstract protected function retrieveStatus($threadId);
	abstract protected function retrieveAllStatus();
	
	public function updateStatus(X_Threads_Thread_Info $thread, $state, $info) {
		// store info in shared memory
		$this->storeStatus($thread->getId(), $state, $info);
		// update the thread
		$thread->setStatus($state, $info);
	}
	
	public function getThread($threadId) {
		
		$thread = new X_Threads_Thread_Info($threadId);
		
		try {
			list($state, $info) = $this->retrieveStatus($threadId);
			$thread->setStatus($state, $info);
		} catch (Exception $e ) {
			// thread found
		}
		
		return $thread;
	}
	
	public function getThreads() {
		
		$allStatus = $this->retrieveAllStatus();
		$threads = array();
		
		foreach ($allStatus as $status) {
			list($id, $state, $info) = $status;
			$thread = new X_Threads_Thread_Info($id);
			$thread->setStatus($state, $info);
			$threads[] = $thread;
		}
		
		return $threads;
		
	}
	
	public function removeThread(X_Threads_Thread_Info $thread) {
		$thread = $this->getThread($thread->getId());
		if ( $thread->getState() === X_Threads_Thread_Info::STOPPED ) {
			$this->removeStatus($thread->getId());
		}
	}
	
}
