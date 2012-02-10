<?php 


class X_VlcShares_Plugins_Hulu extends X_VlcShares_Plugins_Abstract {
	
    const VERSION = '0.1alpha2';
    const VERSION_CLEAN = '0.1';
	
	function __construct() {
		
		// no page parser lib, nothing to do 
		if ( !class_exists('X_VlcShares_Plugins_Utils') ) {
			return;
		}
		
		$this
			->setPriority('getIndexActionLinks',1)
			->setPriority('getIndexMessages',1)
			->setPriority('gen_beforeInit');
		
	}
	
	/**
	 * Registers a veoh hoster inside the hoster broker
	 */
	public function gen_beforeInit(Zend_Controller_Action $controller) {
		
		$this->helpers()->language()->addTranslation(__CLASS__);
		$this->helpers()->registerHelper('hulu', new X_VlcShares_Plugins_Helper_Hulu());
		$this->helpers()->hoster()->registerHoster(new X_VlcShares_Plugins_Helper_Hoster_Hulu());
		
	}
	
	/**
	 * Show the info message with the link to the thread
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_Message
	 */
	public function getIndexMessages(Zend_Controller_Action $controller) {
		$messages = new X_Page_ItemList_Message();
		
		$messages->append(X_VlcShares_Plugins_Utils::getMessageEntry(
				$this->getId(), 
				'p_hulu_message_linktothread',
				X_Page_Item_Message::TYPE_INFO
		));
	
		return $messages;
	}
	
	/**
	 * Add the link add video link to actionLinks
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ActionLink
	 */
	public function getIndexActionLinks(Zend_Controller_Action $controller) {
	
		$link = new X_Page_Item_ActionLink($this->getId(), X_Env::_('p_hulu_linktohulutest'));
		$link->setIcon('/images/plus.png')
		->setLink(
				Zend_Controller_Front::getInstance()->getBaseUrl().'/test-hulu.php'
		);
		return new X_Page_ItemList_ActionLink(array($link));
	}
	
}
