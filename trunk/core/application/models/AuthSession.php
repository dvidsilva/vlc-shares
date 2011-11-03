<?php

class Application_Model_AuthSession extends Application_Model_Abstract {

	protected $ip;
	protected $userAgent;
	protected $username;
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
	 * @return the $ip
	 */
	public function getIp() {
		return $this->ip;
	}

	/**
	 * @return the $userAgent
	 */
	public function getUserAgent() {
		return $this->userAgent;
	}

	/**
	 * @return the $username
	 */
	public function getUsername() {
		return $this->username;
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
	 * Check if entry is valid
	 * @return boolean
	 */
	public function isValid($timeLimit) {
		//X_Debug::i("Check validity: isNew {$this->isNew()} - Created: {$this->getCreated()} - Limit: $timeLimit");
		return ( !$this->isNew() && $this->getCreated() > $timeLimit );
	}

	/**
	 * @param $ip the $ip to set
	 * @return Application_Model_AuthSession
	 */
	public function setIp($ip) {
		$this->ip = $ip;
		return $this;
	}

	/**
	 * @param $userAgent the $userAgent to set
	 * @return Application_Model_AuthSession
	 */
	public function setUserAgent($userAgent) {
		$this->userAgent = $userAgent;
		return $this;
	}

	/**
	 * @param $username the $username to set
	 * @return Application_Model_AuthSession
	 */
	public function setUsername($username) {
		$this->username = $username;
		return $this;
	}
	
	
	/**
	 * @param $new the $new to set
	 * @return Application_Model_AuthSession
	 */
	public function setNew($new) {
		$this->new = $new;
		return $this;
	}

	/**
	 * @param $created the $created to set
	 * @return Application_Model_AuthSession
	 */
	public function setCreated($created) {
		$this->created = $created;
		return $this;
	}
	
}

