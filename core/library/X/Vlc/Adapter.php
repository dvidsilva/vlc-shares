<?php

require_once 'Zend/Config.php';

abstract class X_Vlc_Adapter {
	
	/**
	 * 
	 * @var X_Vlc_Commander_Adapter
	 */
	protected $commander = null;
	// funzioni che devono essere redirette al commander
	private $_redirected = array( // controllo
								'play','stop','seek','pause',
								'next','previous',
								// interrogazione
								'getInfo',
								'getTotalTime','getCurrentTime',
								'getCurrentName');
	
	/**
	 * @var $_options Zend_Config
	 */
	protected $_options = null;
	
	public function __construct($options = array()) {
		// carica le opzioni
		if (!($options instanceof Zend_Config)) {
			if ( !is_array($options) ) {
				$options = array();
			}
			$options = new Zend_Config($options);
		}
		$this->_options = $options;
	}
	
	//========================
	// funzioni di manutenzione
	//========================
	abstract public function spawn($vlcPath, $args = '');
	abstract public function isRunning();
	abstract public function forceKill($pid = null);
	abstract public function forceKillAll();
	abstract public function getPid();
	
	
	//========================
	// funzioni di interrogazione
	// &
	// funzioni di controllo
	//=========================
	// gestite tramite __call
	
	/**
	 * Permette l'utilizzo di tutti i comandi di tipo X_Vlc_Adapter::signalXXX(value)
	 * @param string $name
	 * @param mixed $args
	 */
	public function __call($name, $args) {
		if ( substr($name, 0, strlen('signal') ) == 'signal' ) {
			// funzioni valide sono signalNOME
			return $this->getCommander()->sendCustomSignal(substr($name, strlen('signal')), $args);
		} else if ( array_search($name, $this->_redirected, true)
			&& method_exists($this->getCommander(),$name)) {
			// solo se metodo consentito e 
			// esistente
			//return $this->getCommander()->{$name}($args);
			return call_user_func_array(array($this->getCommander(),$name), $args);
		}
	}

	protected function interfaceCheck($args = '') {
		if ( strpos($args, '-I ') === false && strpos($args, ' --extraintf=" ') === false ) {
			// aggiungo la -I del commander
			$args .= " ".$this->getCommander()->getDefaultVlcArg();
		}
		return $args;
	}
	
	public function setCommander(X_Vlc_Commander $commander) {
		$this->commander = $commander;
	}
	
	/**
	 * @return X_Vlc_Commander
	 */
	protected function getCommander() {
		if ( is_null($this->commander) ) {
			$this->_initDefaultCommander();
		}
		return $this->commander;
	}
	
	/**
	 * Ogni adattatore deve specificare il proprio commander di default
	 * (ad esempio, per windows nn penso sara rc, mentre per linux si
	 */
	abstract protected function _initDefaultCommander();
	
}