<?php 

interface X_Threads_Runnable {
	
	const RUNNING = "running";
	const WAITING = "waiting";
	const STOPPED = "stopped";
	
	// job completed correctly
	const RETURN_NORMAL = "completed";
	// job can't be completed for an invalid precondition
	const RETURN_ABORT = "aborted";
	// job can't be completed cause of an error
	const RETURN_ERROR = "error";
	// job has been rescheduled next for an invalid precondition
	const RETURN_DELAYED = "delayed";
	
	/**
	 * Start the job
	 * 
	 * @param array $params
	 * @param X_Threads_Thread $thread
	 * @return mixed
	 */
	function run($params = array(), X_Threads_Thread $thread);
	
}