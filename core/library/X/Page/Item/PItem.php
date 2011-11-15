<?php

require_once 'X/Page/Item/Link.php';

/**
 * A page item with a link param
 *  
 * @author ximarx
 */
class X_Page_Item_PItem extends X_Page_Item_Link {

	/**
	 * Marks the item as reproducible item (video file, audio file, stream source) 
	 */
	const TYPE_PLAYABLE = 'playable';
	
	/**
	 * Marks the item as a container of other items (for example folder) 
	 */
	const TYPE_CONTAINER = 'container';
	
	/**
	 * Marks the item as list item (for example a file) 
	 */
	const TYPE_ELEMENT = 'element';
	
	/**
	 * Marks the item as a request item (for example search)
	 */
	const TYPE_REQUEST = 'request';
	
	private $description;
	private $thumbnail;
	private $type; // valid values: playable, container, element 
	private $icon;
	private $generator;
	
	private $custom = array();
	
	/**
	 * Create a new X_Page_Item
	 * @param string $key item key in the list
	 * @param string $label item label
	 * @param array|string $link an array of Route params
	 */
	function __construct($key, $label, $link = array(), $route = 'default', $reset = false) {
		parent::__construct($key, $label, $link, $route, $reset);
		$this->type = self::TYPE_CONTAINER;
	}

	/**
	 * Get the $description
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
	
	/**
	 * Set a new description
	 * @param string $description
	 * @return X_Page_Item_PItem
	 */
	public function setDescription($description) {
		$this->description = (string) $description;
		return $this;
	}
	
	/**
	 * Get the thumbnail URL
	 * @return string
	 */
	public function getThumbnail() {
		return $this->thumbnail;
	}
	
	/**
	 * Set a thumbnail URL (must be absolute URL)
	 * @param string $thumbnail
	 * @return X_Page_Item_PItem
	 */
	public function setThumbnail($thumbnail) {
		$this->thumbnail = $thumbnail;
		return $this;
	}
	
	/**
	 * Get PItem type
	 * @return string standard values are X_Page_Item_PItem::TYPE_XXX 
	 */
	public function getType() {
		return $this->type;
	}
	
	/**
	 * Set a PItem type
	 * @param string $type one of TYPE_ constant or a custom one
	 * @return X_Page_Item_PItem
	 */
	public function setType($type = self::TYPE_CONTAINER) {
		$this->type = $type;
		return $this;
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

