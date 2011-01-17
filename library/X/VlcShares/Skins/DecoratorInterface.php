<?php 


interface X_VlcShares_Skins_DecoratorInterface {
	
	/**
	 * @param string $content content to decorate
	 * @param stdClass $options decorator options
	 */
	public function decorate($content, $options);
	/**
	 * @param Zend_View $view
	 */
	public function setView($view); 
	
}