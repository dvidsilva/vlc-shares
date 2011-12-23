<?php 

class X_VlcShares_Plugins_Helper_Megaupload extends X_VlcShares_Plugins_Helper_Megavideo {

	private $upload_to_video_map = array();
	/*
	
	function __construct(Zend_Config $options = null) {
		parent::__construct($options);
	}
	*/
	
	/**
	 * Set source location for megaupload video url or id
	 * 
	 * @param $location Megaupload ID or URL
	 * @return X_VlcShares_Plugins_Helper_Megaupload
	 */
	function setMegauploadLocation($location) {
		
		preg_match ( '#\?d=(.+?)$#', $location, $id );
		$location = @$id [1] ? $id [1] : $location;
		
		if ( !array_key_exists($location, $this->upload_to_video_map) ) {
		
			$http = new Zend_Http_Client("http://www.megavideo.com/?d=$location");
			$response = $http->request();
			
			$body = $response->getBody();
			$matches = array();
			if ( preg_match('/flashvars\.v\s*?=\s*?["\']([A-Z0-9]+)["\'];/', $body, $matches) ) {
				//return $this->setLocation($matches[1]);
				$megavideoId = $matches[1];
			} else {
				$megavideoId = $location;
			}
			
			$this->upload_to_video_map[$location] = $megavideoId;
			
		} else {
			$megavideoId = $this->upload_to_video_map[$location];
		}
		
		
		return $this->setLocation($megavideoId);
		
	}
	
}
