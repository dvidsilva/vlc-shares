<?php 

require_once 'X/VlcShares/Plugins/Abstract.php';

/**
 * Expose and allow the selection of different transcoding profiles
 * for vlc
 * 
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Profiles extends X_VlcShares_Plugins_Abstract {
	
	public function __construct() {
		$this->setPriority('getModeItems');
	}
	
	/**
	 * Give back the link for change modes
	 * and the default config for this location
	 * 
	 * @param string $provider
	 * @param string $location
	 * @param Zend_Controller_Action $controller
	 */
	public function getModeItems($provider, $location, Zend_Controller_Action $controller) {
		
		
		
	}


	
	
}

