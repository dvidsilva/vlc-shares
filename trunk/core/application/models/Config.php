<?php

class Application_Model_Config extends Application_Model_Abstract {

	const TYPE_TEXT = 0;
	const TYPE_SELECT = 1;
	const TYPE_TEXTAREA = 2;
	const TYPE_BOOLEAN = 3;
	const TYPE_FILE = 4;
	const TYPE_RADIO = 5;
	
	/*
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	key VARCHAR(255) NOT NULL UNIQUE,
	value TEXT DEFAULT NULL,
	default TEXT DEFAULT NULL,
	section VARCHAR(255) NOT NULL DEFAULT "general",
	label VARCHAR(255) DEFAULT NULL,
	description VARCHAR(255) DEFAULT NULL,
	type INTEGER NOT NULL DEFAULT 0
	 */

	private $id;
	private $key;
	private $value;
	private $default;
	private $section;
	private $label;
	private $description;
	private $type;
	private $class;
	
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
	 * @return the $value
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * @return the $default
	 */
	public function getDefault() {
		return $this->default;
	}

	/**
	 * @return the $section
	 */
	public function getSection() {
		return $this->section;
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
	 * @param $id the $id to set
	 * @return Application_Model_Config
	 */
	public function setId($id) {
		$this->id = $id;
		return $this;
	}

	/**
	 * @param $key the $key to set
	 * @return Application_Model_Config
	 */
	public function setKey($key) {
		$this->key = $key;
		return $this;
	}

	/**
	 * @param $value the $value to set
	 * @return Application_Model_Config
	 */
	public function setValue($value) {
		$this->value = $value;
		return $this;
	}

	/**
	 * @param $default the $default to set
	 * @return Application_Model_Config
	 */
	public function setDefault($default) {
		$this->default = $default;
		return $this;
	}

	/**
	 * @param $section the $section to set
	 * @return Application_Model_Config
	 */
	public function setSection($section) {
		$this->section = $section;
		return $this;
	}

	/**
	 * @param $label the $label to set
	 * @return Application_Model_Config
	 */
	public function setLabel($label) {
		$this->label = $label;
		return $this;
	}

	/**
	 * @param $description the $description to set
	 * @return Application_Model_Config
	 */
	public function setDescription($description) {
		$this->description = $description;
		return $this;
	}

	/**
	 * @param $type the $type to set
	 * @return Application_Model_Config
	 */
	public function setType($type) {
		$this->type = $type;
		return $this;
	}

	/**
	 * Return class of config
	 * @return string
	 */
	public function getClass() {
		return $this->class;
	}
	
	/**
	 * @param $class the $class to set
	 * @return Application_Model_Config
	 */
	public function setClass($class) {
		$this->class = $class;
		return $this;
	}
	
}

