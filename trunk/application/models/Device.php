<?php

class Application_Model_Device extends Application_Model_Abstract {

	
	/*
	id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
	label VARCHAR(255) NOT NULL,
	pattern TEXT NOT NULL,
	exact INTEGER(1) DEFAULT 0,
	idProfile INTEGER NULL DEFAULT NULL,
	idOutput INTEGER NULL DEFAULT NULL,
	guiClass VARCHAR(255) DEFAULT NULL,
	 */

	protected $id;
	protected $label;
	protected $pattern;
	protected $exact;
	protected $idProfile;
	protected $idOutput;
	protected $guiClass;
	protected $priority;
	
	/**
	 * @return the $id
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return the $key
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @return the $value
	 */
	public function getPattern() {
		return $this->pattern;
	}

	/**
	 * @return the $default
	 */
	public function isExact() {
		return $this->exact;
	}

	/**
	 * @return the $section
	 */
	public function getIdProfile() {
		return $this->idProfile;
	}

	/**
	 * @return the $label
	 */
	public function getIdOutput() {
		return $this->idOutput;
	}

	/**
	 * @return the $description
	 */
	public function getGuiClass() {
		return $this->guiClass;
	}

	public function getPriority() {
		return $this->priority;
	}
	
	/**
	 * @param $id the $id to set
	 * @return Application_Model_Device
	 */
	public function setId($id) {
		$this->id = $id;
		return $this;
	}

	/**
	 * @param $key the $key to set
	 * @return Application_Model_Device
	 */
	public function setPattern($key) {
		$this->pattern = $key;
		return $this;
	}

	/**
	 * @param $value the $value to set
	 * @return Application_Model_Device
	 */
	public function setExact($value) {
		$this->exact = (bool) $value;
		return $this;
	}

	/**
	 * @param $default the $default to set
	 * @return Application_Model_Device
	 */
	public function setIdProfile($default) {
		$this->idProfile = $default;
		return $this;
	}

	/**
	 * @param $section the $section to set
	 * @return Application_Model_Device
	 */
	public function setIdOutput($section) {
		$this->idOutput = $section;
		return $this;
	}

	/**
	 * @param $label the $label to set
	 * @return Application_Model_Device
	 */
	public function setLabel($label) {
		$this->label = $label;
		return $this;
	}

	/**
	 * @param $description the $description to set
	 * @return Application_Model_Device
	 */
	public function setGuiClass($description) {
		$this->guiClass = $description;
		return $this;
	}

	public function setPriority($priority) {
		$this->priority = $priority;
		return $this;
	}
	
	
}

