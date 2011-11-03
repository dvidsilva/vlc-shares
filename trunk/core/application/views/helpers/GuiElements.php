<?php 

/**
 * View helper: factory of gui elements.
 * Allow to get gui elements reference inside view scope
 * 
 */
class X_VlcShares_View_Helper_GuiElements extends Zend_View_Helper_Abstract {
	
	private $elements = array();
	
	/**
	 * View helper entry point
	 * @param string $name gui element name
	 * @return X_VlcShares_View_Helper_GuiElements|X_VlcShares_Elements_Element
	 */
	public function guiElements($name = null) {
		if ( $name !== null ) {
			return $this->getNew($name);
		} else {
			return $this;
		}
	}
	
	/**
	 * Register a new gui element class inside the
	 * this gui element broker
	 * @param string $name
	 * @param string $className
	 */
	public function registerElement($name, $className) {
		// maybe i should add some kind of type check
		if ( class_exists($className) && is_subclass_of($className, 'X_VlcShares_Elements_Element') ) {
			$this->elements[(string) $name] = $className;
		} else {
			throw new Exception("Invalid gui element class $className for $name: $className must extend X_VlcShares_Elements_Element");
		}
		return $this;
	}
	
	public function getNew($name) {
		if ( array_key_exists($name, $this->elements)) {
			$className = (string) $this->elements[(string) $name];
			$element = new $className();
			$element->setView($this->view);
			return $element;
		} else {
			throw new Exception("Unknown element name: $name");
		}
	}
	
	public function __call($methodName, $args) {
		// args is useless
		return $this->getNew($methodName);
	}
	
	public function getRegistered() {
		return $this->elements;
	}
	
	public function isRegistered($name) {
		return ( array_key_exists($name, $this->elements));
	}
	
}
