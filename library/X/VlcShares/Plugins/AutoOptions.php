<?php

/**
 * Choose options (profile, output, interface)
 * using user-agent
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_AutoOptions extends X_VlcShares_Plugins_Abstract {

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
			->setPriority('gen_beforePageBuild', 1)
			;
			
	}

	// choose the gui to be used
	public function gen_beforePageBuild(/*X_Page_ItemList_PItem $items, */Zend_Controller_Action $controller) {

		X_Debug::i("Tuning options");
		
		$this->loadDevice();
		
		if ( $this->device ) {
			
			$guiClass = trim($this->device->getGuiClass());
			
			X_Debug::i("Device configs: {{label: {$this->device->getLabel()}, guiClass: {$guiClass}}");
			
			if ( $guiClass != '' ) {

				// FIXME port old renderers to new RendererInterface
				
				// unregister any other renderer class
				foreach ($this->guis as $className ) {
					if ( $guiClass != $className ) {
						X_VlcShares_Plugins::broker()->unregisterPluginClass($className);
					}
				}
				
				// FIXME remove this check (keep "else" only)
				$reflectionGuiClass = new ReflectionClass($guiClass);
				
				if ( !$reflectionGuiClass->implementsInterface('X_VlcShares_Plugins_RendererInterface') ) {
					
					X_Debug::i("No renderer interface");
					
					// force rendering
					$plugins = X_VlcShares_Plugins::broker()->getPlugins();
					foreach ($plugins as $plugin ) {
						if ( is_a($plugin, $guiClass) ) {
							if ( method_exists($plugin, 'forceRendering') ) {
								$plugin->forceRendering();
							}
						}
					}
				
				} else {
				
					X_Debug::i("New renderer interface");
					
					// uses RendererInterface
					foreach (X_VlcShares_Plugins::broker()->getPlugins() as $pluginKey => $pluginObj) {
						if ( $pluginObj instanceof X_VlcShares_Plugins_RendererInterface ) {
							if ( $guiClass == get_class($pluginObj) ) {
								/* @var $pluginObj X_VlcShares_Plugins_RendererInterface */
								$pluginObj->setDefaultRenderer(true);
							}
						}
					}
				}
				
			}
		}
		
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