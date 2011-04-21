<?php

class Application_Model_FsThumb extends Application_Model_Abstract {

	protected $path;
	protected $url;
	protected $size;
	/**
	 * @var boolean
	 */
	protected $new;
	/**
	 * @var integer
	 */
	protected $created;
	
	function __construct() {
		$this->new = true;
		$this->created = time();
	}
	
	/**
	 * @return the $path
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * @return the $url
	 */
	public function getUrl() {
		return $this->url;
	}
	
	/**
	 * @return the $size
	 */
	public function getSize() {
		return $this->size;
	}
	
	/**
	 * @return the $created
	 */
	public function getCreated() {
		return $this->created;
	}

	/**
	 * @return the $new
	 */
	public function isNew() {
		return (bool) $this->new;
	}

	/**
	 * @param $path the $path to set
	 * @return Application_Model_FsThumb
	 */
	public function setPath($path) {
		$this->path = $path;
		return $this;
	}

	/**
	 * @param $key the $key to set
	 * @return Application_Model_FsThumb
	 */
	public function setUrl($url) {
		$this->url = $url;
		return $this;
	}


	/**
	 * @param $size the $size to set
	 * @return Application_Model_FsThumb
	 */
	public function setSize($size) {
		$this->size = $size;
		return $this;
	}
	
	
	/**
	 * @param $default the $default to set
	 * @return Application_Model_FsThumb
	 */
	public function setNew($new) {
		$this->new = $new;
		return $this;
	}

	/**
	 * @param $section the $section to set
	 * @return Application_Model_FsThumb
	 */
	public function setCreated($created) {
		$this->created = $created;
		return $this;
	}
	
}

