<?php 

/** Zend_View_Helper_Abstract.php */
require_once 'Abstract.php';
require_once 'X/VlcShares/Skins/Default/XHeader.php';

class X_VlcShares_View_Helper_XHeader extends X_VlcShares_View_Helper_Abstract {
	
	protected $_defaultOptions = array(
	);
	
	protected function getDefaultDecorator() {
		return new X_VlcShares_Skins_Default_XHeader();
	}
    
	protected function getDefaultOptions() {
		return $this->_defaultOptions;
	}
	
	/**
	 * @return X_VlcShares_View_Helper_XHeader
	 */
	public function xHeader($newInstance = true) {
		if ($newInstance) {
			// create a new instance with standard options
			$this->newInstance(
				$this->_defaultOptions
			);
		}
		return $this;
	}
}