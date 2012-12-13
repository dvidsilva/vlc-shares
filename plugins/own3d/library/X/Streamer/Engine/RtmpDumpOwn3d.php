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

class X_Streamer_Engine_RtmpDumpOwn3d extends X_Streamer_Engine implements X_Streamer_StopperEngine {

	const ID = 'rtmpdump-own3d';
	
	const SCORE_PATTERN = '%^rtmpdump-own3d://%i';

	
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
		$thread->log("Spawning RTMPDump (rtmpgw-own3d)...");
		$source = $this->getParam('source');
		
		// set the path
		if ( X_Env::isWindows() ) {
			X_RtmpDumpOwn3d::getInstance()->setPath(APPLICATION_PATH.'/../bin/rtmpdump-own3d-win/rtmpgw-own3d.exe');
		} else {
			X_RtmpDumpOwn3d::getInstance()->setPath(APPLICATION_PATH.'/../bin/rtmpdump-own3d-linux/rtmpgw-own3d');
		}
		
		$own3dPlugin = X_VlcShares_Plugins::broker()->getPlugins('own3d');
		if( $own3dPlugin instanceof X_VlcShares_Plugins_Own3d /*&& X_VlcShares_Plugins::helpers()->streamer()->isRegistered('vlc')*/ ) {
		
			// try to get reference to vlc-streamer
			/* @var $vlcStreamer X_Streamer_Engine_Vlc */
			//$vlcStreamer = X_VlcShares_Plugins::helpers()->streamer()->get('vlc');
			
			// get the channel id
			//$source = substr($source, strlen('rtmpdump-own3d://'));
			// make the plugin to parse params from server and build a rtmpdump-weebtv uri
			//$source = $weebPlugin->getLinkParams($source);
			
			// always live
			//$command = (string) X_RtmpDumpOwn3d::getInstance()/*->setLive(true)*/->parseUri($source)/*->setStreamPort($weebPlugin->getStreamingPort())*/;
			
			/*
			$vlcStreamer->getVlcWrapper()->setPipe($command);
			$vlcStreamer->setSource('-');
			$vlcStreamer->setParam('profile', "#std{access=http{mime=video/x-flv},mux=ffmpeg{mux=flv},dst=0.0.0.0:{$own3dPlugin->getStreamingPort()}/stream}");
			
			// redirect std error to null
			// and force quite
			//X_Env::execute("$command -q 2> /dev/null", X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
			//X_Env::execute($command, X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
			$vlcStreamer->getVlcWrapper()->spawn();
			*/
			
			$command = (string) X_RtmpDumpOwn3d::getInstance()->parseUri($source)->setStreamPort($own3dPlugin->getStreamingPort());
			X_Env::execute($command, X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);			
			
			$thread->log("RTMPDump execution finished");
			
		} else {
			$thread->log("RTMPDump-own3d cannot be started without own3d plugin and vlc streamer");
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
		X_RtmpDumpOwn3d::getInstance()->forceKill();
		// wait few seconds
		sleep(2); //TODO tweak value better
	}
	
}
