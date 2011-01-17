<?php 

/** Zend_View_Helper_Abstract.php */
require_once 'Abstract.php';
require_once 'X/VlcShares/Skins/Default/XApplicationButton.php';

class X_VlcShares_View_Helper_XStatusLink extends X_VlcShares_View_Helper_Abstract {
	
	protected $_defaultOptions = array(
	);
	
	protected function getDefaultDecorator() {
		return new X_VlcShares_Skins_Default_XStatusLink();
	}
    
	protected function getDefaultOptions() {
		return $this->_defaultOptions;
	}
	
	/**
	 * @return X_VlcShares_View_Helper_XStatusLink
	 */
	public function xStatusLink($statusLink) {
		return $this->decorate($statusLink);
	}
}