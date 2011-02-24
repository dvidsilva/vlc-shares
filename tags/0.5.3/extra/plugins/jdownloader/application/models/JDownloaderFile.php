<?php 

class Application_Model_JDownloaderFile extends Application_Model_Abstract {
	
	protected $name;
	protected $percent;
	protected $downloading;
	protected $hoster;
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @return string
	 */
	public function getHoster() {
		return $this->hoster;
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

	/**
	 * @param string
	 * @return Application_Model_JDownloaderFile
	 */
	public function setName($name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * @param string
	 * @return Application_Model_JDownloaderFile
	 */
	public function setHoster($hoster) {
		$this->hoster = $hoster;
		return $this;
	}
	
	/**
	 * @param string
	 * @return Application_Model_JDownloaderFile
	 */
	public function setPercent($percent) {
		$this->percent = $percent;
		return $this;
	}
	
	/**
	 * @param string
	 * @return Application_Model_JDownloaderFile
	 */
	public function setDownloading($downloading) {
		$this->downloading = (bool) $downloading;
		return $this;
	}	
	
	
}
