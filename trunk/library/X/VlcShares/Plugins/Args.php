<?php

require_once 'X/Env.php';
require_once 'X/VlcShares.php';
require_once 'Zend/Controller/Request/Http.php';
require_once 'Zend/Controller/Front.php';
require_once 'Zend/Registry.php';
require_once 'Zend/Config/Ini.php';
require_once 'X/Vlc.php';
require_once 'X/Plx.php';

class X_VlcShares_Plugins_Args {
	
	private $vlc;
	private $options;
	private $request;
	private $plx;
	
	public function __construct(array $options = array()) {
		$needRequest = true;
		$needOptions = true;
		$needVlc = true;
		$needPlx = true;
		foreach( $options as $optKey => $optValue) {
			switch ($optKey) {
				case 'request': $needRequest = false; break;
				case 'options': $needOptions = false; break;
				case 'vlc': $needVlc = false; break;
				case 'plx': $needPlx = false; break;
			}
			$this->$optKey = $optValue;
		}
		
		if ($needRequest )
			$this->request = Zend_Controller_Front::getInstance()->getRequest();
		
		if ($needOptions )
			if ( Zend_Registry::isRegistered('vlcshares::options'))
				$this->options = Zend_Registry::get('vlcshares::options');
			else
				$this->options = new Zend_Config_Ini(X_VlcShares::config());
				
		if ($needVlc)
			if ( Zend_Registry::isRegistered('vlcshares::vlc'))
				$this->vlc = Zend_Registry::get('vlcshares::vlc');
			else
				$this->vlc = X_Vlc::getLastInstance();
		
		if ($needPlx)
			if ( Zend_Registry::isRegistered('vlcshares::plx'))
				$this->plx = Zend_Registry::get('vlcshares::plx');
			else
				$this->plx = new X_Plx();
				
	}
	
	public function getVlc() {
		return $this->vlc;
	}
	
	public function getPlx() {
		return $this->plx;
	}
	
	public function getRequest() {
		return $this->request;
	}
	
	public function getOptions() {
		return $this->options;
	}
	
}

