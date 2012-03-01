<?php 


class X_RtmpDumpWeebTv {
	
	static private $instance = null;


	protected $params = array(
		'rtmp' => null,
		'host' => null,
		'port' => null,
		'socks' => null,
		'protocol' => null,
		'playpath' => null,
		'swfUrl' => null,
		'tcUrl' => null,
		'pageUrl' => null,
		'app' => null,
		'swfhash' => null,
		'swfsize' => null,
		'swfVfy' => null,
		'swfAge' => null,
		'auth' => null,
		'conn' => null,
		'flashVer' => null,
		'live' => null,
		'subscribe' => null,
		'flv' => null,
		'resume' => null,
		'timeout' => null,
		'start' => null,
		'stop' => null,
		'token' => null,
		'hashes' => null,
		'buffer' => null,
		'skip' => null,
		'quiet' => null,
		'verbose' => null,
		'debug' => null,
		'sport' => null,
		'weeb' => null,
		'jtv' => null
	);
	
	static protected $longToShort = array(
		'rtmp' => 'r',
		'host' => 'n',
		'port' => 'c',
		'socks' => 'S',
		'protocol' => 'l',
		'playpath' => 'y',
		'swfUrl' => 's',
		'tcUrl' => 't',
		'pageUrl' => 'p',
		'app' => 'a',
		'swfhash' => 'w',
		'swfsize' => 'x',
		'swfVfy' => 'W',
		'swfAge' =>	'X',
		'auth' => 'u',
		'conn' => 'C',
		'flashVer' => 'f',
		'live' => 'v',
		'subscribe' => 'd',
		'flv' => 'm',
		'timeout' => 'm',
		'start' => 'A',
		'stop' => 'B',
		'token' => 'T',
		'buffer' => 'b',
		'quiet' => 'q',
		'weeb' => 'J',
		'jtv' => 'j'			
	);
	
	protected $path = null;
	
	protected $allowPathOverride = false;
	
	/**
	 * @return X_RtmpDumpWeebTv
	 */
	public static function getInstance() {
		if ( self::$instance === null ) {
			self::$instance = new X_RtmpDumpWeebTv();
		}
		return self::$instance;
	}
	
	protected function __construct() {}
	
	/**
	 * Set an args for rtmpdump call
	 * Use rtmpdump --help for more information
	 * about params
	 * @return X_RtmpDumpWeebTv
	 */
	public function setParam($key, $value, $needQuotes = false) {
		if ( array_key_exists($key, $this->params)) {
			$this->params[$key] = ($needQuotes ? "\"$value\"" : $value);
			return $this;
		} else {
			throw new Exception("Unsupported param {{$key}}");
		}
	}
	
	public function getParam($key) {
		if ( array_key_exists($key, $this->params)) {
			return $this->params[$key];
		} else {
			throw new Exception("Unsupported param {{$key}}");
		}
	}
	
	public function setPath($executablePath) {
		$this->path = '"'. trim($executablePath, '"') . '"';
	}
	
	public function __toString() {
		
		if ( $this->path === null ) {
			return "";
		}
		
		$calls = array($this->path);
		foreach ($this->params as $key => $value) {
			if ( $value !== null ) {
				// for $value = true, $key is a flag only
				// for example for debug, quiet, etc..
				if ( is_bool($value) ) {
					if ( $value) {
						$calls[] = "--$key";
					}
				} elseif ($key == 'weeb') {
					$calls[] = "--$key=$value";
				} else {
					$calls[] = "--$key $value";
				}
			}
		}
		/*
		if ( !X_Env::isWindows()) {
			$calls[] = '> /dev/null 2>&1';
		}
		*/
		
		//$calls[] = '-v -z -V 1>&2';
		// send to stdout
		$calls[] = '-q -o -';
		
		return implode(' ', $calls);
	}
	
	/**
	 * @return X_RtmpDumpWeebTv
	 */
	public function parseUri($uri) {
		// reset the params
		foreach ($this->params as $key => $value) {
			$this->params[$key] = null;
		}
		
		$parsed = parse_url($uri);
		
		if ( $parsed === false || $parsed['scheme'] != 'rtmpdump-weebtv' ) {
			throw new Exception("Invalid URI format", 0);
		}
		
		if ( $this->allowPathOverride && $parsed['host'] != 'stream' ) {
			$path = urldecode($parsed['host']);
			if ( file_exists($path) ) {
				$this->setPath($path);
			}
		}
		
		$params = array();
		parse_str($parsed['query'], $params);
		
		foreach ($params as $key => $value ) {
			$value = urldecode($value);
			try {
				$method = "set".ucfirst($key);
				if ( $method != 'setOptions' && method_exists($this, $method) ) {
					$this->$method($value);
				} else {
					if ( $value == 'true' || $value == '' ) {
						$this->setParam($key, true);
					} else {
						$this->setParam($key, $value, true);
					}
				}
			} catch (Exception $e ) {
				// invalid param, ignoring it
			}
		}
		
		return $this;
	}
	
	public static function buildUri($params = array(), $path = null) {
		$querystr = http_build_query($params);
		$path = $path === null ? 'stream' : urlencode($path);
		return "rtmpdump-weebtv://$path/?$querystr";
	}

	public static function buildHttpRequest($params = array(), $path = null) {
		$shortedParams = array();
		foreach ($params as $key => $value) {
			if ( isset(self::$longToShort[$key]) ) {
				$shortedParams[self::$longToShort[$key]] = $value;
			}
		}
		$querystr = http_build_query($shortedParams);
		return "?$querystr";
	}
	
	public static function getFakeUri($linkId) {
		return "rtmpdump-weebtv://$linkId";
	}
	// some usefull shortcuts, for other use setParam()
	
	/**
	 * @return X_RtmpDumpWeebTv
	 */
	public function setRtmp($value) {
		return $this->setParam('rtmp', $value, true);
	}
	
	/**
	 * @return X_RtmpDumpWeebTv
	 */
	public function setLive($isLive) {
		return $this->setParam('live', (bool) $isLive);
	}
	
	/**
	 * @return X_RtmpDumpWeebTv
	 */
	public function setQuiet($isQuiet) {
		return $this->setParam('quiet', (bool) $isQuiet);
	}
	
	/**
	 * @return X_RtmpDumpWeebTv
	 */
	public function setFlv($outputPath) {
		return $this->setParam('flv', $outputPath, true);
	}
	
	public function setStreamPort($port) {
		return $this->setParam('sport', $port, false);
	}
	
	/**
	 * @return X_RtmpDumpWeebTv
	 */
	public function setOptions($options) {
		
		if ( is_array($options) ) {
			$options = new Zend_Config($options);
		}
		
		if ( $options instanceof Zend_Config ) {
			$this->allowPathOverride = $options->get('override', false);
			$this->setPath($options->get('path', null));
		}
		
		return $this;
	}
	
	public function forceKill() {
		
		if ( !X_Env::isWindows() ) {
			X_Env::execute("killall rtmpdump-weebtv", X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
		} else {
			X_Env::execute("taskkill /IM rtmpdump-weebtv.exe /F", X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
		}
		
	}
}
