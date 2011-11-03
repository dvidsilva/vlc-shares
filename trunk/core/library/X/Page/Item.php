<?php

/**
 * Base class of Page items
 *  
 * @author ximarx
 */
class X_Page_Item {

	private $key;
	private $label;
	private $highlight;
	
	/**
	 * Create a new X_Page_Item
	 * @param string $key item key in the list
	 * @param string $label item label
	 */
	function __construct($key, $label) {
		$this->setKey($key)->setLabel($label);
	}
	
	/**
	 * Set a new key for the item
	 * @param string $key
	 * @return X_Page_Item
	 */
	public function setKey($key) {
		$this->key = (string) $key;
		return $this;
	}
	
	/**
	 * Get the item key
	 * @return string the key
	 */
	public function getKey() {
		return $this->key;
	}
	
	/**
	 * Set a new label for the item
	 * @param string $label
	 * @return X_Page_Item
	 */
	public function setLabel($label) {
		$this->label = (string) $label;
		return $this;
	}
	
	/**
	 * Get the item label
	 * @return string the label
	 */
	public function getLabel() {
		return $this->label;
	}
	
	/**
	 * Set an highlight status for the item
	 * @param bool $state
	 * @return X_Page_Item
	 */
	public function setHighlight($state) {
		$this->highlight = (bool) $state;
		return $this;
	}
	
	/**
	 * Check if the item is highlighted
	 * @return boolean
	 */
	public function isHighlight() {
		return $this->highlight;
	}
}

