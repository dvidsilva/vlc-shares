<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/VlcShares/Plugins/ResolverInterface.php';
require_once 'Zend/Config.php';

/**
 * Filter out invalid files extension from the list of items for FileSystem provider
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_FilterExt extends X_VlcShares_Plugins_Abstract {

	private $validExtensions = null;

	function __construct() {
		$this->setPriority('filterShareItems');
	}
	
	/**
	 * @param array $item
	 * @param string $provider
	 * @param Zend_Controller_Action $controller
	 * @return boolean|null true or null if file is ok, false otherwise (will be filtered out)
	 */
	public function filterShareItems($item, $provider, Zend_Controller_Action $controller) {
		try {
			$plugin = X_VlcShares_Plugins::broker()->getPlugins($provider);
			if ( is_a($plugin, 'X_VlcShares_Plugins_FileSystem' ) && 
				$plugin instanceof X_VlcShares_Plugins_ResolverInterface ) {
				// i use instanceof ResolverInterface
				// so i have code suggestions
				// X_VlcShares_Plugins_FileSystem register a custom param in item
				// for location lookup
				$location = $plugin->resolveLocation(@$item['X_VlcShares_Plugins_FileSystem:location']);
				// i must check for $location !== false as a fallback for no custom param case
				if ( $location !== false && file_exists($location) ) {
					// i have a location to check for hidden files:
					if ( $this->_checkEntry($location) === false) {
						X_Debug::i("Plugin triggered, item filtered: $location");
						return false;
					}
				}
				//X_Debug::i('Plugin triggered');
			}
		} catch (Exception $e) {
			X_Debug::w("Problem while filtering: {$e->getMessage()}");
		}
	}
	
	/**
	 * 
	 * @param string $entry path
	 */
	private function _checkEntry($entry) {
		
		if ( $this->validExtensions === null ) {
			$this->validExtensions = explode('|', $this->config('valid', 'avi|mkv|mpg|mpeg|mov|3gp|mp4|mp3|mp2|ts|mpv|mpa|mpgv|mpga|divx|dvx|flv'));
		}
		
		if ( is_file($entry) ) {
			if ( array_search(strtolower(pathinfo($entry, PATHINFO_EXTENSION)), $this->validExtensions ) !== false ) {
				return true;
			} else {
				return false;
			}
		}
		return true;
	}
}	

