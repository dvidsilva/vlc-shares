<?php 

class X_VlcShares_Plugins_Helper_RealDebrid extends X_VlcShares_Plugins_Helper_Abstract {

	const API_URL_FETCH = "http://real-debrid.fr/ajax/deb.php?lang=en&link=%s&password=";
	const API_URL_LOGIN = "https://real-debrid.fr/ajax/login.php?user=%s&pass=%s";
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
	
	protected function fetch($url) {
		
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
			X_Debug::i("Authentication required");
			
			$http->setUri(sprintf(self::API_URL_LOGIN, $this->options->get('username'), $this->options->get('password')));
			$loginBody = $http->request()->getBody();
			
			if ( $loginBody != 'OK' ) {
				// invalid login info
				throw new Exception("Invalid Real-Debrid account");
			} else {
				X_Debug::i("Authentication performed, valid account");
				// login ok, store information in cache (try)
				try {
					/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
					$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
					
					$cks = $http->getCookieJar()->getAllCookies(Zend_Http_CookieJar::COOKIE_OBJECT);
					foreach ($cks as $i => $c) {
						/* @var $c Zend_Http_Cookie */
						$cks[$i] = array(
							'domain' => $c->getDomain(),
							'exp' => $c->getExpiryTime(),
							'name' => $c->getName(),
							'path' => $c->getPath(),
							'value' => $c->getValue()
						);
					} 
						
					// perform a new authentication every 7 days
					$cacheHelper->storeItem("realdebrid::cookies", serialize($cks), 7 * 24 * 60);
					
				} catch (Exception $e) {
					X_Debug::e("Real Debrid requires cache plugin, but it's disabled!!!");
				}
			}
			
		}
		
		$url = urlencode($url);
		$url = sprintf(self::API_URL_FETCH, $url);
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
		
		if ( trim($links) == 'error' ) {
			X_Debug::e("Link error!");
			return false;
		}
		
		$dom = new Zend_Dom_Query($links);
		
		$results = $dom->queryXpath('//a');
		
		$links = array();
		
		while ( $results->valid() ) {
			
			/* @var $current DOMElement */
			$current = $results->current();
			
			$href = (string) $current->getAttribute('href');
			
			if ( !preg_match('/https?:\/\/.+real-debrid\..*/', $href) ) {
				X_Debug::i("Invalid href: $href");
				$results->next();
				continue;
			}
			
			if ( X_Env::startWith($href, 'https://') ) {
				$href = str_replace('https://', 'http://', $href);
			}
			
			X_Debug::i("Valid href: $href");
			
			$links[] = $href;
			
			$results->next();
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