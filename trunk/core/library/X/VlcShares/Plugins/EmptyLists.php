<?php

require_once ('X/VlcShares/Plugins/Abstract.php');

/**
 *	Show a "warning" message if the playlist is empty
 *  
 * @author ximarx
 */
class X_VlcShares_Plugins_EmptyLists extends X_VlcShares_Plugins_Abstract {
	
	function __construct() {
		// gen_afterPageBuild must be called as first
		$this->setPriority('gen_afterPageBuild', 0);
	}
	
	public function gen_afterPageBuild(X_Page_ItemList_PItem $items, Zend_Controller_Action $controller) {
		if ( count($items->getItems()) == 0 ) {
			X_Debug::i("Plugin triggered");
			$item = new X_Page_Item_PItem('emptylists', X_Env::_('p_emptylists_moveaway'));
			$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(X_Env::completeUrl($controller->getHelper('url')->url()));
			$items->append($item);
		}
	}
		
}
