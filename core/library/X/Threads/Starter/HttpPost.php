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
		$http
			// auth info
			->setParameterPost('hash', md5('test') )
			->setParameterPost('key', 1)
			->setParameterPost('salt', 'test')
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
			return true;
		}
	}

	/* (non-PHPdoc)
	 * @see X_Threads_Starter::isValid()
	 */
	function isValid($keyId, $hash, $salt) {
		return true;
	}
	
	
}
