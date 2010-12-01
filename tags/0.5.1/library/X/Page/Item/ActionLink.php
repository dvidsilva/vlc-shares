<?php

require_once 'X/Page/Item/Link.php';

/**
 * A page item with a link param
 *  
 * @author ximarx
 */
class X_Page_Item_ActionLink extends X_Page_Item_Link {
	
	private $icon;
	private $generator;
	
	private $custom = array();
	
	/**
	 * Create a new X_Page_Item_ActionLink
	 * @param string $key item key in the list
	 * @param string $label item label
	 * @param array|string $link an array of Route params
	 */
	function __construct($key, $label, $link = array(), $route = 'default', $reset = false) {
		parent::__construct($key, $label, $link, $route, $reset);
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
	 * @return X_Page_Item_PItem
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
	 * @return X_Page_Item_PItem
	 */
	public function setGenerator($generator) {
		$this->generator = $generator;
		return $this;
	}
	
	/**
	 * Get a custom value of the PItem
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
	 * @return X_Page_Item_PItem
	 */
	public function setCustom($key, $value) {
		$this->custom[$key] = $value;
		return $this;
	}
	
}

