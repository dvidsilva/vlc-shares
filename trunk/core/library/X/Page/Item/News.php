<?php

require_once 'X/Page/Item.php';

/**
 * A News
 *  
 * @author ximarx
 */
class X_Page_Item_News extends X_Page_Item {

	
	private $tab;
	private $content;
	 
	private $generator;
	
	private $custom = array();
	
	/**
	 * Create a new X_Page_Item_News
	 * @param string $key item key in the list
	 * @param string $label item label
	 * @param string $title
	 */
	function __construct($key, $label, $tab = null) {
		parent::__construct($key, $label);
		$this->setTab($tab);
	}

	
	/**
	 * Get News tab name
	 * @return string 
	 */
	public function getTab() {
		return $this->tab;
	}
	
	/**
	 * Set a News tab name
	 * @param string $tab
	 * @return X_Page_Item_News
	 */
	public function setTab($tab) {
		$this->tab = $tab;
		return $this;
	}

	/**
	 * Get News content
	 * @return string 
	 */
	public function getContent() {
		return $this->content;
	}
	
	/**
	 * Set a News content
	 * @param string $content
	 * @return X_Page_Item_News
	 */
	public function setContent($content) {
		$this->content = $content;
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
	 * @return X_Page_Item_News
	 */
	public function setGenerator($generator) {
		$this->generator = $generator;
		return $this;
	}
	
	/**
	 * Get a custom value of the News
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
	 * @return X_Page_Item_News
	 */
	public function setCustom($key, $value) {
		$this->custom[$key] = $value;
		return $this;
	}
	
}

