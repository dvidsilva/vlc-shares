<?php 


interface X_VlcShares_Skins_SkinInterface {

	/**
	 * Return the decorator for the $elementName or a $fallbackName one if
	 * $elementName is unknown
	 * 
	 * @param string $elementName
	 * @param string $elementName
	 * @return X_VlcShares_Skins_DecoratorInterface
	 * @throws Exception if $elementName and $fallbackName aren't valid decorator type name
	 */
	public function getDecorator($elementName, $fallbackName = null);
	
}