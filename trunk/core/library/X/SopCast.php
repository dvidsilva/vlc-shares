<?php 


class X_SopCast {
	
	/**
	 * @var X_SopCast
	 */
	static private $instance = null;

	protected $uri = null;
	
	protected $path = null;
	
	/**
	 * @return X_SopCast
	 */
	public static function getInstance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	protected function __construct() {}
	
	public function setPath($executablePath) {
		$this->path = '"'. trim($executablePath, '"') . '"';
	}
	
	public function __toString() {
		
		if ( $this->path === null ) {
			return "";
		}
		
		$calls = array($this->path);
		
		$calls[] = "\"{$this->getUri()}\"";
		
		if ( !X_Env::isWindows()) {
			// internal port external port
			$calls[] = '3809 8902';
		}
		
		return implode(' ', $calls);
	}
	

	/**
	 * @return X_SopCast
	 */
	public function setUri($value) {
		$this->uri = $value;
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getUri() {
		return $this->uri;
	}
	
	/**
	 * @return X_SopCast
	 */
	public function setOptions($options) {
		
		if ( is_array($options) ) {
			$options = new Zend_Config($options);
		}
		
		if ( $options instanceof Zend_Config ) {
			$this->setPath($options->get('path', null));
		}
		
		return $this;
	}
	
	public function forceKill() {
		
		if ( !X_Env::isWindows() ) {
			X_Env::execute("kill -9 `ps aux | grep {$this->path} | grep -v grep | awk '{print $2}'`", X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
		} else {
			X_Env::execute("taskkill /IM SopCast.exe /F", X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
		}
		
	}
}
