<?php 


interface X_VlcShares_Skins_ParentDecoratorInterface {
	
	/**
	 * Get a special subdecorator for the elementType
	 * when the parent is this decorator
	 * (allow to use subdecorator per-parent)
	 * 
	 * @param string $elementType
	 * @return X_VlcShares_Skins_DecoratorInterface
	 */
	public function getSubDecorator($elementType);
	
}