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
	
	public function start(X_Streamer_Engine $engine ) {
		// check if streamer is alive (if it is, shutdown it)
		if ( $this->isStreaming() ) {
			$this->stop();
		}
		
		// then wake up the thread
		X_Threads_Manager::instance()->appendJob($engine->getRunnableClass(), $engine->getRunnableParams(), self::THREAD_ID);
		//die("DEV");
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
