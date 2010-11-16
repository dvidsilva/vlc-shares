<?php

require_once 'X/Page/Item.php';

/**
 * A page item with a link param
 *  
 * @author ximarx
 */
class X_Page_Item_Link extends X_Page_Item {

	private $link = array();
	private $url = false;
	private $reset = false;
	private $route = 'default';
	
	/**
	 * Create a new X_Page_Item
	 * @param string $key item key in the list
	 * @param string $label item label
	 * @param array|string $link an array of Route params
	 * @param string $route route name
	 * @param boolean $reset if route reset is needed
	 */
	function __construct($key, $label, $link = array(), $route = 'default', $reset = false) {
		parent::__construct($key, $label);
		$this->setLink($link, $route, $reset);
	}
	
	/**
	 * Set a new link as a route or URL
	 * @param array|string $link
	 * @return X_Page_Item
	 */
	public function setLink($link, $route = 'default', $reset = false) {
		$this->reset = $reset;
		$this->route = $route;
		if ( !is_array($link) ) {
			$link = (string) $link;
			$this->url = true;
		} else {
			$this->url = false;
		}
		$this->link = $link;
		return $this;
	}
	
	/**
	 * Get the link array or a URL. Use isURL to check return type 
	 * @return array|string the link info
	 */
	public function getLink() {
		return $this->link;
	}
	
	/**
	 * Return true if the link is a URL (absolute or relative)
	 * or false if is an array of Route params
	 */
	public function isUrl() {
		return $this->url;
	}
	
	/**
	 * Get the controller param from the link
	 * @return string Controller name
	 * @throws Exception if isUrl is true
	 */
	public function getLinkController() {
		if ( !$this->isUrl() ) {
			if ( array_key_exists('controller', $this->link)) {
				return $this->link['controller'];
			} else {
				return null;
			}
		}
		throw new Exception('This link is an URL');
	}

	/**
	 * Get the action param from the link
	 * @return string action name
	 * @throws Exception if isUrl is true
	 */
	public function getLinkAction() {
		if ( !$this->isUrl() ) {
			if ( array_key_exists('action', $this->link)) {
				return $this->link['action'];
			} else {
				return null;
			}
		}
		throw new Exception('This link is an URL');
	}
	
	/**
	 * Get a param from the link
	 * @return string param key
	 * @throws Exception if isUrl is true
	 */
	public function getLinkParam($paramKey) {
		if ( !$this->isUrl() ) {
			if ( array_key_exists($paramKey, $this->link)) {
				return $this->link[$paramKey];
			} else {
				return null;
			}
		}
		throw new Exception('This link is an URL');
	} 
	
	/**
	 * Return true if a route reset is needed
	 * @return boolean
	 */
	public function isReset() {
		return $this->reset;
	}
	
	/**
	 * Return the route name
	 * @return string
	 */
	public function getRoute() {
		return $this->route;
	}
	
}

