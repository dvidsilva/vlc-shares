<?php

require_once 'X/Page/Item/ActionLink.php';
require_once 'X/Page/ItemList/ActionLink.php';

/**
 * A page item with a link param
 *  
 * @author ximarx
 */
class X_Page_Item_ManageLink extends X_Page_Item_ActionLink {
	
	private $title;
	/**
	 * @var X_Page_ItemList_ActionLink
	 */
	private $subinfos;
	
	private $custom = array();
	
	/**
	 * Create a new X_Page_Item_ManageLink
	 * @param string $key item key in the list
	 * @param string $label item label
	 * @param array|string $link an array of Route params
	 */
	function __construct($key, $label, $link = array(), $route = 'default', $reset = false) {
		parent::__construct($key, $label, $link, $route, $reset);
		$this->subinfos = new X_Page_ItemList_ActionLink();
	}

	
	/**
	 * Get the title
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * Set a new title
	 * @param string $title
	 * @return X_Page_Item_ManageLink
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}
	
	/**
	 * Get the generator who create this item
	 * @return X_Page_ItemList_ActionLink
	 */
	public function getSubinfos() {
		return $this->subinfos;
	}
	
	/**
	 * Set the generator
	 * @param X_Page_ItemList_ActionLink $subinfos
	 * @return X_Page_Item_PItem
	 */
	public function setSubinfos(X_Page_ItemList_ActionLink $subinfos) {
		$this->subinfos = $subinfos;
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

