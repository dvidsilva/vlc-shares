<?php 

/**
 * Prepare the X_SopCast instance
 */
class X_VlcShares_Plugins_Helper_SopCast extends X_VlcShares_Plugins_Helper_Abstract {
	
	/**
	 * 
	 * @var Zend_Config
	 */
	private $options = null;
	
	function __construct(Zend_Config $options) {
		$this->options = $options;
		if ( $this->isEnabled() ) {
			X_SopCast::getInstance()->setOptions($this->options);
		}
	}
	
	/**
	 * Show if helper is enabled and ready to get infos
	 * @return boolean
	 */
	function isEnabled() {
		return ($this->options->get('enabled', false) && file_exists($this->options->get('path', false))); 
	}
	
	
}

