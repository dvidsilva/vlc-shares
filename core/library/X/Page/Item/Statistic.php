<?php

require_once 'X/Page/Item/Link.php';

/**
 * A page item with a link param
 * 
 * @author ximarx
 */
class X_Page_Item_Statistic extends X_Page_Item_Link {
	
	private $title;
	private $generator;
	
	private $stats = array ();
	
	/**
	 * Create a new X_Page_Item_Statistic
	 * @param string $key item key in the list
	 * @param string $label item label
	 * @param array|string $link an array of Route params
	 */
	function __construct($key, $label) {
		parent::__construct ( $key, $label );
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
	 * @return X_Page_Item_Statistic
	 */
	public function setTitle($title) {
		$this->title = $title;
		return $this;
	}
	
	/**
	 * Set the route to the stats custom provider
	 * @param array $link
	 * @param string $route
	 * @param boolean $reset
	 * @return X_Page_Item_Statistic
	 * @throws Exception if $link isn't an array
	 */
	public function setLink($link = array(), $route = 'default', $reset = false) {
		if (is_array ( $link )) {
			parent::setLink ( $link, $route, $reset );
			return $this;
		} else {
			throw new Exception ( 'Only routes are allowed inside Statistic' );
		}
	}
	
	/**
	 * Get the route params for the stats provider or NULL if not setted
	 * @return array|null
	 */
	public function getLink() {
		$link = parent::getLink();
		if ( count($link) <= 0 ) {
			return null;
		} else {
			return $link;
		}
	}

	/**
	 * Set the content
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
		if (array_key_exists ( $key, $this->custom )) {
			return @$this->custom [$key];
		} else {
			return null;
		}
	}

	/**
	 * Get an array of stats
	 * @return array
	 */
	public function getStats() {
		return $this->stats;
	}
	
	/**
	 * Append a new stat
	 * @param string $value
	 * @return X_Page_Item_Statistic
	 */
	public function appendStat($value) {
		$this->stats[] = $value;
		return $this;
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
		$this->custom [$key] = $value;
		return $this;
	}

}

