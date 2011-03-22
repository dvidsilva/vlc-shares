<?php

class Application_Model_AuthAccount extends Application_Model_Abstract {

	protected $id;
	protected $username;
	protected $password;
	protected $passphrase;
	protected $enabled;
	protected $altAllowed;
	

	/**
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
	}
	
	
	/**
	 * @return the $username
	 */
	public function getUsername() {
		return $this->username;
	}

	/**
	 * @return the $password
	 */
	public function getPassword() {
		return $this->password;
	}

	/**
	 * @return the $passphrase
	 */
	public function getPassphrase() {
		return $this->passphrase;
	}
	
	
	/**
	 * @return the $enabled
	 */
	public function isEnabled() {
		return (bool) $this->enabled;
	}

	/**
	 * @return the $altAllowed
	 */
	public function isAltAllowed() {
		return (bool) $this->altAllowed;
	}

	
	/**
	 * @param $username the $username to set
	 * @return Application_Model_AuthAccount
	 */
	public function setUsername($username) {
		$this->username = $username;
		return $this;
	}

	/**
	 * @param $id the $id to set
	 * @return Application_Model_AuthAccount
	 */
	public function setId($id) {
		$this->id = $id;
		return $this;
	}

	/**
	 * @param $password the $password to set
	 * @return Application_Model_AuthAccount
	 */
	public function setPassword($password) {
		$this->password = $password;
		return $this;
	}

	/**
	 * @param $passphrase the $passphrase to set
	 * @return Application_Model_AuthAccount
	 */
	public function setPassphrase($passphrase) {
		$this->passphrase = $passphrase;
		return $this;
	}
	
	
	/**
	 * @param $enabled the $enabled to set
	 * @return Application_Model_AuthAccount
	 */
	public function setEnabled($enabled) {
		$this->enabled = (bool) $enabled;
		return $this;
	}

	/**
	 * @param $altAllowed the $altAllowed to set
	 * @return Application_Model_AuthAccount
	 */
	public function setAltAllowed($altAllowed) {
		$this->altAllowed = (bool) $altAllowed;
		return $this;
	}
	
}

