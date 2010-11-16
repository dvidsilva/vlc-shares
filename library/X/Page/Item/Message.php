<?php

require_once 'X/Page/Item.php';

/**
 * A message
 *  
 * @author ximarx
 */
class X_Page_Item_Message extends X_Page_Item {

	/**
	 * Marks the item as reproducible item (video file, audio file, stream source) 
	 */
	const TYPE_INFO = 'info';
	
	/**
	 * Marks the item as a container of other items (for example folder) 
	 */
	const TYPE_WARNING = 'warning';
	
	/**
	 * Marks the item as list item (for example a file) 
	 */
	const TYPE_ERROR = 'error';
	
	/**
	 * Marks the item as a request item (for example search)
	 */
	const TYPE_FATAL = 'fatal';
	
	private $type; // valid values: playable, container, element 
	private $generator;
	
	private $custom = array();
	
	/**
	 * Create a new X_Page_Item_Message
	 * @param string $key item key in the list
	 * @param string $label item label
	 * @param array|string $link an array of Route params
	 */
	function __construct($key, $label, $type = self::TYPE_INFO) {
		parent::__construct($key, $label);
		$this->setType($type);
	}

	
	/**
	 * Get Message type
	 * @return string standard values are X_Page_Item_Message::TYPE_XXX 
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * Set a Message type
	 * @param string $type one of TYPE_ constant or a custom one
	 * @return X_Page_Item_Message
	 */
	public function setType($type = self::TYPE_INFO) {
		$this->type = $type;
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
	 * @return X_Page_Item_Message
	 */
	public function setGenerator($generator) {
		$this->generator = $generator;
		return $this;
	}
	
	/**
	 * Get a custom value of the Message
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
	 * @return X_Page_Item_Message
	 */
	public function setCustom($key, $value) {
		$this->custom[$key] = $value;
		return $this;
	}
	
}

