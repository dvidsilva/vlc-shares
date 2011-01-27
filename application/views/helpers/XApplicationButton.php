<?php 

require_once 'X/VlcShares/Skins/Default/XApplicationButton.php';

class X_VlcShares_View_Helper_XApplicationButton extends X_VlcShares_Elements_Element {
	
	public function getDefaultDecorator() {
		return new X_VlcShares_Skins_Default_XApplicationButton();
	}
	
	/**
	 * @return X_VlcShares_View_Helper_XHeader
	 */
	public function xApplicationButton($manageLink) {
		return $this->render($manageLink);
	}
}