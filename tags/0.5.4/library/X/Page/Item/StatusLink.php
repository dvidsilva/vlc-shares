<?php

require_once 'X/Page/Item/Link.php';

/**
 * A page item with a link param
 *  
 * @author ximarx
 */
class X_Page_Item_StatusLink extends X_Page_Item_Link {
	
	private $icon;
	private $generator;
	
	/**
	 * Marks the item as a label 
	 */
	const TYPE_LABEL = 'label';
	
	/**
	 * Marks the item as a button 
	 */
	const TYPE_BUTTON = 'button';
	
	/**
	 * Marks the item as a simple link 
	 */
	const TYPE_LINK = 'link';
	
	
	
	private $custom = array();
	
	/**
	 * Create a new X_Page_Item_StatusLink
	 * @param string $key item key in the list
	 * @param string $label item label
	 * @param array|string $link an array of Route params
	 */
	function __construct($key, $label, $type = self::TYPE_LABEL, $link = array(), $route = 'default', $reset = false) {
		parent::__construct($key, $label, $link, $route, $reset);
		$this->setType($type);
	}

	
	/**
	 * Get the icon URL
	 * @return string an absolute URL to a icon image
	 */
	public function getIcon() {
		return $this->icon;
	}
	
	/**
	 * Set a new absolute URL to an icon image
	 * @param string $icon
	 * @return X_Page_Item_StatusLink
	 */
	public function setIcon($icon) {
		$this->icon = $icon;
		return $this;
	}
	
	/**
	 * Get the generator who create this item
	 * @return string
	 */
	public function getGenerator() {
		return $this->generator;
	}
	
	/**
	 * Set the generator
	 * @param string $generator
	 * @return X_Page_Item_StatusLink
	 */
	public function setGenerator($generator) {
		$this->generator = $generator;
		return $this;
	}
	
	/**
	 * Get a custom value of the StatusLink
	 * @param string $key the custom key
	 * @return mixed|null the custom value if exists or null
	 */
	public function getCustom($key) {
		if ( array_key_exists($key, $this->custom) ) {
			return @$this->custom[$key];
		} else {
			return null;
		}
	}

	/**
	 * Get an array of custom params
	 * @return array
	 */
	public function getCustoms() {
		return $this->custom;
	}
	
	/**
	 * Set a custom value (if exists, the older will be overwritten)
	 * @param string $key
	 * @param string $value
	 * @return X_Page_Item_StatusLink
	 */
	public function setCustom($key, $value) {
		$this->custom[$key] = $value;
		return $this;
	}
	
	/**
	 * Get type
	 * @return string standard values are X_Page_Item_StatusLink::TYPE_XXX 
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * Set a type
	 * @param string $type one of TYPE_ constant or a custom one
	 * @return X_Page_Item_StatusLink
	 */
	public function setType($type = self::TYPE_CONTAINER) {
		$this->type = $type;
		return $this;
	}
	
	
}

