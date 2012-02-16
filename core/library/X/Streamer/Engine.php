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

abstract class X_Streamer_Engine {
	
	const RUNNABLE = 'X_Streamer_Thread';
	
	protected $params = array();
	
	/**
	 * Set a streamer param
	 * @param string $key
	 * @param mixed $value
	 * @return X_Streamer_Engine
	 */
	public function setParam($key, $value) {
		$this->params[$key] = $value;
		return $this;
	}
	
	/**
	 * Get a streamer param
	 * @param string $key
	 * @return mixed
	 */
	public function getParam($key) {
		if ( isset($this->params[$key])) {
			return $this->params[$key];
		} else {
			return null;
		}
	}
	
	/**
	 * Get all streamer params
	 * @return array
	 */
	public function getParams() {
		return $this->params;
	}
	
	/**
	 * Set a list of streamer params
	 * @param array $params
	 * @return X_Streamer_Engine
	 */
	public function setParams($params) {
		if ( is_array($params) ) {
			$this->params = array_merge($this->params, $params);
		}
		return $this;
	}
	
	public function setSource($uri) {
		$this->setParam('source', $uri);
	}
	
	/**
	 * Get the runnable class that will execute the streamer impl
	 * @return string
	 */
	abstract public function getRunnableClass();
	
	/**
	 * Get the params that will be used by the runnable
	 * @return array of params
	 */
	abstract public function getRunnableParams();
	
	/**
	 * Get a unique id for the stream engine
	 * @return string
	 */
	abstract public function getId();
	
	/**
	 * Check the engine score for the $url
	 * @param string $url a resource uri
	 * @return int a score
	 */
	abstract public function checkScore($url);
	
	/**
	 * Return debug string serialization of the corrent state of the engine
	 * @return string
	 */
	abstract public function __toString();
	
	/**
	 * Start the engine execution
	 * @param X_Threads_Thread $thread
	 */
	abstract public function startEngine(X_Threads_Thread $thread);
	
}
