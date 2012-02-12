<?php

/**
 * Choose options (profile, output, interface)
 * using user-agent
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Devices extends X_VlcShares_Plugins_Abstract {

	private $guis = array(
		'X_VlcShares_Plugins_MobileRenderer',
		'X_VlcShares_Plugins_WiimcPlxRenderer',
	);
	
	/**
	 * @var Application_Model_Device|false|null
	 */
	private $device = null;
	
	function __construct() {
		$this
			->setPriority('getIndexManageLinks')
			->setPriority('gen_beforeInit')
			->setPriority('gen_beforePageBuild', 1)
			;
			
	}
	
	/**
	 * Add the link for -manage-devices-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_devices_mlink'));
		$link->setTitle(X_Env::_('p_devices_managetitle'))
			->setIcon('/images/devices/logo.png')
			->setLink(array(
					'controller'	=>	'devices',
					'action'		=>	'index',
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
	
	}
	


	// choose the gui to be used
	public function gen_beforePageBuild(/*X_Page_ItemList_PItem $items, */Zend_Controller_Action $controller) {

		X_Debug::i("Tuning options");
		
		try {
			
			/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache  */
			$cacheHelper = $this->helpers()->helper('cache');
			$lastdevices = false;
			try {
				$lastdevices = $cacheHelper->retrieveItem('devices::lastdevices');
			} catch (Exception $e) { /* key missing */ }
			if ( $lastdevices ) {
				$lastdevices = @unserialize($lastdevices);
			} 
			if ( !is_array($lastdevices) ) {
				$lastdevices = array();
			}

			foreach ($lastdevices as $key => $time ) {
				if ( $time < time() ) {
					unset($lastdevices[$key]);
				}
			}
			
			if ( !array_key_exists($_SERVER['HTTP_USER_AGENT'], $lastdevices) ) {
				$lastdevices[$_SERVER['HTTP_USER_AGENT']] = (time() + ( 15 * 60 ));
			}
			
			// clear the cache entry every 60 min if untouched 
			$cacheHelper->storeItem('devices::lastdevices', serialize($lastdevices), 60);
			
		} catch (Exception $e) {
			X_Debug::i("User agent cannot be added to the cache");
		}
			
		$guiClass = $this->helpers()->devices()->getDefaultDeviceGuiClass();
		
		X_Debug::i("Device configs: {{label: {$this->helpers()->devices()->getDeviceLabel()}, guiClass: {$guiClass}}");
			
		foreach (X_VlcShares_Plugins::broker()->getPlugins() as $pluginKey => $pluginObj) {
			if ( $pluginObj instanceof X_VlcShares_Plugins_RendererInterface ) {
				if ( $guiClass == get_class($pluginObj) ) {
					/* @var $pluginObj X_VlcShares_Plugins_RendererInterface */
					$pluginObj->setDefaultRenderer(true);
				}
			}
		}
		
		// disable this trigger, prevent double initialization
		$this->setPriority('gen_beforePageBuild', -1);
		
	}
	
	/**
	 * Load the device features inside the $this->device
	 */
	private function loadDevice() {
		if ( $this->device === null ) {
			
			$this->device = false;
			$devices = Application_Model_DevicesMapper::i()->fetchAll();
			
			/* @var Application_Model_Device $device */
			foreach ($devices as $device) {
				// if exact do an == comparison
				if ( ($device->isExact() && $device->getPattern() == $_SERVER['HTTP_USER_AGENT'])
					// otherwise a regex match
						|| (!$device->isExact() && preg_match($device->getPattern(), $_SERVER['HTTP_USER_AGENT'] ) > 0 ) ) {
					
					// valid $device found;
					$this->device = $device;
						
				} // false + 0 matches
			}
		}
	}

}