<?php

require_once 'X/VlcShares/Plugins/Abstract.php';


/**
 * This is a soc plugin:
 * It's an example of how to add warning messages in dashboard
 * This plugin only produce info message for the dashboard
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Sample extends X_VlcShares_Plugins_Abstract {
	
	public function __construct() {
		$this->setPriority('getIndexMessages', 1); // i add it near the top of the stack
	}
	
	/**
	 * Return the message
	 * @param Zend_Controller_Action $this
	 * @return array The format of the array should be:
	 * 		array(
	 * 			array(
	 * 				'type' => message type,
	 * 				'text' => the message you want to show,
	 * 			), ...
	 * 		)
	 */
	public function getIndexMessages(Zend_Controller_Action $controller) {
		
		return array(
			array(
				'type' => 'info',
				'text' => 'Sample plugin installed'
			),
		);
		
	}
	
	
}
