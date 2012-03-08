<?php

class Application_Model_Bookmark extends Application_Model_Abstract {
	
	const TYPE_HTML = 'html';
	const TYPE_RSS = 'rss';
	const TYPE_TEXT = 'text';
	
	protected $id = false;
	protected $type = self::TYPE_TEXT;
	protected $url;
	protected $cookies;
	protected $title;
	protected $description;
	protected $thumbnail;
	protected $ua;
	
	public function isNew() {
		return ($this->id === false);
	}
	
	/**
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return the $type
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return the $url
	 */
	public function getUrl() {
		return $this->url;
	}

	/**
	 * @return the $cookies
	 */
	public function getCookies() {
		return $this->cookies;
	}

	/**
	 * @return the $title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @return the $description
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @return the $thumbnail
	 */
	public function getThumbnail() {
		return $this->thumbnail;
	}

	/**
	 * @return the $ua
	 */
	public function getUa() {
		return $this->ua;
	}
	
	/**
	 * @param field_type $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @param string $type
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 * @param field_type $url
	 */
	public function setUrl($url) {
		$this->url = $url;
	}

	/**
	 * @param field_type $cookies
	 */
	public function setCookies($cookies) {
		$this->cookies = $cookies;
	}

	/**
	 * @param field_type $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * @param field_type $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * @param field_type $thumbnail
	 */
	public function setThumbnail($thumbnail) {
		$this->thumbnail = $thumbnail;
	}

	/**
	 * @param field_type $ua
	 */
	public function setUa($ua) {
		$this->ua = $ua;
	}
	
}
