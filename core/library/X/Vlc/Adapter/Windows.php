<?php

require_once 'X/Vlc/Adapter.php';
require_once 'X/Vlc/Commander/Http.php';

class X_Vlc_Adapter_Windows extends X_Vlc_Adapter {

	private $pidFile = '';
	private $cached_isRunning = null;
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
		//X_Env::debug(__METHOD__);
		$args = $this->interfaceCheck($args);
		if ( !$this->isRunning() ) {
			
			if ( $this->vlcLog ) {
				$logFile = sys_get_temp_dir().'/vlcShares.vlc-log.txt';
				//$args .= " --verbose=\"2\"";
				$args .= " --file-logging --logfile=\"$logFile\"";
			} else {
				$args .= " --quite";
			}
			
			// append pidfile (lock only)
			$args .= " > \"{$this->pidFile}\" && del \"{$this->pidFile}\"";
				
			X_Env::execute("$vlcPath $args", X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
		}
	}

	/**
	 * 
	 */
	public function isRunning() {
		try {
			if ( file_exists($this->pidFile) ) {
				if ( $this->getCommander()->getCurrentTime() ) {
					/*
					$pid = $this->getPid();
					$command = "tasklist /FI \"PID eq $pid\" /FO CSV";
					$lines = X_Env::execute($command, X_Env::EXECUTE_OUT_ARRAY, X_Env::EXECUTE_PS_WAIT);
					$line = $lines[count($lines)-1];
					$array = str_getcsv($line);
					X_Env::debug(print_r($array, true));
					if ( $array[2] == 'Console' || $array[0] == 'vlc.exe' ) {
						return true;
					} else {
						@unlink($this->pidFile);
						return false;
					}
					*/
					return true;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} catch ( Exception $e) {
			return false;
		}
	}

	/**
	 * @param unknown_type $pid
	 */
	public function forceKill($pid = null) {
		if ( is_null($pid) ) {
			/*
			if ( $this->isRunning() ) {
				X_Env::execute("taskkill /PID {$this->getPid()} /F", X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
				// al termine del kill devo eliminare il file di pid
				@unlink($this->pidFile);
			} else {
			*/
				$this->forceKillAll();
			//}
		} else {
			X_Env::execute("taskkill /PID $pid /F", X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
		}
	}

	/**
	 * 
	 */
	public function forceKillAll() {
		X_Env::execute("taskkill /IM vlc.exe /F", X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
		if ( file_exists($this->pidFile))
			@unlink($this->pidFile);
	}

	/**
	 * 
	 */
	public function getPid() {
		return trim(file_get_contents($this->pidFile));
	}

	/**
	 * 
	 */
	protected function _initDefaultCommander() {
		$this->setCommander(new X_Vlc_Commander_Http($this->_options));
		// su windows (almeno) il default sara' http, anche se cmq
		// l'efficienza di Rc e' migliorata
		//$this->setCommander(new X_Vlc_Commander_Http($this->_options));
	}
}
