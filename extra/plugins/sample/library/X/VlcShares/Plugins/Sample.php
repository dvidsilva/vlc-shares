<?php

require_once 'X/VlcShares/Plugins/Abstract.php';

/**
 * This is a soc plugin:
 * It's an example of how to add warning messages in dashboard
 * This plugin only produce a warning message for the dashboard
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Sample extends X_VlcShares_Plugins_Abstract {
	
	public function __construct() {
		$this->setPriority('getIndexMessages', 1); // i add it near the top of the queue
	}
	
	/**
	 * Return the HELLO WORLD message
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Message
	 */
	public function getIndexMessages(Zend_Controller_Action $controller) {
		$message = new X_Page_Item_Message($this->getId(), 'HELLO WORLD');
		$message->setType(X_Page_Item_Message::TYPE_WARNING);
		return new X_Page_ItemList_Message(array($message));
	}
	
}
