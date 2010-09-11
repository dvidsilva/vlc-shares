<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/Plx.php';
require_once 'X/Plx/Item.php';
require_once 'Zend/Config.php';

/**
 * Richiede ppa:shiki/mediainfo
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Mediainfo extends X_VlcShares_Plugins_Abstract {

	/**
	 * 
	 * @var Zend_Config
	 */
	private $options;
	
	private $_registeredEvents = array(
		X_VlcShares::TRG_SUBS_TRAVERSAL_PRE	=>	'getSubs',
	);
	
	public function __construct(Zend_Config $options) {
		$this->options = $options;
		$this->id = $this->options->id;
		$this->registerEvents($this->_registeredEvents);
	}

	public function getSubs($argv = array()) {
		
	}
	
	
	
}
