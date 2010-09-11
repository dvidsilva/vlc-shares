<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/Plx.php';
require_once 'X/Plx/Item.php';
require_once 'Zend/Config.php';

class X_VlcShares_Plugins_AndroidProfile extends X_VlcShares_Plugins_Abstract {

	/**
	 * 
	 * @var Zend_Config
	 */
	private $options;
	
	private $_registeredEvents = array(
		X_VlcShares::TRG_PROFILES_TRAVERSAL 	=>	'checkProfile',
		X_VlcShares::TRG_PROFILES_ADDITIONALS	=>	'getProfiles',
		X_VlcShares::TRG_VLC_ARGS_SUBTITUTE		=>	'getArgs',
		X_VlcShares::TRG_CONTROLS_MENU_PRE		=>	'getForwardLink'		
	);
	
	public function __construct(Zend_Config $options) {
		$this->options = $options;
		$this->id = $this->options->id;
		$this->registerEvents($this->_registeredEvents);
	}	
	
	public function checkProfile($args) {
		$profileKey = $args[3];
		X_Env::debug(__METHOD__.': controllo '.$profileKey);
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'WiiMC') === false ) {
			if ( substr($profileKey, 0, strlen('plg:')) != 'plg:') {
				X_Env::debug(__METHOD__.': scartato');
				return false;
			}
		}
		return true;
	}
	
	public function getProfiles($argv = array()) {
		$profiles = array();
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'WiiMC') === false ) {
			$_profiles = $this->options->get('profiles', new Zend_Config(array()))->toArray();
			foreach ($_profiles as $pKey => $pValue) {
				$profiles['plg:'.$this->getId().':'.$pKey] = $pValue;
			}
		}
		return $profiles;
	}
	
	public function getArgs($argv = array()) {
		$request = $argv[0];
		
		$qId = $request->getParam('qId');
		
		if ( substr($qId, 0, strlen('plg:'.$this->getId())) == 'plg:'.$this->getId()) {
			$_profiles = $this->options->get('profiles', new Zend_Config(array()))->toArray();
			foreach ($_profiles as $pKey => $pValue) {
				$profiles['plg:'.$this->getId().':'.$pKey] = $pValue;
			}
			return array('profile' => $profiles[$qId]['args']);
		} else {
			return array();
		}
	}
	
	public function getForwardLink() {
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'WiiMC') === false ) {
			return new X_Plx_Item(X_Env::_('resume'), 'javascript:history.forward();');
		}
	}
}
