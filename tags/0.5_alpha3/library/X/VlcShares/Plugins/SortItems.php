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
	 * @param array &$items list of items
	 * @param string $provider id of the plugin the handle the request
	 * @param Zend_Controller_Action $controller
	 */
	public function orderShareItems(&$items, $provider, Zend_Controller_Action $controller) {
		X_Debug::i('Plugin triggered');
		try {
			$plugin = X_VlcShares_Plugins::broker()->getPlugins($provider);
			if ( is_a($plugin, 'X_VlcShares_Plugins_FileSystem' ) ) {
				X_Debug::i('Sort filesystem');
				usort($items, array(__CLASS__, 'sortFileSystem'));
			} else {
				X_Debug::i('Sort generic');
				usort($items, array(__CLASS__, 'sortAlphabetically'));
			}
		} catch ( Exception $e ) {
			X_Debug::w("Problem while sorting: {$e->getMessage()}");
		}
	}
	
	static function sortFileSystem($item1, $item2) {
		
		// prevent warning for array modification
		$label1 = $item1['label'];
		$label2 = $item2['label'];
		
		if ( substr($label1, -1) == '/' ) {
			if ( substr($label2, -1) == '/' ) {
				return self::sortAlphabetically($item1, $item2);
			} else {
				return -1;
			}
		} else {
			if ( substr($label2, -1) == '/' ) {
				return 1;
			} else {
				return self::sortAlphabetically($item1, $item2);
			}
		}
	}
	
	static function sortAlphabetically($item1, $item2) {
		// prevent warning for array modification
		$label1 = $item1['label'];
		$label2 = $item2['label'];
		
		return strcasecmp($label1, $label2);
	}
	
}
?>