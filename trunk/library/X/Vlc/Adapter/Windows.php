<?php

require_once 'X/Vlc/Adapter.php';
require_once 'X/Vlc/Commander/Http.php';

class X_Vlc_Adapter_Windows extends X_Vlc_Adapter {

	private $pidFile = '';
	private $cached_isRunning = null;
	
	public function __construct($options = array()) {
		parent::__construct($options);
		$this->pidFile = $this->_options->get('adapter', new Zend_Config(array()))->get('pidFile', sys_get_temp_dir().'/vlcLock.pid');
	}
	
	/**
	 * @param string $args stringa di argomenti
	 */
	public function spawn($vlcPath, $args = '') {
		X_Env::debug(__METHOD__);
		$args = $this->interfaceCheck($args);
		if ( !$this->isRunning() ) {
			// moved in wrapper
			//$vlcPath = '"'.trim($vlcPath,'"').'"';
			// qui devo semplicemente aggiungere la roba passata da configurazione
			//$args .= " --daemon --pidFile=\"$this->pidFile\"";
			X_Env::execute("$vlcPath $args", X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_BACKGROUND);
         // wait 2 seconds before check for process (WORKAROUND for slow PCs: they take a while to spawn it)
			sleep(2);
			$result = X_Env::execute("tasklist /FO CSV /FI \"imagename eq vlc.exe\"", X_Env::EXECUTE_OUT_ARRAY, X_Env::EXECUTE_PS_WAIT);
			$pid = 0;
			X_Debug::i(print_r($result, true));
			// suppongo che l'ultimo creato sia la riga del pid valido
			$line = $result[count($result)-1];
			$array = str_getcsv($line);
			X_Debug::i(print_r($array, true));
			if ( $array[2] == 'Console' || $array[0] == 'vlc.exe' ) {
				$pid = $array[1];
			}
			if ( $pid != 0 ) {
				X_Debug::i("PID: $pid");
				file_put_contents($this->pidFile, $pid);
			} else {
				X_Debug::e("No pid found. VLC is not working for me");
			}
		}
	}

	/**
	 * 
	 */
	public function isRunning() {
		if ( file_exists($this->pidFile) ) {
			// su windows l'esistenza del file non e' garanzia di esistenza del processo
			// perche al termine di una sessione (normale o crash, su linux solo crash)
			// il pid rimane cmq attivo. L'unico modo in cui nn ci sia e' che
			// venga chiamato il forceKill
			
			// controllo che vlc sia attivo tramite il commander
			// oppure verificando che il pid sia proprio quello del vlc
			/*$output = $this->getCommander()->getCurrentTime();
			if ( trim($output) !== '' ) {
				return true;
			} else {
				return false;
			}*/
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
				X_Env::execute("taskkill /PID {$this->getPid()} /F", X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
				// al termine del kill devo eliminare il file di pid
				@unlink($this->pidFile);
			} else {
				$this->forceKillAll();
			}
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
