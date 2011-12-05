<?php 

/**
 * Manage message queue for threads
 * 
 * @author ximarx
 *
 */
abstract class X_Threads_Messenger {

	/**
	 * Append a new message in the queue for the thread
	 * 
	 * @param X_Threads_Thread_Info $thread
	 * @param X_Threads_Message $message
	 */
	abstract public function enQueue(X_Threads_Thread_Info $thread, X_Threads_Message $message);
	
	/**
	 * Get the first message in the queue for the thread
	 * 
	 * @param X_Threads_Thread $thread
	 * @return X_Threads_Message
	 */
	abstract public function deQueue(X_Threads_Thread $thread);
	
	/**
	 * 
	 * Check if thread still has message left
	 * 
	 * @param X_Threads_Thread_Info $thread
	 * @return boolean
	 */
	abstract public function hasMessage(X_Threads_Thread_Info $thread);
	
	/**
	 * 
	 * Reset the queue
	 * 
	 * @param X_Threads_Thread_Info $thread
	 */
	abstract public function clearQueue(X_Threads_Thread_Info $thread);
	
	/**
	 * Get all messages in queue for the thread
	 * without dequeuing
	 *  
	 * @param X_Threads_Thread_Info $thread
	 * return array[X_Threads_Message]
	 */
	abstract public function showQueue(X_Threads_Thread_Info $thread);
	
}
