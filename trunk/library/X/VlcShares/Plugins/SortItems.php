<?php

require_once ('X/VlcShares/Plugins/Abstract.php');

/**
 * Sorts items
 * If provider is FileSystem adds directory/files sort too
 *  
 * @author ximarx
 */
class X_VlcShares_Plugins_SortItems extends X_VlcShares_Plugins_Abstract {
	
	function __construct() {
		// gen_afterPageBuild must be called as first
		$this->setPriority('orderShareItems');
	}
	
	/**
	 * Sorts items:
	 * 		if provider is FileSystem uses a folder/file sort
	 * 		else alphabetical one
	 * @param array &$items array of X_Page_Item_PItem
	 * @param string $provider id of the plugin the handle the request
	 * @param Zend_Controller_Action $controller
	 */
	public function orderShareItems(&$items, $provider, Zend_Controller_Action $controller) {
		X_Debug::i('Plugin triggered');
		try {
			$plugin = X_VlcShares_Plugins::broker()->getPlugins($provider);
			if ( is_a($plugin, 'X_VlcShares_Plugins_FileSystem' ) ) {
				X_Debug::i('Sort sortFolderBased');
				usort($items, array(__CLASS__, 'sortFolderBased'));
			} else {
				X_Debug::i('Sort generic');
				usort($items, array(__CLASS__, 'sortAlphabetically'));
			}
		} catch ( Exception $e ) {
			X_Debug::w("Problem while sorting: {$e->getMessage()}");
		}
	}
	
	static function sortFolderBased(X_Page_Item_PItem $item1, X_Page_Item_PItem $item2) {
		
		if ( $item1->getType() == X_Page_Item_PItem::TYPE_CONTAINER ) {
			if ( $item2->getType() == X_Page_Item_PItem::TYPE_CONTAINER ) {
				return self::sortAlphabetically($item1, $item2);
			} else {
				return -1;
			}
		} else {
			if ( $item2->getType() == X_Page_Item_PItem::TYPE_CONTAINER ) {
				return 1;
			} else {
				return self::sortAlphabetically($item1, $item2);
			}
		}
	}
	
	static function sortAlphabetically(X_Page_Item_PItem $item1, X_Page_Item_PItem $item2) {
		// prevent warning for array modification
		$label1 = $item1->getLabel();
		$label2 = $item2->getLabel();
		
		return strcasecmp($label1, $label2);
	}
	
}
