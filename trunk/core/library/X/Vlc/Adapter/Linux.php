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

require_once 'X/Vlc/Adapter.php';
require_once 'X/Vlc/Commander/Http.php';

class X_Vlc_Adapter_Linux extends X_Vlc_Adapter {

	private $pidFile = '';
	private $vlcLog = false;
	
	public function __construct($options = array()) {
		parent::__construct($options);
		$this->pidFile = $this->_options->get('adapter', new Zend_Config(array()))->get('pidFile', sys_get_temp_dir().'/vlcLock.pid');
		$this->vlcLog = $this->_options->get('adapter', new Zend_Config(array()))->get('log', false);
	}
	
	/**
	 * @param string $args stringa di argomenti
	 */
	public function spawn($vlcPath, $args = '') {
		$args = $this->interfaceCheck($args);
		if ( !$this->isRunning() ) {
			// log store checkc
			if ( $this->vlcLog ) {
				$logFile = sys_get_temp_dir().'/vlcShares.vlc-log.txt';
				//$args .= " --verbose=\"2\"";
				$args .= " --file-logging --logfile=\"$logFile\"";
			} else {
				$args .= " --quiet";
			}
			
			$args .= " | pidof $vlcPath > \"{$this->pidFile}\" && rm \"{$this->pidFile}\"";
			// trying with normal background mode
			X_Env::execute("$vlcPath $args", X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
			
		}
	}

	/**
	 * 
	 */
	public function isRunning() {
		if (file_exists($this->pidFile)) {
			// controlliamo meglio
			if ( $this->getCurrentTime() != '' ) {
				return true;
			} else {
				unlink($this->pidFile);
				return false;
			}
		} else {
			return false;
		}
	}

	/**
	 * @param unknown_type $pid
	 */
	public function forceKill($pid = null) {
		if ( is_null($pid) ) {
			$pid = file_get_contents($this->pidFile);
			if ( $this->isRunning() && $pid ) {
				X_Env::execute("kill `cat {$this->pidFile}`", X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
			} else {
				// uccido anche le sessioni nn registrate
				$this->forceKillAll();
			}
		} else {
			X_Env::execute("kill $pid", X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
		}
	}

	/**
	 * 
	 */
	public function forceKillAll() {
		$path = '"'.trim($this->_options->get('path', 'vlc'),'"').'"';
		X_Env::execute("killall $path", X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
	}

	/**
	 * 
	 */
	public function getPid() {
		if ( $this->isRunning() ) {
			return trim(file_get_contents($this->pidFile));
		} else throw new Zend_Exception(__METHOD__.' not allowed if vlc is not running');
	}

	/**
	 * 
	 */
	protected function _initDefaultCommander() {
		$this->setCommander(new X_Vlc_Commander_Http($this->_options));
	}
}
