<?php 

class Application_Model_JDownloaderPackage extends Application_Model_Abstract {
	
	protected $name;
	protected $eta;
	protected $size;
	protected $percent;
	protected $downloading;
	
	protected $_files = array();
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @return string
	 */
	public function getETA() {
		return $this->eta;
	}
	
	/**
	 * @return string
	 */
	public function getSize() {
		return $this->size;
	}
	
	/**
	 * @return string
	 */
	public function getPercent() {
		return $this->percent;
	}
	
	/**
	 * @return boolean
	 */
	public function isDownloading() {
		return (bool) $this->downloading;
	}

	public function getFilesCount() {
		return count($this->_files);
	}
	
	/**
	 * @param string
	 * @return Application_Model_JDownloaderPackage
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * @param string
	 * @return Application_Model_JDownloaderPackage
	 */
	public function setETA($eta) {
		$this->eta = $eta;
		return $this;
	}
	
	/**
	 * @param string
	 * @return Application_Model_JDownloaderPackage
	 */
	public function setSize($size) {
		$this->size = $size;
		return $this;
	}
	
	/**
	 * @param string
	 * @return Application_Model_JDownloaderPackage
	 */
	public function setPercent($percent) {
		$this->percent = $percent;
		return $this;
	}
	
	/**
	 * @param string
	 * @return Application_Model_JDownloaderPackage
	 */
	public function setDownloading($downloading) {
		$this->downloading = (bool) $downloading;
		return $this;
	}
	
	public function appendFile(Application_Model_JDownloaderFile $file) {
		$this->_files[] = $file;
	}
	
	public function getFiles() {
		return $this->_files;
	}
	
}

