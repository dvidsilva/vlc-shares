<?php 


class X_VlcShares_Plugins_Helper_Cache extends X_VlcShares_Plugins_Helper_Abstract {
	
	/**
	 * @var X_VlcShares_Plugins_Cache
	 */
	private $plugin = null;
	
	public function __construct($plugin) {
		if ( $plugin instanceof X_VlcShares_Plugins_Cache ) {
			$this->plugin = $plugin; 
		}
	}
	
	/**
	 * @return X_VlcShares_Plugins_Cache
	 */
	public function getPlugin() {
		return $this->plugin;
	}
	
	/**
	 * Store an item in cache manually for $validity time
	 * @param string $key content key for retrieval
	 * @param string $content
	 * @param int $validity number of minutes entry will be valid
	 * @return string
	 */
	public function storeItem($key, $content, $validity) {
		return $this->getPlugin()->storeItem($key, $content, $validity);
	}
	
	/**
	 * Retrieve an item from the cache using $key
	 * @param string $key
	 * @return string
	 * @throws Exception if no valid item with $key found 
	 */
	public function retrieveItem($key) {
		return $this->getPlugin()->retrieveItem($key);
	}
}

