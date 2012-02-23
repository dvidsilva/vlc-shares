<?php 
/**
 * This file is part of the vlc-shares project by Francesco Capozzo (ximarx) <ximarx@gmail.com>
 *
 * @author: Francesco Capozzo (ximarx) <ximarx@gmail.com>
 *
 * vlc-shares is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * vlc-shares is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with vlc-shares.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class X_VlcShares_Plugins_Helper_Hoster_RapidShare implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'rapidshare';
	// works with multiline, but is useless for vlc-shares and more complicated (converted from jdownloader)
	//const PATTERN = '/https?:\/\/[\w\.]*?rapidshare\.com\/(files\/(?P<ID1>\d+\/[^\"\r\n ]+)|\#\!download\|\d.*?\|(?P<ID2a>[^\|]+)\|(?P<ID2b>[^\|]+))/';
	// easier pattern
	const PATTERN = '/https?:\/\/[\w\.]*?rapidshare\.com\/(files\/|\#\!download\|[^\|]+\|)(?P<IDa>\d+)(\/|\|)(?P<IDb>[^\|]+)/i';
	
	const API_URL_HTTPS = 'https://api.rapidshare.com/cgi-bin/rsapi.cgi?sub=%s&%s';
	const API_URL_HTTP = 'http://api.rapidshare.com/cgi-bin/rsapi.cgi?sub=%s&%s';
	
	const DOWNLOAD_URL_FREE = 'http://%s/cgi-bin/rsapi.cgi?sub=download&fileid=%s&filename=%s&dlauth=%s';
	const DOWNLOAD_URL_PREMIUM = 'https://%s/cgi-bin/rsapi.cgi?sub=download&fileid=%s&filename=%s&cookie=%s';
	
	const LOGIN_URL_HTTPS = 'https://api.rapidshare.com/cgi-bin/rsapi.cgi?sub=getaccountdetails&login=%s&password=%s&withcookie=1';
	
	const PATTERN_DLTOKEN = '/^DL:(?P<host>[^,]+?),(?P<auth>[^,]+?),(?P<countdown>[^,]+?),(?P<md5>.*)$/';
	
	private $info_cache = array();
	
	private $options = array();
	
	public function __construct($configs = array()) {
		// extends default configs with values from constructor (if array) or ignore constructor params
		$this->options = array_merge(array(
				'premium' => false,
				'username' => '',
				'password' => '',
				'timeout' => 25
			), is_array($configs)? $configs : array());
		
	}
	
	/**
	 * give the hoster id
	 * @return string
	 */
	function getId() {
		return self::ID;
	}
	/**
	 * get the hoster pattern for regex match
	 * @return string
	 */
	function getPattern() {
		return self::PATTERN;
	}
	/**
	 * get the resource ID for the hoster
	 * from an $url
	 * @param string $url the hoster page
	 * @return string the resource id
	 */
	function getResourceId($url) {
		$matches = array();
		if ( preg_match(self::PATTERN, $url, $matches ) ) {
			if ( $matches['IDa'] != '' && $matches['IDb'] != '' ) {
				return "{$matches['IDa']}/{$matches['IDb']}";
			}
			X_Debug::e("No id found in {{$url}}");
			throw new Exception("No id found in {{$url}}", self::E_ID_NOTFOUND);
		} else {
			X_Debug::e("Regex failed {".preg_last_error()."}");
			throw new Exception("Regex failed {".preg_last_error()."}", self::E_URL_INVALID);
		}
	}
	/**
	 * get a playable resource url
	 * from an $url (or a resource id if $isId = true)
	 * @param string $url the hoster page or resource ID
	 * @param boolean $isId
	 * @return string a playable url
	 */
	function getPlayable($url, $isId = true) {
		if ( !$isId ) {
			$url = $this->getResourceId($url);
		}
		
		// check cached
		if ( isset($this->info_cache[$url]) && isset($this->info_cache[$url]['url']) ) {
			return $this->info_cache[$url]['url'];
		}
		if ( $this->options['premium'] && $this->options['username'] && $this->options['password'] ) {
			$url = $this->getPlayablePremium($url);
		} else {
			$url = $this->getPlayableFree($url);
		}
		// store in memory cache
		$this->info_cache[$url]['url'] = $url;
		
		return $url;
	}
	
	/**
	 * Get a playable url (pro) from the id
	 *
	 * @param string $id
	 * @param boolean $reLogin force a relogin (reset the stored cookie if any)
	 * @return string playable url (pro url)
	 * @throws X_Exceptions_Hoster_NoMoreQuota
	 * @throws X_Exceptions_Hoster_InvalidId
	 * @throws X_Exceptions_Hoster_TempUnavailableResource
	 * @throws X_Exceptions_Hoster_InvalidAccount
	 */
	protected function getPlayablePremium($id, $reLogin = false) {
	
		list($fileId, $fileName) = @explode('/', $id);
		if ( !$fileId || !$fileName ) {
			//throw new X_Exception_Hoster_InvalidId("Invalid id {{$id}}"); //TODO 0.5.5+ only
			X_Debug::e("Invalid id {{$id}}");
			throw new Exception("Invalid id {{$id}}", self::E_ID_INVALID);
		}
	
		$cookie = $this->getCookie($this->options['username'], $this->options['password'], $reLogin);
		
		$http = new Zend_Http_Client(sprintf(self::API_URL_HTTPS, 'download', "fileid={$fileId}&filename={$fileName}&try=1&cookie={$cookie}"));
		$this->prepareHttpClient($http);
		$text = $http->request('GET')->getBody();
	
		// check for api error response
		if ( X_Env::startWith($text, 'ERROR:') ) {
			// error found, get error message
			$matches = array();
			if ( !preg_match('/^ERROR: (?P<error>.*)$/m', $text, $matches) ) {
				// can't find the error msg. who cares...
				X_Debug::e("Invalid id {{$id}}");
				throw new Exception("Invalid id {{$id}}", self::E_ID_INVALID);
			} else {
				X_Debug::e("Invalid id {{$id}}: {$matches['error']}");
				throw new Exception("Invalid id {{$id}}: {$matches['error']}", self::E_ID_INVALID);
			}
		}
	
		// normal reponse "DL:$hostname,$dlauth,$countdown,$md5hex"
		$matches = array();
		if ( !preg_match(self::PATTERN_DLTOKEN, $text, $matches ) ) {
			X_Debug::e("DLTOKEN pattern failed {".preg_last_error()."}");
			throw new Exception("DLTOKEN pattern failed {".preg_last_error()."}", self::E_ID_INVALID);
		}
	
		$host = $matches['host'];
		$auth = $matches['auth'];
		$countdown = $matches['countdown'];
		// md5 useless
	
		// auth is 0 only if cookie is valid
		if ( $auth != '0' || $countdown > 0 ) {
			if ( $reLogin ) {
				// already tried to relogin, cookie is not valid
				//use free one
				
				$plugin = X_VlcShares_Plugins::broker()->getPlugins('rapidshare');
				if ( $plugin instanceof X_VlcShares_Plugins_RapidShare ) {
					if ( $countdown > 0 ) {
						$plugin->setCountDownMessage($countdown);
					}
					if ( $cookie ) {
						$plugin->setInvalidPremiumMessage(X_VlcShares_Plugins_RapidShare::MESSAGE_PREMIUM_EXPIRED);
					} else {
						$plugin->setInvalidPremiumMessage(X_VlcShares_Plugins_RapidShare::MESSAGE_LOGIN_INVALID);
					}
				}
				
				return sprintf(self::DOWNLOAD_URL_FREE, $host, $fileId, $fileName, $auth);
			} else {
				// recursion, try again forcing relogin
				return $this->getPlayablePremium($id, true);
			}
		}
		
		// nice, premium valid and i got a link
		
		return sprintf(self::DOWNLOAD_URL_PREMIUM, $host, $fileId, $fileName, $cookie);
	
	}
		
	protected function getCookie($username, $password, $reLogin = false) {
		
		$canCache = false;
		try {
			/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
			$cacheHelper = X_VlcShares_Plugins::helpers('cache');
			$canCache = true;
		} catch (Exception $e) {
			// cache plugin not available or no login
		}
		
		if ( $canCache && !$reLogin ) {
			// try to get cache value
			try {
				$cookie = $cacheHelper->retrieveItem('rapidshare::cookie');
				X_Debug::i("Using cookie stored in cache: ".substr($cookie, 0, 5)."...");
				return $cookie;
			} catch (Exception $e) {
				// no cookie in cache
				X_Debug::i("No cookie cached");
			}
		}

		// check for login flood flag
		if ( $reLogin && $canCache ) {
			// if i'm relogin, previous cookie was invalid
			try {
				$lastForcedCheck = $cacheHelper->retrieveItem('rapidshare::lastreloginflag');
				X_Debug::e("Anti login flood flag found. Ignoring relogin");
				return false;
			} catch (Exception $e) {
				// no previous fail, all right
				// setting relogin flag
				X_Debug::i("Setting reLogin flood flag");
				$cacheHelper->storeItem('rapidshare::lastreloginflag', true, 60); 
				// in 60 min, flag will be not valid anymore and relogin
				// will be possible one more time 
			}
		}
		
		
		// relogin is required (or because no value or because no cache or because relogin = true)
		$http = new Zend_Http_Client(sprintf(self::LOGIN_URL_HTTPS, $username, $password));
		$this->prepareHttpClient($http);
		
		$response = $http->request('GET')->getBody();

		$validity = 24 * 60; // 1 day (don't ask again)
		
		if ( X_Env::startWith($response, 'ERROR: ') ) {
			$cookie = false;
			X_Debug::e("Login values are not valid: {{$response}}");
		} else {
			$matches = array();
			if ( !preg_match('/^cookie=(?P<cookie>.*?)$/m', $response, $matches) ) {
				// cookie not found
				X_Debug::e("Cookie value not found in response {".preg_last_error()."}: \n$response");
				$cookie = false;
			} else {
				$cookie = $matches['cookie'];
			}
		}
		
		// store in cache
		if ( $canCache ) {
			$cacheHelper->storeItem('rapidshare::cookie', $cookie, $validity);
		}
		
		return $cookie;
		
	}
	
	
	/**
	 * Get a playable url (free) from the id
	 * 
	 * @param string $id
	 * @return string playable url (free url)
	 * @throws X_Exceptions_Hoster_NoMoreQuota
	 * @throws X_Exceptions_Hoster_InvalidId
	 * @throws X_Exceptions_Hoster_TempUnavailableResource
	 */
	protected function getPlayableFree($id) {
		
		list($fileId, $fileName) = @explode('/', $id);
		if ( !$fileId || !$fileName ) {
			//throw new X_Exception_Hoster_InvalidId("Invalid id {{$id}}"); //TODO 0.5.5+ only
			X_Debug::e("Invalid id {{$id}}");
			throw new Exception("Invalid id {{$id}}", self::E_ID_INVALID);
		}
		
		$http = new Zend_Http_Client(sprintf(self::API_URL_HTTP, 'download', "fileid={$fileId}&filename={$fileName}"));
		$this->prepareHttpClient($http);
		$text = $http->request('GET')->getBody();
		
		// check for api error response
		if ( X_Env::startWith($text, 'ERROR:') ) {
			// error found, get error message
			$matches = array();
			if ( !preg_match('/^ERROR: (?P<error>.*)$/m', $text, $matches) ) {
				// can't find the error msg. who cares...
				X_Debug::e("Invalid id {{$id}}");
				throw new Exception("Invalid id {{$id}}", self::E_ID_INVALID);
			} else {
				X_Debug::e("Invalid id {{$id}}: {$matches['error']}");
				throw new Exception("Invalid id {{$id}}: {$matches['error']}", self::E_ID_INVALID);
			}
		}
		
		// normal reponse "DL:$hostname,$dlauth,$countdown,$md5hex"
		$matches = array();
		if ( !preg_match(self::PATTERN_DLTOKEN, $text, $matches) ) {
			X_Debug::e("DLTOKEN pattern failed {".preg_last_error()."}");
			throw new Exception("DLTOKEN pattern failed {".preg_last_error()."}", self::E_ID_INVALID);
		}
		
		$host = $matches['host'];
		$auth = $matches['auth'];
		$countdown = $matches['countdown'];
		// md5 useless
		
		if ( $countdown > 0 ) {
			$plugin = X_VlcShares_Plugins::broker()->getPlugins('rapidshare');
			if ( $plugin instanceof X_VlcShares_Plugins_RapidShare ) {
				$plugin->setCountDownMessage($countdown);
			}
		}
		
		return sprintf(self::DOWNLOAD_URL_FREE, $host, $fileId, $fileName, $auth);
		
	}
	
	
	
	protected function prepareHttpClient(Zend_Http_Client $client) {
		
		$client->setHeaders(array(
			'User-Agent' => "vlc-shares/".X_VlcShares::VERSION." rapidshare/".X_VlcShares_Plugins_RapidShare::VERSION
		));
		$client->setConfig(array(
			'timeout'		=> $this->options['timeout']
		));
		
		
		
	}
	
	/**
	 * get an array with standard information about the playable
	 * @param string $url the hoster page or resource ID
	 * @param boolean $isId
	 * @return array format:
	 * 		array(
	 * 			'title' => TITLE
	 * 			'description' => DESCRIPTION
	 * 			'length' => LENGTH
	 * 			...
	 * 		)
	 */
	function getPlayableInfos($url, $isId = true) {
		
		if ( !$isId ) {
			$url = $this->getResourceId($url);
		}
		
		// use cached values
		if ( array_key_exists($url, $this->info_cache) ) {
			return $this->info_cache[$url];
		}
		
		list($fileId, $fileName) = @explode('/', $url);
		if ( !$fileId || !$fileName ) {
			//throw new X_Exception_Hoster_InvalidId("Invalid id {{$id}}"); //TODO 0.5.5+ only
			X_Debug::e("Invalid id {{$url}}");
			throw new Exception("Invalid id {{$url}}", self::E_ID_INVALID);
		}
		
		
		// use the api
		$http = new Zend_Http_Client(sprintf(self::API_URL_HTTP, 'checkfiles', "files={$fileId}&filenames={$fileName}" ));
		$this->prepareHttpClient($http);
		
		$text = $http->request()->getBody();

			// check for api error response
		if ( X_Env::startWith($text, 'ERROR:') ) {
			// error found, get error message
			$matches = array();
			if ( !preg_match('/^ERROR: (?P<error>.*)$/m', $text, $matches) ) {
				// can't find the error msg. who cares...
				X_Debug::e("Invalid id {{$url}}");
				throw new Exception("Invalid id {{$url}}", self::E_ID_INVALID);
			} else {
				X_Debug::e("Invalid id {{$url}}: {$matches['error']}");
				throw new Exception("Invalid id {{$url}}: {$matches['error']}", self::E_ID_INVALID);
			}
		}
		
		// normal reponse "fileid,filename,size,serverid,status,shorthost,md5\n"
		list(, , $size, , $status, ,) = explode(',', $text, 7); // md5 + garbage
		
		switch ($status) {
			case '1': // file OK, continue
				break;
			case '0': // file not found
				X_Debug::e("Invalid id {{$url}}: file not found");
				throw new Exception("Invalid id {{$url}}: file not found", self::E_ID_INVALID);
			case '3': // server down
				X_Debug::e("Resource unavailable {{$url}}: server is down");
				throw new Exception("Resource unavailable {{$url}}: server is down", 101); // 101 = resource not available in 0.5.5
			case '4': // illegal
				X_Debug::e("Invalid id {{$url}}: illegal resource");
				throw new Exception("Invalid id {{$url}}: illegal resource", 102); // 101 = resource illegal in 0.5.5
			default: // unknown status code
				X_Debug::e("Invalid id {{$url}}: unknown status code {{$status}}");
				throw new Exception("Invalid id {{$url}}: unknown status code {{$status}}", 999998); // 999998 = plugin obsolete in 0.5.5
		}
		
		$infos = array(
			'title' => $fileName,
			'size' => $size,
			'description' => '',
			'length' => 0
		);
				
		// add in cache
		$this->info_cache[$url] = $infos;
		
		return $infos;
		
	}
	
	function getHosterUrl($playableId) {
		return "http://www.rapidshare.com/files/$playableId";
	}
	
	
}
