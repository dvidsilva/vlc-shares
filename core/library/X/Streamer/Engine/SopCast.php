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

class X_Streamer_Engine_SopCast extends X_Streamer_Engine implements X_Streamer_StopperEngine {

	const ID = 'sopcast';
	
	const SCORE_PATTERN = '%^sop://%i';

	
	public function __construct() {
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
			return X_VlcShares_Plugins_Helper_Streamer::SCORE_BEST;
		} else {
			return X_VlcShares_Plugins_Helper_Streamer::SCORE_IGNORE;
		}
	}

	/* (non-PHPdoc)
	 * @see X_Streamer_Engine::startEngine()
	*/
	public function startEngine(X_Threads_Thread $thread) {
		// assume all parameters are ready
		$thread->log("Spawning SopCast...");
		$source = $this->getParam('source');
		$command = (string) X_SopCast::getInstance()->setUri($source);
		X_Env::execute($command, X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
		//$this->vlc->spawn();
		$thread->log("SopCast execution finished");
		
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
		X_SopCast::getInstance()->forceKill();
		// wait few seconds
		sleep(2); //TODO tweak value better
	}
	
}
