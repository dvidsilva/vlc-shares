<?php

require_once 'X/Vlc/Commander.php';
require_once 'Zend/Config.php';
require_once 'X/Env.php';

class X_Vlc_Commander_Rc extends X_Vlc_Commander {
	
	// ho bisogno di nc
	private $nc_command = '';
	
	public function __construct($options = array()) {
		parent::__construct($options);
		
		if ( X_Env::isWindows() ) {
			$this->nc_command = $this->options->get('commander', new Zend_Config(array()))->get('nc_command', 'echo {%command%} | "'.dirname(__FILE__).'/nc.exe" {%host%} {%port%} -w 1');
		} else {
			$this->nc_command = $this->options->get('commander', new Zend_Config(array()))->get('nc_command', 'echo {%command%} | nc {%host%} {%port%}');
		}
		
		$host = $this->options->get('commander', new Zend_Config(array()))->get('nc_host', '127.0.0.1');
		$port = $this->options->get('commander', new Zend_Config(array()))->get('nc_port', '4212');
		$this->nc_command = str_replace(array('{%host%}', '{%port%}'), array($host, $port), $this->nc_command);
		
	}
	
	/**
	 * @param unknown_type $resource
	 */
	public function play($resource = null) {
		// !!!!attenzione
		// ricorda che ogni comando = 1 secondo su windows
		if ( !is_null($resource) /*&& file_exists($resource)*/ ) {
			// non posso controllare l'esistenza per i flussi
			// remoti
			//$this->_send('clear');
			$this->_send('add '.$resource);
		} else {
			$this->_send('play');
		}
	}

	/**
	 * 
	 */
	public function stop() {
		$this->_send('stop');
	}

	/**
	 * 
	 */
	public function pause() {
		$this->_send('pause');
	}

	/**
	 * @param int $time
	 * @param bool $relative
	 */
	public function seek($time, $relative = false) {
		if ( $relative ) {
			$array = $this->_send('get_time', X_Env::EXECUTE_OUT_ARRAY);
			// se prendo solo l'ultima riga non ho garanzie che mi venga indicato
			// solo il tempo nell'ultima riga
			$cTime = $array[count($array)-1];
			$time = $cTime + $time;
			if ( $time < 0 ) $time = 0;
		}
		$this->_send('seek '.$time);
	}

	/**
	 * 
	 */
	public function next() {
		$this->_send('next');
	}

	/**
	 * 
	 */
	public function previous() {
		$this->_send('prev');
	}

	/**
	 * 
	 */
	public function getInfo() {
		$infos = $this->_send('info', X_Env::EXECUTE_OUT_ARRAY);
		/**
		status change: ( new input: /home/ximarx/Musica/QOD/QOD/musica.mp3 )
		status change: ( audio volume: 256 )
		status change: ( play state: 3 )
		+----[ Diffusione 0 ]
		| 
		| Tipo: Video
		| Codifica: XVID
		| Risoluzione: 720x576
		| Risoluzione video: 720x576
		| Immagini al secondo: 25
		| 
		+----[ Diffusione 1 ]
		| 
		| Tipo: Audio
		| Codifica: mpga
		| Canali: Stereo
		| Campionamento: 44100 Hz
		| Bitrate: 192 kb/s
		| 
		+----[ end of stream info ]
		*/
		$rInfos = array();
		$current = array();
		$status = 0;
		foreach( $infos as $line ) {
			if ( substr($line, 0, strlen('+----[ ')) == '+----[ ' ) {
				if ( $status ) {
					$rInfos['streams'][] = $current;
					$current = array();
				}
				$status++;
			} elseif ( substr($line, 0, strlen('status change: ( new input: ')) == 'status change: ( new input: ') {
				$rInfos['name'] = trim(substr($line, strlen('status change: ( new input: '), -2));
			} elseif ( substr($line, 0, 1) == '|' && trim($line) != '|' ) {
				$chunk = explode(':', trim(substr($line, 1)));
				$current[$this->_reverseTranslate($chunk[0])] = $chunk[1];
			}
		}
		return $rInfos;
	}

	/**
	 * 
	 */
	public function getTotalTime() {
		$output = $this->_send('get_length', X_Env::EXECUTE_OUT_ARRAY);
		return @$output[count($output)-1];
	}

	/**
	 * 
	 */
	public function getCurrentTime() {
		$output = $this->_send('get_time', X_Env::EXECUTE_OUT_ARRAY);
		return @$output[count($output)-1];
	}

	/**
	 * 
	 */
	public function getCurrentName() {
		$output = $this->_send('get_title', X_Env::EXECUTE_OUT_ARRAY);
		return @$output[count($output)-1];
	}

	/**
	 * @param unknown_type $signal
	 * @param unknown_type $values
	 */
	public function sendCustomSignal($signal, $values = null) {
		return $this->_send($signal . (!is_null($values) ? ' ' . $values : ''), X_Env::EXECUTE_OUT_ARRAY);
	}


	public function getDefaultVlcArg() {
		$host = $this->options->get('commander', new Zend_Config(array()))->get('nc_host', '127.0.0.1');
		$port = $this->options->get('commander', new Zend_Config(array()))->get('nc_port', '4212');
		
		if ( X_Env::isWindows() ) {
			return '-I oldrc --rc-host="'.$host.':'.$port.'"';
		} else {
			return '-I rc --rc-host="'.$host.':'.$port.'" --rc-fake-tty';
		}
	}
	
	private function _send($command, $outtype = X_Env::EXECUTE_OUT_NONE) {
		X_Env::debug(__METHOD__.": sending message $command");
		$command = str_replace('{%command%}', $command, $this->nc_command);
		$return = X_Env::execute($command, $outtype, X_Env::EXECUTE_PS_WAIT);
		if ( $outtype != X_Env::EXECUTE_OUT_NONE ) {
			X_Env::debug("Reply:");
			X_Env::debug(print_r($return, true));
		}
		return $return;
	}
	
	private $_translate = array(
		'tipo' => 'type',
		'codifica' => 'codec',
		'risoluzione' => 'resolution',
		'risoluzione video' => 'v_resolution',
		'immagini al secondo' => 'fps',
		'canali' => 'channel',
		'campionamento' => 'freq',
		'bitrate' => 'bitrate'
	);
	
	private function _reverseTranslate($name) {
		$name = strtolower($name);
		if ( array_key_exists($name, $this->_translate) ) {
			return $this->_translate[$name];
		} else {
			return lcfirst(str_replace(' ', '', ucwords($name))); // camelcase
		}
	}
}

if ( !function_exists('lcfirst') ) {
	function lcfirst($str) {
		return strtolower(substr($str, 0, 1)).substr($str, 1);
	}
}
