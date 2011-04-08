<?php

class Application_Model_Cache extends Application_Model_Abstract {

	protected $uri;
	protected $content;
	protected $cType = 0;
	/**
	 * @var boolean
	 */
	protected $new;
	/**
	 * @var integer
	 */
	protected $validity;
	
	function __construct() {
		$this->new = true;
		$this->validity = time();
	}
	
	/**
	 * @return the $uri
	 */
	public function getUri() {
		return $this->uri;
	}

	/**
	 * @return the $content
	 */
	public function getContent() {
		return $this->content;
	}

	/**
	 * @return the $value
	 */
	public function getCType() {
		return $this->cType;
	}

	/**
	 * @return the $created
	 */
	public function getValidity() {
		return $this->validity;
	}

	/**
	 * @return the $new
	 */
	public function isNew() {
		return (bool) $this->new;
	}

	/**
	 * Check if entry is valid
	 * @return boolean
	 */
	public function isValid($timeLimit) {
		//X_Debug::i("Check validity: isNew {$this->isNew()} - Created: {$this->getCreated()} - Limit: $timeLimit");
		return ( !$this->isNew() && $this->getValidity() > $timeLimit );
	}

	/**
	 * @param $uri the $uri to set
	 * @return Application_Model_Cache
	 */
	public function setUri($uri) {
		$this->uri = $uri;
		return $this;
	}

	/**
	 * @param $key the $key to set
	 * @return Application_Model_Cache
	 */
	public function setContent($content) {
		$this->content = $content;
		return $this;
	}

	/**
	 * @param $value the $value to set
	 * @return Application_Model_Cache
	 */
	public function setCType($ctype) {
		$this->ctype = $ctype;
		return $this;
	}

	/**
	 * @param $default the $default to set
	 * @return Application_Model_Cache
	 */
	public function setNew($new) {
		$this->new = $new;
		return $this;
	}

	/**
	 * @param $section the $section to set
	 * @return Application_Model_Cache
	 */
	public function setValidity($created) {
		$this->validity = $created;
		return $this;
	}
	
}

