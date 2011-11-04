<?php 


class X_VlcShares_Plugins_Veoh extends X_VlcShares_Plugins_Abstract {
	
	// base64 encrypted
	const APIKEY = 'NTY5Nzc4MUUtMUM2MC02NjNCLUZGRDgtOUI0OUQyQjU2RDM2';
    const IV     = "ZmY1N2NlYzMwYWVlYTg5YTBmNTBkYjQxNjRhMWRhNzI=";
    const SKEY   = "ODY5NGRmY2RkODY0Y2FhYWM4OTAyZDdlYmQwNGVkYWU=";
	
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
		$this->helpers()->hoster()->registerHoster(new X_VlcShares_Plugins_Helper_Hoster_Veoh());
		
	}
	
}
