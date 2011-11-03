<?php

class Application_Model_Video {
	protected $_idVideo;
	protected $_title;
	protected $_description;
	protected $_category;
	protected $_id;
	protected $_hoster;
	protected $_thumbnail;
	
	public function __construct(array $options = null) {
		if (is_array ( $options )) {
			$this->setOptions ( $options );
		}
	}
	
	public function __set($name, $value) {
		$method = 'set' . $name;
		if (('mapper' == $name) || ! method_exists ( $this, $method )) {
			throw new Exception ( "Invalid video property {{$name}}" );
		}
		$this->$method ( $value );
	}
	
	public function __get($name) {
		$method = 'get' . $name;
		if (('mapper' == $name) || ! method_exists ( $this, $method )) {
			throw new Exception ( "Invalid video property {{$name}}" );
		}
		return $this->$method ();
	}
	
	public function setOptions(array $options) {
		$methods = get_class_methods ( $this );
		foreach ( $options as $key => $value ) {
			$method = 'set' . ucfirst ( $key );
			if (in_array ( $method, $methods )) {
				$this->$method ( $value );
			}
		}
		return $this;
	}
	
	/**
	 * @return Application_Model_Video
	 */
	public function setTitle($text) {
		$this->_title = ( string ) $text;
		return $this;
	}
	
	public function getTitle() {
		return $this->_title;
	}
	
	/**
	 * @return Application_Model_Video
	 */
	public function setDescription($email) {
		$this->_description = ( string ) $email;
		return $this;
	}
	
	public function getDescription() {
		return $this->_description;
	}
	
	/**
	 * @return Application_Model_Video
	 */
	public function setIdVideo($ts) {
		$this->_idVideo = $ts;
		return $this;
	}
	
	public function getIdVideo() {
		return $this->_idVideo;
	}
	
	/**
	 * @return Application_Model_Video
	 */
	public function setCategory($ts) {
		$this->_category = $ts;
		return $this;
	}
	
	public function getCategory() {
		return $this->_category;
	}
	
	/**
	 * @return Application_Model_Video
	 */
	public function setHoster($ts) {
		$this->_hoster = $ts;
		return $this;
	}
	
	public function getHoster() {
		return $this->_hoster;
	}
	
	/**
	 * @return Application_Model_Video
	 */
	public function setThumbnail($ts) {
		$this->_thumbnail = $ts;
		return $this;
	}
	
	public function getThumbnail() {
		return $this->_thumbnail;
	}
	
	/**
	 * @return Application_Model_Video
	 */
	public function setId($id) {
		$this->_id = ( int ) $id;
		return $this;
	}
	
	public function getId() {
		return $this->_id;
	}
}
