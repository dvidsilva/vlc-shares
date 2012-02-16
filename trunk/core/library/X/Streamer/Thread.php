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

class X_Streamer_Thread implements X_Threads_Runnable {
	
	/* (non-PHPdoc)
	 * @see X_Threads_Runnable::run()
	 */
	public function run($params = array(), X_Threads_Thread $thread) {
		
		$this->thread = $thread;
		
		$streamerId = X_Env::isset_or($params['streamerId'], false);
		$streamerClass = X_Env::isset_or($params['streamerClass'], false);
		$params = X_Env::isset_or($params['params'], array());
		
		if ( !$streamerId ) {
			$thread->log("Sorry dude, no streamerId submitted. Can't do anything");
			return X_Threads_Thread::RETURN_INVALID_PARAMS;
		}
			
		
		/* @var $streamer X_Streamer_Engine_Vlc */
		$streamer = X_VlcShares_Plugins::helpers()->streamer()->get($streamerId);
		$streamer->setParams($params);
		$streamer->startEngine($thread);
				
		return self::RETURN_NORMAL;
		
	}

}
