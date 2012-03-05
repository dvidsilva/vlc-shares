<?php 


class X_Threads_Starter_HttpPost extends X_Threads_Starter {

	protected $url = "";
	
	function __construct($providerUrl = '') {
		
		$this->url = $providerUrl;
		
	}
	
	/* (non-PHPdoc)
	 * @see X_Threads_Starter::spawn()
	 */
	function spawn($threadId) {
		
		$http = new Zend_Http_Client($this->url, array(
			'timeout' => 1,
			'keepalive' => false
		));

		$i = 20;
		do {
			if ( $i-- < 0 ) throw new Exception("To many hash generation failed");
			$salt = rand(1, 1000);
			$key = time();
			$privKey = rand(100000, 999999);
			$hash = md5("{$salt}{$privKey}{$key}");
		} while ( !$this->storeRequestKey($hash, $privKey) ); 
		
		$http
			// auth info
			->setParameterPost('hash', $hash )
			->setParameterPost('key', $key)
			->setParameterPost('salt', $salt)
			// thread info
			->setParameterPost('thread', $threadId )
			;
		
		try {

			$body = $http->request(Zend_Http_Client::POST)->getBody();
			
			$response = @Zend_Json::decode($body);
			
			if ( $response ) {
				return @$response['success'];
			}
		
		} catch (Exception $e) {
			// timeout
			// don't know, assume true
			X_Debug::w("Request timeout");
			return true;
		}
	}

	function storeRequestKey($hash, $privKey) {
		// set a short time validity. Only 1 minute
		try {
			X_VlcShares_Plugins_Cache::forcedStoreItem("threads-requestkey::{$hash}", $privKey, 1);
			return true;
		} catch (Exception $e) {
			return false;
		}
	}
	
	/* (non-PHPdoc)
	 * @see X_Threads_Starter::isValid()
	 */
	function isValid($key, $hash, $salt) {
		try {
			$privKey = X_VlcShares_Plugins_Cache::forcedRetrieveItem("threads-requestkey::{$hash}");
			return  (md5("{$salt}{$privKey}{$key}") === $hash);
		} catch (Exception $e) {
			// no cache entry? invalid for sure...
			return false;
		}
	}
}
