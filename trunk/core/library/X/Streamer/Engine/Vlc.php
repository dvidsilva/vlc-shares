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

class X_Streamer_Engine_Vlc extends X_Streamer_Engine implements X_Streamer_StopperEngine {

	const ID = 'vlc';
	
	const SCORE_PATTERN = '%^(https?|upd|mms|rtsp|ftp)://%i';

	protected $vlc;
	
	public function __construct(X_Vlc $vlcInstance = null) {
		if ( is_null($vlcInstance) ) {
			$vlcInstance = X_Vlc::getLastInstance(); // OMG OMG no 
		}
		if ( is_null($vlcInstance) ) { // check again
			X_Debug::e("Streamer engine VLC without a vlc instance available");
			throw new Exception("No X_Vlc instance available");
		}
		$this->vlc = $vlcInstance;
	}
	

	/* (non-PHPdoc)
	 * @see X_Streamer_Engine::getId()
	*/
	public function getId() {
		return self::ID;
	}
	
	/* (non-PHPdoc)
	 * @see X_Streamer_Engine::getRunnableClass()
	 */
	public function getRunnableClass() {
		return parent::RUNNABLE;
	}

	/* (non-PHPdoc)
	 * @see X_Streamer_Engine::getRunnableParams()
	 */
	public function getRunnableParams() {
		return array(
			'streamerId' => $this->getId(),
			'params' => $this->getParams(),
			'streamerClass' => get_class($this),				
		);
	}


	/* (non-PHPdoc)
	 * @see X_Streamer_Engine::checkScore()
	 */
	public function checkScore($url) {
		if ( preg_match(self::SCORE_PATTERN, $url) ) {
			return X_VlcShares_Plugins_Helper_Streamer::SCORE_HIGH;
		} else {
			return X_VlcShares_Plugins_Helper_Streamer::SCORE_LOW;
		}
	}

	/* (non-PHPdoc)
	 * @see X_Streamer_Engine::startEngine()
	*/
	public function startEngine(X_Threads_Thread $thread) {
		// assume all parameters are ready
		$thread->log("Spawning VLC...");
		$this->vlc->spawn();
		$thread->log("VLC execution finished");
		
	}
	
	
	/* (non-PHPdoc)
	 * @see X_Streamer_Engine::__toString()
	 */
	public function __toString() {
		return sprintf("\nclass %s\nparams: %s", get_class($this), print_r($this->getParams(), true));
	}

	/* (non-PHPdoc)
	 * @see X_Streamer_StopperEngine::doStop()
	 */
	public function doStop($threadInfo) {
		// this should be "->shutdown()"
		X_Vlc::getLastInstance()->forceKill();
		// wait few seconds
		sleep(2); //TODO tweak value better
	}
	
	/* (non-PHPdoc)
	 * @see X_Streamer_Engine::getParam()
	 */
	public function getParam($key) {
		return X_Vlc::getLastInstance()->getArg($key);
	}

	/* (non-PHPdoc)
	 * @see X_Streamer_Engine::getParams()
	 */
	public function getParams() {
		return X_Vlc::getLastInstance()->getArgs();
	}

	/* (non-PHPdoc)
	 * @see X_Streamer_Engine::setParam()
	 */
	public function setParam($key, $value) {
		X_Vlc::getLastInstance()->registerArg($key, $value);
		return $this;
	}

	/* (non-PHPdoc)
	 * @see X_Streamer_Engine::setParams()
	 */
	public function setParams($params) {
		if ( is_array($params) ) {
			foreach ($params as $key => $value) {
				$this->setParam($key, $value);
			}
		}
		return $this;
	}
	
}
