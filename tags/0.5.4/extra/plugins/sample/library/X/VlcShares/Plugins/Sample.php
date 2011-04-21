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
	 * Return the HELLO WORLD message, one for message type
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Message
	 */
	public function getIndexMessages(Zend_Controller_Action $controller) {
		$messages = array();
		$message = new X_Page_Item_Message($this->getId(), 'HELLO WORLD (INFO)');
		$message->setType(X_Page_Item_Message::TYPE_INFO);
		$messages[] = $message;
		$message = new X_Page_Item_Message($this->getId(), 'HELLO WORLD (SUCCESS)');
		$message->setType(X_Page_Item_Message::TYPE_SUCCESS);
		$messages[] = $message;
		$message = new X_Page_Item_Message($this->getId(), 'HELLO WORLD (WARNING)');
		$message->setType(X_Page_Item_Message::TYPE_WARNING);
		$messages[] = $message;
		$message = new X_Page_Item_Message($this->getId(), 'HELLO WORLD (ERROR)');
		$message->setType(X_Page_Item_Message::TYPE_ERROR);
		$messages[] = $message;
		$message = new X_Page_Item_Message($this->getId(), 'HELLO WORLD (FATAL)');
		$message->setType(X_Page_Item_Message::TYPE_FATAL);
		$messages[] = $message;
		return new X_Page_ItemList_Message($messages);
	}
	
}
