<?php 

class X_VlcShares_Elements_InnerBlock extends X_VlcShares_Elements_Block {
	
	public function getDefaultDecorator() {
		return X_VlcShares_Skins_Manager::i()->getDecorator(X_VlcShares_Skins_Manager::INNERBLOCK);
	}
	
}
