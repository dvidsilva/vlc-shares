<?php

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
			
			$this->_conf_vlcArgs = $options->get('args', "{%source%}");
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
		
		$this->_registredArgs[$placeholder] = $substitution;
		
		return $this;
	}
	
	public function spawn($args = array()) {
		
		$this->lazyInit();
		
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
			return $this->adapter->spawn($this->_conf_vlcPath, $vlcArgs);
		} elseif (is_string($args)) {
			// se e' una stringa, la sostituisco a quella delle opzioni
			return $this->adapter->spawn($this->_conf_vlcPath, $args);
		}
	}

	public function __call($name, $argv) {
		
		$this->lazyInit();
		
		return call_user_func_array(array($this->adapter, $name), $argv);
	}
	
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
