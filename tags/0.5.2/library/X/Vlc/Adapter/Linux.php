<?php

require_once 'X/Vlc/Adapter.php';
require_once 'X/Vlc/Commander/Http.php';

class X_Vlc_Adapter_Linux extends X_Vlc_Adapter {

	private $pidFile = '';
	
	public function __construct($options = array()) {
		parent::__construct($options);
		$this->pidFile = $this->_options->get('adapter', new Zend_Config(array()))->get('pidFile', sys_get_temp_dir().'/vlcLock.pid');
	}
	
	/**
	 * @param string $args stringa di argomenti
	 */
	public function spawn($vlcPath, $args = '') {
		$args = $this->interfaceCheck($args);
		if ( !$this->isRunning() ) {
			// qui devo semplicemente aggiungere la roba passata da configurazione
			$args .= " --daemon --pidfile=\"$this->pidFile\"";
			X_Env::execute("$vlcPath $args", X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_BACKGROUND);
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
			if ( $this->isRunning() ) {
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
		X_Env::execute("killall vlc", X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
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
