<?php

require_once 'Zend/Config.php';

abstract class X_Vlc_Commander {
	
	/**
	 * @var $options Zend_Config
	 */
	protected $options = null;
	
	public function __construct($options = array()) {
		if (!($options instanceof Zend_Config)) {
			if ( !is_array($options) ) {
				$options = array();
			}
			$options = new Zend_Config($options);
		}
		$this->options = $options;
	}

	abstract public function play($resource = null);
	abstract public function stop();
	abstract public function pause();
	abstract public function seek($time, $relative = false);
	abstract public function next();
	abstract public function previous();
	
	abstract public function getInfo($infos = null);
	abstract public function getTotalTime();
	abstract public function getCurrentTime();
	abstract public function getCurrentName();
	
	abstract public function sendCustomSignal($signal, $values = null );
	
	abstract public function getDefaultVlcArg();
	
}