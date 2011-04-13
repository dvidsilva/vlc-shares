<?php 


class X_VlcShares_Plugins_DailyMotion extends X_VlcShares_Plugins_Abstract {
	
    const VERSION = '0.1';
    const VERSION_CLEAN = '0.1';
	
	function __construct() {
		
		$this
			->setPriority('gen_beforeInit');
		
	}
	
	/**
	 * Registers a veoh hoster inside the hoster broker
	 */
	public function gen_beforeInit(Zend_Controller_Action $controller) {
		
		$this->helpers()->language()->addTranslation(__CLASS__);
		$this->helpers()->hoster()->registerHoster(new X_VlcShares_Plugins_Helper_Hoster_DailyMotion());
		
	}
	
}
