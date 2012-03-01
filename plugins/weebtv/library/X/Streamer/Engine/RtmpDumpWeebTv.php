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

class X_Streamer_Engine_RtmpDumpWeebTv extends X_Streamer_Engine implements X_Streamer_StopperEngine {

	const ID = 'rtmpdump-weebtv';
	
	const SCORE_PATTERN = '%^rtmpdump-weebtv://%i';

	
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
		$thread->log("Spawning RTMPDump (rtmpdump-weebtv | vlc)...");
		$source = $this->getParam('source');
		
		// set the path
		if ( X_Env::isWindows() ) {
			X_RtmpDumpWeebTv::getInstance()->setPath(APPLICATION_PATH.'/../bin/rtmpdump-weebtv-win/rtmpdump-weebtv.exe');
		} else {
			X_RtmpDumpWeebTv::getInstance()->setPath(APPLICATION_PATH.'/../bin/rtmpdump-weebtv-linux/rtmpdump-weebtv');
		}
		
		$weebPlugin = X_VlcShares_Plugins::broker()->getPlugins('weebtv');
		if( $weebPlugin instanceof X_VlcShares_Plugins_WeebTv && X_VlcShares_Plugins::helpers()->streamer()->isRegistered('vlc') ) {
		
			// try to get reference to vlc-streamer
			/* @var $vlcStreamer X_Streamer_Engine_Vlc */
			$vlcStreamer = X_VlcShares_Plugins::helpers()->streamer()->get('vlc');
			
			// get the channel id
			$source = substr($source, strlen('rtmpdump-weebtv://'));
			// make the plugin to parse params from server and build a rtmpdump-weebtv uri
			$source = $weebPlugin->getLinkParams($source);
			
			// always live
			$command = (string) X_RtmpDumpWeebTv::getInstance()/*->setLive(true)*/->parseUri($source)/*->setStreamPort($weebPlugin->getStreamingPort())*/;
			
			$vlcStreamer->getVlcWrapper()->setPipe($command);
			$vlcStreamer->setSource('-');
			$vlcStreamer->setParam('profile', "#std{access=http{mime=video/x-flv},mux=ffmpeg{mux=flv},dst=0.0.0.0:{$weebPlugin->getStreamingPort()}/stream}");
			
			// redirect std error to null
			// and force quite
			//X_Env::execute("$command -q 2> /dev/null", X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
			//X_Env::execute($command, X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
			$vlcStreamer->getVlcWrapper()->spawn();
			$thread->log("RTMPDump execution finished");
			
		} else {
			$thread->log("RTMPDump-weebtv cannot be started without weebtv plugin and vlc streamer");
		}
		
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
		X_RtmpDumpWeebTv::getInstance()->forceKill();
		// wait few seconds
		sleep(2); //TODO tweak value better
	}
	
}
