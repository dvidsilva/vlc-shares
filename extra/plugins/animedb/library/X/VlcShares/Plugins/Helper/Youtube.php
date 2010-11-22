<?php 

require_once 'X/VlcShares/Plugins/Helper/Abstract.php';

class X_VlcShares_Plugins_Helper_Youtube extends X_VlcShares_Plugins_Helper_Abstract {

	
	private $_cachedSearch = array();
	private $_location = null;
	/**
	 */
	private $_fetched = false;
	
	/**
	 * Set source location
	 * 
	 * @param $location Youtube video ID or URL
	 * @return X_VlcShares_Plugins_Helper_Youtube
	 */
	function setLocation($location) {
		if ( $this->_location != $location ) {
			$this->_location = $location;
			if ( array_key_exists($location, $this->_cachedSearch) ) {
				$this->_fetched = $this->_cachedSearch[$location];
			} else {
				$this->_fetched = false;
			}
		}
		return $this;
	}
	
	
	protected function _fetch() {
		if ( $this->_location == null ) {
			X_Debug::e('Trying to fetch a megavideo location without a location');
			throw new Exception('Trying to fetch a megavideo location without a location');
		}
		if ( $this->_fetched === false ) {
			//$this->_fetched = new Megavideo($this->_location);
			//$this->_cachedSearch[$this->_location] = $this->_fetched;
		}
	}
}