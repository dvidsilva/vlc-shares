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


/**
 * Manager of all streamer engines availables
 * 
 * @author ximarx
 */
class X_VlcShares_Plugins_Helper_Streamer extends X_VlcShares_Plugins_Helper_Abstract {
	
	const SCORE_BEST = 100;
	const SCORE_HIGH = 75;
	const SCORE_AVERAGE = 50;
	const SCORE_LOW = 25;
	const SCORE_IGNORE = 0;
	
	private $streamers = array();
	
	/**
	 * @var Zend_Config
	 */
	private $options;
	
	public function __construct($options = array()) {

		if ( is_array($options) ) {
			$options = new Zend_Config($options);
		}
		if ( !($options instanceof Zend_Config)) {
			$options = new Zend_Config(array(
				// default options
			));
		}
		$this->options = $options;
		
		// vlc streamer is not special
		// i had it here only for dev purpose
		// TODO move vlc streamer init away from here
		
		//$this->register(new X_Streamer_Engine_Vlc());
		
	}
	
	public function isEnabled() {
		return (bool) $this->options->get('enabled', true);
	}
	
	/**
	 * add a new Streamer_Engine inside the supported streamers
	 * @param X_Streamer_Engine $streamer concrete implementation of X_Streamer_Engine
	 * @param string $id an overloaded streamer id
	 * @param string $pattern an overloaded location pattern
	 * @return void
	 */
	public function register(X_Streamer_Engine $streamer, $id = null) {
		
		if ( $id === null ) {
			$id = $streamer->getId();
		}
		X_Debug::i("Registering streamer {{$id}}");
		$this->streamers[$id] = $streamer;
		
		return $this;
	} 

	/**
	 * remove a register streamer from the lit
	 * @param string $id host id
	 */
	public function unregister($id) {
		if ( $this->isRegistered($id) ) {
			unset($this->streamers[$id]);
		}
	}
	
	/**
	 * check if a streamer for the $id is registered
	 * @param string $id
	 * @return boolean
	 */
	public function isRegistered($id) {
		return array_key_exists($id, $this->streamers);
	}
	
	/**
	 * get a streamer engine handler for the $url type
	 * @param $url
	 * @return X_Streamer_Engine
	 */
	public function find($url) {
		/* @var $streamerConcrete X_Streamer_Engine */
		$streamerConcrete = null;
		$maxScore = self::SCORE_IGNORE;
		//X_Debug::i("Available streamers: ".print_r($this->streamers, true));
		foreach ($this->streamers as $streamer) {
			/* @var $streamer X_Streamer_Engine */
			$score = $streamer->checkScore($url);
			X_Debug::i("Checking score for ".get_class($streamer).": $score");
			if ( $score == self::SCORE_BEST ) {
				$streamerConcrete = $streamer;
				break;
			} elseif ( $score > $maxScore ) {
				$maxScore = $score;
				$streamerConcrete = $streamer;
			}
		}
		if ( $streamerConcrete === null ) {
			throw new Exception("There is no Streamer who can handle the request for {$url}");
		}
		
		X_Debug::i("Streamer found for url {{$url}} with score {{$maxScore}}: {$streamer->getId()}");
		
		return $streamerConcrete;
	}
	
	/**
	 * Get a streamer by id
	 * @param string $id
	 * @return X_Streamer_Engine
	 */
	public function get($id) {
		if ( $this->isRegistered($id) ) {
			return $this->streamers[$id];
		}
		throw new Exception("No streamer found with id {{$id}}");
	}
	
	/**
	 * Get all registered streamers
	 * @return array[id => X_Streamer_Engine]
	 */
	public function getAll() {
		return $this->streamers;
	}
	
}
