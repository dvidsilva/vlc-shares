<?php 

class X_VlcShares_Plugins_Helper_RealDebrid extends X_VlcShares_Plugins_Helper_Abstract {

	//const API_URL_FETCH = "http://real-debrid.fr/ajax/deb.php?lang=en&link=%s&password=";
	const API_URL_FETCH = "http://real-debrid.fr/ajax/unrestrict.php?link=%s&password=&remote=0&time=%s";
	const API_URL_LOGIN = "http://real-debrid.fr/ajax/login.php?user=%s&pass=%s&captcha_challenge=&captcha_answer=&time=%s";
	const API_URL_ACCOUNT = "http://real-debrid.fr/api/account.php";
	
	/**
	 * @var Zend_Config
	 */
	protected $options;
	
	function __construct($options = null) {
		
		if ( !is_array($options) ) {
			$options = array('username' => '', 'password' => '', 'cache' => 24 * 60);
		}
		$this->options = new Zend_Config($options);
	}
	
	private $cachedLocationsFallback = array();
	
	private $location = null;
	
	private $lastUrl = null;
	
	/**
	 * @param string $url
	 * @return X_VlcShares_Plugins_Helper_RealDebrid
	 */
	public function setLocation($url) {
		
		// cachedLocationsFallback for insession caching
		// cache plugin for global caching
		if ( !array_key_exists($url, $this->cachedLocationsFallback) ) {
			
			X_Debug::i("Setting new location: $url");
			
			// try to fetch info from cache plugin
			$locationInfo = false;
			try {
				/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
				$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
				$locationInfo = unserialize($cacheHelper->retrieveItem("realdebrid::$url"));
			} catch (Exception $e) {
				$locationInfo = $this->fetch($url);
				// unable to load
				if ( $locationInfo != false ) {
					try {
						/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
						$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
						$cacheHelper->storeItem("realdebrid::$url", serialize($locationInfo), $this->options->get('cache', 24*60));
					} catch (Exception $e) {
						// cache isn't enabled, only session cache available
					}
				}
			}
			
			$this->cachedLocationsFallback[$url] = $locationInfo;
		}
		
		$this->lastUrl = $url;
		$this->location = $this->cachedLocationsFallback[$url];
		
		return $this;
		
	}
	
	public function isValid() {
		if ( $this->location != false 
				&& $this->location != null 
				&& is_array($this->location) 
				&& count($this->location) > 0 
			) {
				return true;
		} else {
			return false;
		}
	}
	
	protected function fetch($url, $retry = true) {
		
		if ( $this->options->get('username', '') == '' || $this->options->get('password', '') == '' ) {
			X_Debug::e("Account missing");
			return false;
		}
		
		$http = new Zend_Http_Client();
		$http->setCookieJar(true);
		
		// load cookies from file
		try {
			/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
			$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
			$cookies = unserialize($cacheHelper->retrieveItem("realdebrid::cookies"));
			
			X_Debug::i("Using cached authentication");
			
			foreach ($cookies as $c) {
				$_c = new Zend_Http_Cookie($c['name'], $c['value'], $c['domain'], $c['exp'], $c['path']);
				$http->getCookieJar()->addCookie($_c);
			}
			
		} catch (Exception $e) {
			// no cache plugin or no authentication performed
			// perform a new authentication
			X_Debug::i("Authentication required: {$e->getMessage()}");
			
			try {
			
				$http->setUri(sprintf(self::API_URL_LOGIN, $this->options->get('username'), md5($this->options->get('password')), time()));
				$loginBody = Zend_Json::decode($http->request()->getBody());
			
			} catch (Exception $e) {
				if ( $retry ) {
					X_Debug::e("Login request failed, try again: {$e->getMessage()}");
					return $this->fetch($url, false);
				} else {
					X_Debug::e("Login request failed (2nd time): {$e->getMessage()}");
					throw $e;
				}
			}
			
			if ( $loginBody['error'] != 0 ) {
				// invalid login info
				throw new Exception("Invalid Real-Debrid account");
			} else {
				X_Debug::i("Authentication performed, valid account");
				// login ok, store information in cache (try)
				try {
					/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
					$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
					
					$cks = $http->getCookieJar()->getAllCookies(Zend_Http_CookieJar::COOKIE_OBJECT);
					$minValidity = 99999999999;
					foreach ($cks as $i => $c) {
						/* @var $c Zend_Http_Cookie */
						$expire = $c->getExpiryTime();
						if ( $expire != null && $expire < $minValidity ) {
							$minValidity = $expire;
						}
						$cks[$i] = array(
							'domain' => $c->getDomain(),
							'exp' => $c->getExpiryTime(),
							'name' => $c->getName(),
							'path' => $c->getPath(),
							'value' => $c->getValue()
						);
					}

					$minValidity = (int) ($minValidity - time() / 60) - 1;
					if ( $minValidity < 0 ) $minValidity = 5; // if error, set to 5 minutes
					
					// perform a new authentication every 7 days
					$cacheHelper->storeItem("realdebrid::cookies", serialize($cks), $minValidity);
					
				} catch (Exception $e) {
					X_Debug::e("Real Debrid requires cache plugin, but it's disabled!!!");
				}
			}
			
		}
		
		$url = urlencode($url);
		$url = sprintf(self::API_URL_FETCH, $url, time());
		X_Debug::i("Fetching: $url");
		
		$http->setUri($url)->setHeaders(array(
			'User-Agent: vlc-shares/'.X_VlcShares::VERSION.' realdebrid/'.X_VlcShares_Plugins_RealDebrid::VERSION,
		));
		
		// always add "showlink=1" and "lang=en" in the cookies
		//$http
			//->setCookie('showlink', '1')
			//->setCookie('lang','en');
		
		$links = $http->request()->getBody();
		
		//X_Debug::i("Request: ".var_export( $http->getLastRequest() , true));
		//X_Debug::i("Real debrid response: ".$links);
		
		$json = Zend_Json::decode($links);
		
		if ( isset($json['error']) ) { 
			
			switch ( $json['error'] ) {
				case '0': // everything ok
					break;
				case '2': // invalid login
					// invalid account or login missing.
					// Try 1 more time only forcing relogin
					if ( $retry ) {
						return $this->fetch($url, false);
					} else {
						return false;
					}
				case '5': // expired account
					return false;
				default: // maybe invalid links?
					return false;
			
			}
			
		}
		
		
		$links = array();
		
		/*
			Minecraft MindCrack - S3E266 - Revisiting Arkas.mp4 (720p)|-|http:\/\/s08.real-debrid.com\/dl\/94i41374r203339931\/Minecraft%20MindCrack%20-%20S3E266%20-%20Revisiting%20Arkas.mp4|--|Minecraft MindCrack - S3E266 - Revisiting Arkas.flv (HQ)|-|http:\/\/s08.real-debrid.com\/dl\/94i41374r203434188\/Minecraft%20MindCrack%20-%20S3E266%20-%20Revisiting%20Arkas.flv|--|Minecraft MindCrack - S3E266 - Revisiting Arkas.flv (SD)|-|http:\/\/s08.real-debrid.com\/dl\/94i41374r203537640\/Minecraft%20MindCrack%20-%20S3E266%20-%20Revisiting%20Arkas.flv|--|Minecraft MindCrack - S3E266 - Revisiting Arkas.flv (240p)|-|http:\/\/s08.real-debrid.com\/dl\/94i41374r203631597\/Minecraft%20MindCrack%20-%20S3E266%20-%20Revisiting%20Arkas.flv"
		 */
		
		$linksS = $json['generated_links'];
		
		$xLinksS = explode('|--|', $linksS);
		
		foreach ( $xLinksS as $xLinkS ) {
			
			// $xLinkS = Minecraft MindCrack - S3E266 - Revisiting Arkas.mp4 (720p)|-|http:\/\/s08.real-debrid.com\/dl\/94i41374r203339931\/Minecraft%20MindCrack%20-%20S3E266%20-%20Revisiting%20Arkas.mp4
			
			$xLink = explode('|-|', $xLinkS);
			
			$links[] = $xLink[1];
			
		}
		
		if ( count($links) > 1 ) {
			$links = array_reverse($links, false);
		}
		
		return $links;
		
	}
	
	public function getUrl() {
		if ( !$this->isValid() ) {
			throw new Exception("Invalid location");
		} else {
			// Choose here the type of stream
			$streamType = Zend_Controller_Front::getInstance()->getRequest()->getParam('realdebrid', false);
			X_Debug::i("Stream Type: ". (int) $streamType);
			if ( $streamType == false || !array_key_exists((int) $streamType, $this->location) ) {
				return reset($this->location);
			} else {
				return $this->location[(int)$streamType];
			}
		}
	}
	
	public function getUrls() {
		if ( !$this->isValid() ) {
			throw new Exception("Invalid location");
		} else {
			return $this->location;
		}
	}
	
	public function cleanCurrentCacheEntry() {
		if ( $this->lastUrl == null ) {
			X_Debug::e("No location available");
		}
		try {
			/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
			$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
			$cacheHelper->storeItem("realdebrid::{$this->lastUrl}", '', 0);
		} catch (Exception $e) {
			X_Debug::w("Cache disabled, clean is useless");
		}
	}
	
}