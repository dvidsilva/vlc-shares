<?php

class Application_Model_Plugin extends Application_Model_Abstract {
	/*
	CREATE TABLE plugins (
		id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
		`key` VARCHAR(255) NOT NULL UNIQUE,
		class VARCHAR(255) NOT NULL,
		file VARCHAR(255) DEFAULT NULL,
		label VARCHAR(255) DEFAULT NULL,
		description VARCHAR(255) DEFAULT NULL,
		`type` INTEGER NOT NULL DEFAULT 0,
		version VARCHAR(16) DEFAULT NULL
	);
	*/
	
	const SYSTEM = 0;
	const DISTRIBUTION = 1;
	const USER = 2;
	
	private $id;
	private $key;
	private $class;
	private $file;
	private $label;
	private $description;
	private $type;
	private $enabled;
	private $version;
	
	/**
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return the $key
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * @return the $class
	 */
	public function getClass() {
		return $this->class;
	}

	/**
	 * @return the $file
	 */
	public function getFile() {
		return $this->file;
	}

	/**
	 * @return the $label
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @return the $description
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @return the $type
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return the $enabled
	 */
	public function isEnabled() {
		return $this->enabled;
	}
	
	/**
	 * @return the $version
	 */
	public function getVersion() {
		return $this->version;
	}
	
	/**
	 * @param $id the $id to set
	 * @return class
	 */
	public function setId($id) {
		$this->id = $id;
		return $this;
	}

	/**
	 * @param $key the $key to set
	 * @return Application_Model_Plugin
	 */
	public function setKey($key) {
		$this->key = $key;
		return $this;
	}

	/**
	 * @param $class the $class to set
	 * @return Application_Model_Plugin
	 */
	public function setClass($class) {
		$this->class = $class;
		return $this;
	}

	/**
	 * @param $file the $file to set
	 * @return Application_Model_Plugin
	 */
	public function setFile($file) {
		$this->file = $file;
		return $this;
	}

	/**
	 * @param $label the $label to set
	 * @return Application_Model_Plugin
	 */
	public function setLabel($label) {
		$this->label = $label;
		return $this;
	}

	/**
	 * @param $description the $description to set
	 * @return Application_Model_Plugin
	 */
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}

	/**
	 * @param $type the $type to set
	 * @return Application_Model_Plugin
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 * @param $enabled the $enabled to set
	 * @return Application_Model_Plugin
	 */
	public function setEnabled($enabled) {
		$this->enabled = (bool) $enabled;
		return $this;
	}

	/**
	 * @param $version the $version to set
	 * @return Application_Model_Plugin
	 */
	public function setVersion($version) {
		$this->version = (string) $version;
		return $this;
	}
	
	
}

