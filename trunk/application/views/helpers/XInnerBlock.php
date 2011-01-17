<?php 

/** Zend_View_Helper_Abstract.php */
require_once 'XBlock.php';
require_once 'X/VlcShares/Skins/Default/XInnerBlock.php';

class X_VlcShares_View_Helper_XInnerBlock extends X_VlcShares_View_Helper_XBlock {
	
	protected function getDefaultDecorator() {
		return new X_VlcShares_Skins_Default_XInnerBlock();
	}
    
	/**
	 * @return X_VlcShares_View_Helper_XInnerBlock
	 */
	public function xInnerBlock($newInstance = true) {
		return $this->xBlock($newInstance);
	}
}