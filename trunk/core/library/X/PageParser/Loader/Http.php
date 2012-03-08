<?php 

class X_PageParser_Loader_Http implements X_PageParser_Loader {

	/**
	 * 
	 * @var Zend_Http_Client
	 */
	private $http = null;

	private $last_request_uri = null;
	
	public function __construct() {}
	
	/* (non-PHPdoc)
	 * @see X_PageParser_Loader::getPage()
	 */
	public function getPage($uri) {
		// http hold all info about the last request and last response object as well as all options
		if ( $this->last_request_uri != $uri ) {
			$this->last_request_uri = $uri;
			X_Debug::i("Fetching: $uri");
			$this->getHttpClient()->setUri($uri)->request();
			//X_Debug::i("Request: \n".$this->getHttpClient()->getLastRequest());
		}
		return $this->getHttpClient()->getLastResponse()->getBody();
	}
	
	public function getHttpClient() {
		if ( $this->http === null ) {
			$this->http = new Zend_Http_Client();
			$this->http->setHeaders('User-Agent', 'vlc-shares/'.X_VlcShares::VERSION. 'X_PageParser_Loader_Http/0.1');
		}
		return $this->http;
	}
	
}

