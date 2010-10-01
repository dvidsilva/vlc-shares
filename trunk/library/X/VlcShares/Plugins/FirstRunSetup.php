<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins.php';
require_once 'X/VlcShares/Plugins/Abstract.php';

/**
 * Redirect application flow to installer controller (always)
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_FirstRunSetup extends X_VlcShares_Plugins_Abstract {

	function __construct() {
		$this->setPriority('gen_beforePageBuild');
	}
	
	/**
	 * Redirect to controls if vlc is running
	 * @param Zend_Controller_Action $controller
	 */
	public function gen_beforePageBuild(Zend_Controller_Action $controller ) {
		
		X_Debug::i("Plugin triggered: redirect to installer");
		
		$controller->getRequest()->setControllerName('installer')->setActionName('index')->setDispatched(false);
		
	}
}	

