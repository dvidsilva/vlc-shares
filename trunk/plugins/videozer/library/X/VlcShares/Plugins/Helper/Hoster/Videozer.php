<?php 

class X_VlcShares_Plugins_Helper_Hoster_Videozer implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'videozer';
	const PATTERN = '/http:\/\/(www\.)?videozer\.com\/(video|embed)\/(?P<ID>[A-Za-z0-9]+)/i';
	
	private $info_cache = array();

	const KEY_2 = 215678;
	
	const MAGIC_1A = 11;
	const MAGIC_1B = 77213;
	const MAGIC_1C = 81371;
	
	const MAGIC_2A = 17;
	const MAGIC_2B = 92717;
	const MAGIC_2C = 192811;
	
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
			if ( $matches['ID'] != '' ) {
				return $matches['ID'];
			}
			X_Debug::e("No id found in {{$url}}", self::E_ID_NOTFOUND);
			throw new Exception("No id found in {{$url}}");
		} else {
			X_Debug::e("Regex failed");
			throw new Exception("Regex failed", self::E_URL_INVALID);
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
		$infos = $this->getPlayableInfos($url, $isId);
		if ( $infos['rawinfo']['cfg']['msg'][0] != "you still have quota left" ) {
			throw new Exception("No more quota left", 99); // temp substitution for self::E_QUOTA_NOMORE
		}
		return $infos['url'];
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
		
		// use the api
		$http = new Zend_Http_Client($this->getHosterUrl($url),
			array(
				'headers' => array(
					'User-Agent' => "vlc-shares/".X_VlcShares::VERSION." videozer/".X_VlcShares_Plugins_Videozer::VERSION
/*					'User-Agent' => "Mozilla/5.0 (X11; Linux i686) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.121 Safari/535.2",
					'Referer' => 'http://www.videozer.com/player/player.swf?pv=1_1_37a',
					'Origin' => 'http://www.videozer.com',
					'Accept-Language:it-IT,it;q=0.8,en-US;q=0.6,en;q=0.4'*/
				)
			)
		);
		$http->setCookieJar(true);
		$http->request();
		
		$http->setUri("http://www.videozer.com/player_control/settings.php?v=" . $url . '&fv=v1.1.14');
		
		$datas = $http->request()->getBody();

		if ( preg_match("/(\"The page you have requested cannot be found|>The web page you were attempting to view may not exist or may have moved|>Please try to check the web address for typos)/i", $datas)) {
			throw new Exception("Invalid ID {{$url}}", self::E_ID_INVALID);
		}
		
		$json = Zend_Json::decode($datas);
		
		// format url
		$token = @base64_decode(@$json['cfg']['environment']['token1']);
		$cypher = @$json['cfg']['info']['sece2'];
		$key1 = @$json['cfg']['environment']['rkts'];
		
		
		
		$passkey = $this->decrypt($cypher, (int) $key1, self::KEY_2);
		
		//echo "Cypher: $cypher<br/>Key1: $key1<br/>Key2: ".self::KEY_2."<br/>Passkey: $passkey";
		
		$link = str_replace(':80', '', $token)."&c=".$passkey;

		/*
		$ctrlurl = @$json['cfg']['environment']['ctrl_url9'];
		
		$datas = new stdClass();
		$datas->d = $this->getHosterUrl($url);
		$datas->ut = @$json['cfg']['environment']['ut'];
		$datas->i = @$json['cfg']['environment']['icode'];
		$datas->t = time();
		$datas->ac = "34";
		$datas->u = "";
		$datas->c = "b455ce73e469a6f88c581843851cba71";
		$datas->v = $url;
		$datas = Zend_Json::encode(array($datas));
		
		$http->setUri($ctrlurl)
			->setMethod(Zend_Http_Client::POST)
			->setParameterPost('data', $datas)
			->setParameterPost("checkpromotion", 0);
		$http->request();
		*/
		
		$infos = array(
			'title' => @$json["cfg"]['info']['video']['title'],
			'description' => @$json["cfg"]['info']['video']['description'],
			'length' => 0,
			'thumbnail' => @$json["cfg"]['environment']['thumbnail'],
			'url' => $link,
			'rawinfo' => $json
		);
		
		// add in cache
		$this->info_cache[$url] = $infos;
		
		return $infos;
		
	}
	
	function getHosterUrl($playableId) {
		return "http://www.videozer.com/video/$playableId";
	}
	
	private function decrypt($cypher, $keyOne, $keyTwo) {
	
		$C = self::hex2Bin($cypher);
	
		$B = array();
	
		for ( $i = 0; $i < 384; $i++ ) {
	
			$keyOne = ($keyOne * self::MAGIC_1A + self::MAGIC_1B) % self::MAGIC_1C;
			$keyTwo = ($keyTwo * self::MAGIC_2A + self::MAGIC_2B) % self::MAGIC_2C;
	
			$B[$i] = ($keyOne + $keyTwo) % 128;
		}
	
		$x = $y = $z = 0;
	
		for ( $i = 255; $i >= 0; $i-- ) {
			$x = $B[$i];
			$y = $i % 128;
			$z = $C[$x];
			$C[$x] = $C[$y];
			$C[$y] = $z;
		}
	
		for ( $i = 0; $i < 128; $i++ ) {
			$C[$i] = $C[$i] ^ $B[$i + 256] & 1;
		}
	
		return self::bin2Hex($C);
	
	}
	
	private static function hex2Bin($hexString, $pad = 256) {
	
		$result = '';
		for ( $i = 0; $i < strlen($hexString); $i++ ) {
			$result .= str_pad(decbin(hexdec($hexString[$i])), 4, '0', STR_PAD_LEFT);
		}
		$result = str_pad($result, $pad, '0', STR_PAD_LEFT);
		return $result;
	}
	
	private static function bin2Hex($binString) {
	
		$result = '';
		//$binString = strrev($binString);
	
		for ( $i = 0; $i < strlen($binString); $i = $i + 4) {
			$segment = substr($binString, $i, 4);
			//$segment = strrev($segment);
			$segment = str_pad($segment, 4, '0', STR_PAD_LEFT);
			$result .= dechex(bindec($segment));
		}
		return /*strrev*/($result);
	}	
	
}
