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
require_once 'X/Vlc/Adapter/Linux.php';
require_once 'X/Vlc/Adapter/Windows.php';
require_once 'X/Vlc/Commander.php';

/**
 * Proxy per l'utilizzo di vlc in base alla piattaforma
 * @author Francesco Capozzo
 *
 */
class X_Vlc {
	
	/**
	 * 
	 * @var X_Vlc_Adapter
	 */
	private $adapter = null;
	
	private $_conf_vlcArgs = '';
	
	private $_registredArgs = array();
	
	private static $instance = null;
	
	private $_initialized = false;
	
	private $_pipe = false;
	
	/**
	 * 
	 * @var Zend_Config
	 */
	private $options = null;
	
	public function __construct($options = array()) {
		if (!($options instanceof Zend_Config)) {
			if ( !is_array($options) ) {
				$options = array();
			}
			$options = new Zend_Config($options);
		}
		// store the options for lazyinit
		$this->options = $options;
		// store the instance for lastInstance
		self::$instance = $this;
		
	}
	
	/**
	 * Execute vlcwrapper initialization on the first request
	 */
	private function lazyInit() {

		if ( $this->_initialized === false ) {
			
			$options = $this->options;
			
			$adapterConf = $options->get('adapter', new Zend_Config(array()));
			$adapter = $adapterConf->get('name', "X_Vlc_Adapter_".(X_Env::isWindows() ? 'Windows' : 'Linux'));
			X_Debug::i("Adapter: $adapter");
			$this->adapter = new $adapter($options);
			
			$commanderConf = $options->get('commander', new Zend_Config(array()));
			$commander = $commanderConf->get('name', '');
			$commanderPath = $commanderConf->get('path', '');
			if ( $commanderPath != '' && file_exists($commanderPath)) {
				X_Debug::i("Including commanderPath: $commanderPath");
				include_once $commanderPath;
			}
			if ( class_exists($commander, true ) && array_key_exists('X_Vlc_Commander', class_parents($commander))) {
				X_Debug::i("Commander: $commander");
				$commander = new $commander($options);
				$this->adapter->setCommander($commander);
			} else {
				X_Debug::w("Commander: no selection");
			}
			
			$this->_conf_vlcArgs = $options->get('args', "{%source%} --play-and-exit --sout=\"#{%profile%}:{%output%}\" --sout-keep {%subtitles%} {%audio%} {%filters%}");
			$this->_conf_vlcPath = $options->get('path', "vlc");
		
			$this->_initialized = true;
		}
	}
	
	/**
	 * 
	 * @param string $placeholder
	 * @param string $substitution
	 * @return X_Vlc
	 */
	public function registerArg($placeholder, $substitution) {
		
		$this->lazyInit();
		
		X_Debug::i("Setting {{$placeholder}} => {{$substitution}}");
		$this->_registredArgs[$placeholder] = $substitution;
		
		return $this;
	}
	
	public function spawn($args = array()) {
		
		$this->lazyInit();

		// vlcpath quotes moved here from adapters
		
		try {
			$pathString = ((string) $this->getPipe()) . ' | ' . '"'.trim($this->_conf_vlcPath,'"').'"';
		} catch (Exception $e) {
			// ignore pipe is not setted
			// just use the normal path
			$pathString = '"'.trim($this->_conf_vlcPath,'"').'"';
		}
		
		
		if ( is_array($args) ) {
			// aggiungo agli argomenti registrati
			foreach ($args as $arg_k => $arg_v ) {
				$this->registerArg($arg_k, $arg_v);
			}
			$vlcArgs = $this->_conf_vlcArgs;
			// sostituisco alla lista
			foreach ( $this->_registredArgs as $a_k => $a_v ) {
				$vlcArgs = str_replace("{%$a_k%}", $a_v, $vlcArgs);
			}
			// cancello gli argomenti
			$this->_registredArgs = array();
			// elimino i restanti placeholder
			$vlcArgs = preg_replace('/{%\w+%}/', '', $vlcArgs);
			
			// invio all'adapter che provvedera' per il resto
			return $this->adapter->spawn($pathString, $vlcArgs);
		} elseif (is_string($args)) {
			// se e' una stringa, la sostituisco a quella delle opzioni
			return $this->adapter->spawn($pathString, $args);
		}
	}

	public function __call($name, $argv) {
		
		$this->lazyInit();
		
		return call_user_func_array(array($this->adapter, $name), $argv);
	}
	
	/**
	 * @deprecated
	 * @param unknown_type $pipe
	 */
	public function setPipe($pipe) {
		$this->_pipe = $pipe;
		return $this;
	}
	
	/**
	 * @deprecated
	 * @throws Exception
	 */
	public function getPipe() {
		if ($this->_pipe !== false && ((string) $this->_pipe) !== '' ) {
			return $this->_pipe;
		} else {
			throw new Exception("No pipe defined");
		}
	}
	
	/**
	 * @return X_Vlc
	 */
	public static function getLastInstance() {
		return self::$instance;
	}
	
	public function getArg($argName) {
		$this->lazyInit();
		return @$this->_registredArgs[$argName];
	}

	public function getArgs() {
		$this->lazyInit();
		return $this->_registredArgs;
	}
	
}
