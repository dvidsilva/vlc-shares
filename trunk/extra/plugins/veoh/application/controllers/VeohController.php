<?php 

class VeohController extends X_Controller_Action {
	
	
	
    public function init()
    {
        parent::init();
		if ( !X_VlcShares_Plugins::broker()->isRegistered('veoh') ) {
			throw new Exception(X_Env::_('err_pluginnotregistered') . ": veoh");
		}
    }
	
	public function videoAction() {
		
		$id = $this->getRequest()->getParam("id", false);
		if ( !$id ) {
			throw new Exception(X_Env::_("p_veoh_invalid_id"));
		}
		
		// this algorithm is taken from jdownload veoh hoster plugin
		
		$http1 = new Zend_Http_Client();
		$http1->setCookieJar(true);
		$http1->setUri("http://www.veoh.com/watch/$id");
		$response1 = $http1->request()->getBody();
		
		
		$fbsettingPattern = '/FB\.init\(\"(?P<fbsetting>[^\"]+)\"/';
		$fbsetting = array();
		if ( !preg_match($fbsettingPattern, $response1, $fbsetting) ) {
			X_Debug::e("Can't get FBSetting. Regex failed");
			throw new Exception("Can't get FBSetting");
		} 
		$fbsetting = $fbsetting['fbsetting'];
		
		$http2 = new Zend_Http_Client();
		$http2->setUri("http://www.veoh.com/static/swf/webplayer/VWPBeacon.swf?port=50246&version=1.2.2.1112");
		$response2 = $http2->request();
		
		$http1->setUri("http://www.veoh.com/rest/v2/execute.xml?apiKey=" . base64_decode(X_VlcShares_Plugins_Veoh::APIKEY) . "&method=veoh.video.findByPermalink&permalink=" . $id . "&");
		$response1 = $http1->request()->getBody();
		
		$fHashPath = array();
		if ( !preg_match( '/fullHashPath\=\"(?P<fHashPath>[^\"]+)\"/' , $response1, $fHashPath) ) {
			X_Debug::e("Can't get fHashPath. Regex failed");
			throw new Exception("Can't get fHashPath");
		}
		$fHashPath = $fHashPath['fHashPath'];
		
		$fHashToken = array();
		if ( !preg_match( '/fullHashPathToken\=\"(?P<fHashToken>[^\"]+)\"/' , $response1, $fHashToken) ) {
			X_Debug::e("Can't get fHashToken. Regex failed");
			throw new Exception("Can't get fHashToken");
		}
		$fHashToken = $fHashToken['fHashToken'];
		
		// TODO check if correct
		$fHashToken = mcrypt_decrypt(
			MCRYPT_RIJNDAEL_128,
			pack('H*', base64_decode(X_VlcShares_Plugins_Veoh::SKEY)),
			base64_decode($fHashToken),
			MCRYPT_MODE_CBC,
			pack('H*', base64_decode(X_VlcShares_Plugins_Veoh::IV))
		);
		
		//$fHashToken = trim($fHashToken);
		if ( !preg_match('/(?P<fHashToken>[a-z0-9A-z]+)/', $fHashToken, $fHashToken) ) {
			throw new Exception("Decryption failed");
		}
		
		$fHashToken = $fHashToken['fHashToken'];
		
		if ( $fHashPath == null || $fHashToken == null ) {
			throw new Exception("Hoster failure");
		}
		
		X_Debug::i("HashPath: $fHashPath, HashToken: $fHashToken");
		
		$http1->getCookieJar()->addCookie(new Zend_Http_Cookie(
			"fbsetting_$fbsetting",
			"%7B%22connectState%22%3A2%2C%22oneLineStorySetting%22%3A3%2C%22shortStorySetting%22%3A3%2C%22inFacebook%22%3Afalse%7D",
			"http://www.veoh.com"
		));

		$http1->getCookieJar()->addCookie(new Zend_Http_Cookie(
			"base_domain_$fbsetting",
			"veoh.com",
			"http://www.veoh.com"
		));
		
		$cookies = $http1->getCookieJar()->getAllCookies(Zend_Http_CookieJar::COOKIE_STRING_CONCAT);
		
		$opts = array('http' =>
			array(
				'header'  => array(
					"Referer: http://www.veoh.com/static/swf/qlipso/production/MediaPlayer.swf?version=2.0.0.011311.5",
					"x-flash-version: 10,1,53,64",
					"Cookie: $cookies"
				),
				'content' => $fHashPath . $fHashToken
			)
		);
		
		$context  = stream_context_create($opts);
		
		X_Debug::i("Video url: $fHashPath$fHashToken");
		
		// this action is so special.... no layout or viewRenderer
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
		
		
		// if user abort request (vlc/wii stop playing), this process ends
		ignore_user_abort(false);
		
		// close and clean the output buffer, everything will be read and send to device
		ob_end_clean();
		
		header("Content-Type: video/flv");
		
		@readfile("$fHashPath$fHashToken", false, $context);
	}
	
}
