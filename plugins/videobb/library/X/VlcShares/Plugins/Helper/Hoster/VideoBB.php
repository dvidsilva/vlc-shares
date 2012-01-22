<?php 

class X_VlcShares_Plugins_Helper_Hoster_VideoBB implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'videobb';
	const PATTERN = '/http\:\/\/((www\.)?)videobb\.com\/(video\/|watch_video.php\?v\=)(?P<ID>[A-Za-z0-9]+)/';
	
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
		226593,
		441252,
		301517,
		596338,
		852084
	);
	
	const MAGIC_1 = 950569;
	
	
	private $info_cache = array();
	
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
		$http = new Zend_Http_Client("http://www.videobb.com/player_control/settings.php?v=" . $url,
			array(
				'headers' => array(
					'User-Agent' => "vlc-shares/".X_VlcShares::VERSION." videobb/".X_VlcShares_Plugins_VideoBB::VERSION
				)
			)
		);
		
		$datas = $http->request()->getBody();

		$json = Zend_Json::decode($datas);
		
		if ( @$json['settings']['messages']['display']['text'] == 'The video you have requested is not available' ) {
			throw new Exception("Invalid ID {{$url}}", self::E_ID_INVALID);
		}
		
		// format url
		
		$token = @base64_decode(@$json['settings']['config']['token1']);
		
		if ( $token == '' ) {
			X_Debug::e('Link has been removed or is unavailable: '.@$json['settings']['messages']['display']['text'], true);
			throw new Exception('Link has been removed or is unavailable: '.@$json['settings']['messages']['display']['text'], self::E_ID_INVALID);
		}
		
		// prepare token for append: add & as last char if missing
		$token = rtrim($token, '&').'&';
		
		$spen = @$json['settings']['login_status']['spen'];
		$salt = @$json['settings']['login_status']['salt'];
		
		$algoCtrl = pack('H*', $this->decryptBit($spen, $salt, self::MAGIC_1));
		$algoCtrl = explode(';', $algoCtrl);
		
		//echo "Algoctrl: ".print_r($algoCtrl, true);

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
		$key1 = @$json['settings']['config']['rkts'];

		foreach (explode('&', $algoCtrl[0]) as $val) {
			list($key, $value) = explode('=', $val);

			$decryptedString = "";
			$keyString = "";
			
			switch ($value) {
				case 1:
					$keyString = @$json['settings']['video_details']['sece2'];
					$decryptedString = $this->decryptByte($keyString, $key1, $key2);
					break;
				case 2:
					$keyString = @$json['settings']['banner']['g_ads']['url'];
					$decryptedString = $this->decryptBit($keyString, $key1, $key2);
					break;
				case 3:
					$keyString = @$json['settings']['banner']['g_ads']['type'];
					$decryptedString = $this->decryptBit9300($keyString, $key1, $key2);
					break;
				case 4:
					$keyString = @$json['settings']['banner']['g_ads']['time'];
					$decryptedString = $this->decryptBitLion($keyString, $key1, $key2);
					break;
				case 5:
					$keyString = @$json['settings']['login_status']['euno'];
					$decryptedString = $this->decryptBitHeal($keyString, $key1, $key2);
					break;
				case 6:
					$keyString = @$json['settings']['login_status']['sugar'];
					$decryptedString = $this->decryptBitBrokeUp($keyString, $key1, $key2);
					break;
				default:
					break;
			}
			
			$token .= $key . '=' . $decryptedString . "&";  
			
			
		}
		
		$token .= "start=0";
		
		
		$infos = array(
			'title' => @$json['settings']['video_details']['video']['title'],
			'description' => @$json['settings']['video_details']['video']['description'],
			'length' => 0,
			'thumbnail' => @$json['settings']['config']['thumbnail'],
			'url' => $token
		);
		
		// add in cache
		$this->info_cache[$url] = $infos;
		
		return $infos;
		
	}
	
	function getHosterUrl($playableId) {
		return "http://www.videobb.com/video/$playableId";
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
