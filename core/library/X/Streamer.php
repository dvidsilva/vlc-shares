<?php

/**
 * This file is part of the vlc-shares project by Francesco Capozzo (ximarx) <ximarx@gmail.com>
 *
 * @author: Francesco Capozzo (ximarx) <ximarx@gmail.com>
 *
 * vlc-shares is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * vlc-shares is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with vlc-shares.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class X_Streamer {
	
	const THREAD_ID = 'streamer';
	
	public function isStreaming() {
		
		$thread = X_Threads_Manager::instance()->getThreadInfo(self::THREAD_ID);
		return ($thread->getState() == X_Threads_Thread_Info::RUNNING);
		
	}
	
	public function getStreamingEngineId() {
		
		// i don't use self::isStreaming because
		// i don't want double fetch of thread status
		$thread = X_Threads_Manager::instance()->getThreadInfo(self::THREAD_ID);
		if ( $thread->getState() != X_Threads_Thread_Info::RUNNING ) {
			throw new Exception("No engine is streaming right now");
		}
		
		$infos = $thread->getInfo();
		if ( !isset($infos['message_params']) || !isset($infos['message_params']['streamerId']) ) {
			X_Debug::e("Streamer thread is working, but without a real streamer engine");
			throw new Exception("No engine information available");
		}
		
		return $infos['message_params']['streamerId'];
		
	}
	
	/**
	 * Invoke remote execution of the streamer thread with the streamer engine job
	 * @param X_Streamer_Engine $engine
	 * @return boolean true if thread move to running state, false otherwise
	 * 		This could happen if streamer job failed really fast (wrong params?)
	 * 		or if the job is appended between the last waiting tick and the shutdown
	 * 		of the thread 
	 */
	public function start(X_Streamer_Engine $engine ) {
		// check if streamer is alive (if it is, shutdown it)
		if ( $this->isStreaming() ) {
			$this->stop();
		}
		
		// get standard params overloaded by engine's ones, if any
		$params = array_merge(array(
				'streamerId' => $engine->getId(),
				'streamerClass' => get_class($engine),
			), $engine->getRunnableParams());
		
		// then wake up the thread
		X_Threads_Manager::instance()->appendJob($engine->getRunnableClass(), $params, self::THREAD_ID);
		
		// sleep 1 second, then check for 10 times and watch if something is working
		sleep(1);
		
		$i = 10;
		$threadInfo = X_Threads_Manager::instance()->getThreadInfo(self::THREAD_ID);
		while ( $threadInfo->getState() != X_Threads_Thread_Info::RUNNING && $i > 0 ) { 
			// this is a very special case: i appended the job just between
			// the last waiting tick and the stop of the thread,
			// so the thread manager tought he can simple append the message,
			// but for real the thread go in stop state (so it haven't read the
			// read the message)
			// to avoid this, i just append a renew message, so the thread
			// move from stop -> run stream job -> stop (if any)
			sleep(1);
			$threadInfo = X_Threads_Manager::instance()->getThreadInfo(self::THREAD_ID);
			$i--;
		}
		
		// check 1 more time
		if ( $i <= 0 || $threadInfo->getState() != X_Threads_Thread_Info::RUNNING ) {
			X_Debug::e("Streamer thread doesn't want to wake up or it finished really fast");
			return false;
		}
		return true;
	}
	
	public function stop() {
		
		if ( !$this->isStreaming() ) {
			return;
		}
		
		$thread = X_Threads_Manager::instance()->getThreadInfo(self::THREAD_ID);
		
		$infos = $thread->getInfo();
		
		if ( isset($infos['message_params']) && isset($infos['message_params']['streamerId']) ) {
			$engineId = $infos['message_params']['streamerId'];
			
			$engine = X_VlcShares_Plugins::helpers()->streamer()->get($engineId);
			
			//$engine = new $engineClass();
			if ( $engine instanceof X_Streamer_StopperEngine ) {
				$engine->doStop($infos);
			}
			
		}
		
		X_Threads_Manager::instance()->halt($thread);
		
	}

	
	// internal stuff
	
	/**
	 * ref to self
	 * @var X_Streamer
	 */
	static private $i = null;
	/**
	 * singleton
	 * @param X_Streamer $newInst
	 * @return X_Streamer
	 */
	public static function i(X_Streamer $newInst = null) {
		if ( $newInst ) {
			self::$i = $newInst;
		}
		if ( is_null(self::$i) ) {
			self::$i = new X_Streamer();
		}
		return self::$i;
	}
	
}
