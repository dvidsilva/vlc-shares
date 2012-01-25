<?php 

class X_VlcShares_Plugins_2Shared extends X_VlcShares_Plugins_Abstract  {
	
	const VERSION = '0.1beta';
	const VERSION_CLEAN = '0.1';
	
	function __construct() {
		$this->setPriority('gen_beforeInit');
	}

	/**
	 * Inizialize translator for this plugin
	 * @param Zend_Controller_Action $controller
	 */
	function gen_beforeInit(Zend_Controller_Action $controller) {
		$this->helpers()->language()->addTranslation(__CLASS__);
		$this->helpers()->hoster()->registerHoster(
				new X_VlcShares_Plugins_Helper_Hoster_2Shared()
		);
	}
	
}
