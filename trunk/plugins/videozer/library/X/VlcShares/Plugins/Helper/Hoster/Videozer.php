<?php 

class X_VlcShares_Plugins_Helper_Hoster_Videozer implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'videozer';
	const PATTERN = '/http:\/\/(www\.)?videozer\.com\/(video|embed)\/(?P<ID>[A-Za-z0-9]+)/i';
	
	private $info_cache = array();

	
	//{{{ MAGICs for BIT
	const MAGIC_BIT_1A = 11;
	const MAGIC_BIT_1B = 77213;
	const MAGIC_BIT_1C = 81371;
	
	const MAGIC_BIT_2A = 17;
	const MAGIC_BIT_2B = 92717;
	const MAGIC_BIT_2C = 192811;
	//}}}
	

	//{{{ MAGICs for BIT9300
	const MAGIC_BIT9300_1A = 26;
	const MAGIC_BIT9300_1B = 25431;
	const MAGIC_BIT9300_1C = 56989;
	
	const MAGIC_BIT9300_2A = 93;
	const MAGIC_BIT9300_2B = 32589;
	const MAGIC_BIT9300_2C = 784152;
	//}}}
	

	//{{{ MAGICs for BITBROKEUP
	const MAGIC_BITBROKEUP_1A = 22;
	const MAGIC_BITBROKEUP_1B = 66595;
	const MAGIC_BITBROKEUP_1C = 17447;
	
	const MAGIC_BITBROKEUP_2A = 52;
	const MAGIC_BITBROKEUP_2B = 66852;
	const MAGIC_BITBROKEUP_2C = 400595;
	//}}}
	

	//{{{ MAGICs for BITHEAL
	const MAGIC_BITHEAL_1A = 10;
	const MAGIC_BITHEAL_1B = 12254;
	const MAGIC_BITHEAL_1C = 95369;
	
	const MAGIC_BITHEAL_2A = 39;
	const MAGIC_BITHEAL_2B = 21544;
	const MAGIC_BITHEAL_2C = 545555;
	//}}}
	
	
	//{{{ MAGICs for BITLION
	const MAGIC_BITLION_1A = 82;
	const MAGIC_BITLION_1B = 84669;
	const MAGIC_BITLION_1C = 48779;
	
	const MAGIC_BITLION_2A = 32;
	const MAGIC_BITLION_2B = 65598;
	const MAGIC_BITLION_2C = 115498;
	//}}}
	
	private static $INTERNAL_KEYS = array( 
		215678,
		516929,
		962043,
		461752,
		141994
	);
	
	const MAGIC_1 = 950569;
	
		
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
		/*
		$cypher = @$json['cfg']['info']['sece2'];
		$key1 = @$json['cfg']['environment']['rkts'];
		$passkey = $this->decrypt($cypher, (int) $key1, self::KEY_2);
		
		//echo "Cypher: $cypher<br/>Key1: $key1<br/>Key2: ".self::KEY_2."<br/>Passkey: $passkey";
		
		$link = str_replace(':80', '', $token)."&c=".$passkey;
		*/
		
		if ( $token == '' ) {
			X_Debug::e('Link has been removed or is unavailable: '.@$json['cfg']['msg']['display']['text'], true);
			throw new Exception('Link has been removed or is unavailable: '.@$json['cfg']['msg']['display']['text'], self::E_ID_INVALID);
		}
		
		
		// prepare token for append: add & as last char if missing
		$token = rtrim($token, '&').'&';
		
		$spen = @$json['cfg']['login']['spen'];
		$salt = @$json['cfg']['login']['salt'];
		
		$algoCtrl = pack('H*', $this->decryptBit($spen, $salt, self::MAGIC_1));
		$algoCtrl = explode(';', $algoCtrl);
		

		if ( count($algoCtrl) <= 1 ) {
			X_Debug::e("Invalid AlgoCtrl: ".print_r($algoCtrl, true));
			throw new Exception("Invalid AlgoCtrl value", self::E_ID_INVALID);
		}
		
		$keys = array();
		
		foreach (explode('&', $algoCtrl[1]) as $val) {
			list($key, $value) = explode('=', $val);
			$keys[$key] = $value;
		}
		
		//echo "Keys: ".print_r($keys, true);
		
		$key2 = self::$INTERNAL_KEYS[$keys['ik'] - 1];
		$key1 = @$json['cfg']['environment']['rkts'];

		foreach (explode('&', $algoCtrl[0]) as $val) {
			list($key, $value) = explode('=', $val);

			$decryptedString = "";
			$keyString = "";
			
			switch ($value) {
				case 1:
					$keyString = @$json['cfg']['info']['sece2'];
					$decryptedString = $this->decryptByte($keyString, $key1, $key2);
					break;
				case 2:
					$keyString = @$json['cfg']['ads']['g_ads']['url'];
					$decryptedString = $this->decryptBit($keyString, $key1, $key2);
					break;
				case 3:
					$keyString = @$json['cfg']['ads']['g_ads']['type'];
					$decryptedString = $this->decryptBit9300($keyString, $key1, $key2);
					break;
				case 4:
					$keyString = @$json['cfg']['ads']['g_ads']['time'];
					$decryptedString = $this->decryptBitLion($keyString, $key1, $key2);
					break;
				case 5:
					$keyString = @$json['cfg']['login']['euno'];
					$decryptedString = $this->decryptBitHeal($keyString, $key1, $key2);
					break;
				case 6:
					$keyString = @$json['cfg']['login']['sugar'];
					$decryptedString = $this->decryptBitBrokeUp($keyString, $key1, $key2);
					break;
				default:
					break;
			}
			
			$token .= $key . '=' . $decryptedString . "&";  
			
			
		}
		
		$token .= "start=0";
		
		
		$infos = array(
			'title' => @$json["cfg"]['info']['video']['title'],
			'description' => @$json["cfg"]['info']['video']['description'],
			'length' => 0,
			'thumbnail' => @$json["cfg"]['environment']['thumbnail'],
			'url' => $token,
			'rawinfo' => $json
		);
		
		// add in cache
		$this->info_cache[$url] = $infos;
		
		return $infos;
		
	}
	
	function getHosterUrl($playableId) {
		return "http://www.videozer.com/video/$playableId";
	}
	
	private function decryptBit($arg1, $arg2, $arg3) {
		return $this->zDecrypt(false, $arg1, $arg2, $arg3, self::MAGIC_BIT_1A, self::MAGIC_BIT_1B, self::MAGIC_BIT_1C, self::MAGIC_BIT_2A, self::MAGIC_BIT_2B, self::MAGIC_BIT_2C);
	}

	private function decryptBit9300($arg1, $arg2, $arg3) {
		return $this->zDecrypt(false, $arg1, $arg2, $arg3, self::MAGIC_BIT9300_1A, self::MAGIC_BIT9300_1B, self::MAGIC_BIT9300_1C, self::MAGIC_BIT9300_2A, self::MAGIC_BIT9300_2B, self::MAGIC_BIT9300_2C);
	}

	private function decryptBitBrokeUp($arg1, $arg2, $arg3) {
		return $this->zDecrypt(false, $arg1, $arg2, $arg3, self::MAGIC_BITBROKEUP_1A, self::MAGIC_BITBROKEUP_1B, self::MAGIC_BITBROKEUP_1C, self::MAGIC_BITBROKEUP_2A, self::MAGIC_BITBROKEUP_2B, self::MAGIC_BITBROKEUP_2C);
	}

	private function decryptBitHeal($arg1, $arg2, $arg3) {
		return $this->zDecrypt(false, $arg1, $arg2, $arg3, self::MAGIC_BITHEAL_1A, self::MAGIC_BITHEAL_1B, self::MAGIC_BITHEAL_1C, self::MAGIC_BITHEAL_2A, self::MAGIC_BITHEAL_2B, self::MAGIC_BITHEAL_2C);
	}

	private function decryptBitLion($arg1, $arg2, $arg3) {
		return $this->zDecrypt(false, $arg1, $arg2, $arg3, self::MAGIC_BITLION_1A, self::MAGIC_BITLION_1B, self::MAGIC_BITLION_1C, self::MAGIC_BITLION_2A, self::MAGIC_BITLION_2B, self::MAGIC_BITLION_2C);
	}

	private function decryptByte($arg1, $arg2, $arg3) {
		return $this->zDecrypt(true, $arg1, $arg2, $arg3, self::MAGIC_BIT_1A, self::MAGIC_BIT_1B, self::MAGIC_BIT_1C, self::MAGIC_BIT_2A, self::MAGIC_BIT_2B, self::MAGIC_BIT_2C);
	}

	
	private function zDecrypt($algo = true, $cipher, $keyOne, $keyTwo, $arg0, $arg1, $arg2, $arg3, $arg4, $arg5) {
		
		$x = $y = $z = 0;
		
		$C = self::hex2Bin($cipher, strlen($cipher) * 4);
		$len = strlen($C) * 2;
		if ( $algo ) {
			$len = 256;
		}
		
		$B = array();
		$A = array();
		
		$i = 0;
		while ( $i < $len * 1.5 ) {
			
			$keyOne = ($keyOne * $arg0 + $arg1) % $arg2;
			$keyTwo = ($keyTwo * $arg3 + $arg4) % $arg5;

			$B[$i] = ($keyOne + $keyTwo) % ($len / 2);
			
			$i++;
		} 
		
		$i = $len;
		while ( $i >= 0 ) {

			$x = $B[$i];
			$y = $i % ($len / 2);
			$z = $C[$x];
			$C[$x] = $C[$y];
			$C[$y] = $z;
			
			$i--;
		}
		
		$i = 0;
		while ( $i < $len / 2 ) {
			$C[$i] = $C[$i] ^ $B[$i + $len] & 1;
			$i++;
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
