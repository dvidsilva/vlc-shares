<?php


class X_VlcShares_Plugins_Helper_Hulu extends X_VlcShares_Plugins_Helper_Abstract {
	
	const VERSION_CLEAN = '0.1';
	const VERSION = '0.1';
	
	const HULU_PLAYER = 'http://download.hulu.com/huludesktop.swf';
	
	static private $KEYS_PID = array(
		'6fe8131ca9b01ba011e9b0f5bc08c1c9ebaf65f039e1592d53a30def7fced26c',
		'd3802c10649503a60619b709d1278ffff84c1856dfd4097541d55c6740442d8b',
		'c402fb2f70c89a0df112c5e38583f9202a96c6de3fa1aa3da6849bb317a983b3',
		'e1a28374f5562768c061f22394a556a75860f132432415d67768e0c112c31495',
		'd3802c10649503a60619b709d1278efef84c1856dfd4097541d55c6740442d8b'
	);
	
	static private $KEYS_SMIL = array(
		array('4878B22E76379B55C962B18DDBC188D82299F8F52E3E698D0FAF29A40ED64B21', 'WA7hap7AGUkevuth'),
		array('246DB3463FC56FDBAD60148057CB9055A647C13C02C64A5ED4A68F81AE991BF5', 'vyf8PvpfXZPjc7B1'),
		array('8CE8829F908C2DFAB8B3407A551CB58EBC19B07F535651A37EBC30DEC33F76A2', 'O3r9EAcyEeWlm5yV'),
		array('852AEA267B737642F4AE37F5ADDF7BD93921B65FE0209E47217987468602F337', 'qZRiIfTjIGi3MuJA'),
		array('76A9FDA209D4C9DCDFDDD909623D1937F665D0270F4D3F5CA81AD2731996792F', 'd9af949851afde8c'),
		array('1F0FF021B7A04B96B4AB84CCFD7480DFA7A972C120554A25970F49B6BADD2F4F', 'tqo8cxuvpqc7irjw'),
		array('3484509D6B0B4816A6CFACB117A7F3C842268DF89FCC414F821B291B84B0CA71', 'SUxSFjNUavzKIWSh'),
		array('B7F67F4B985240FAB70FF1911FCBB48170F2C86645C0491F9B45DACFC188113F', 'uBFEvpZ00HobdcEo'),
		array('40A757F83B2348A7B5F7F41790FDFFA02F72FC8FFD844BA6B28FD5DFD8CFC82F', 'NnemTiVU0UA5jVl0'),
		array('d6dac049cc944519806ab9a1b5e29ccfe3e74dabb4fa42598a45c35d20abdd28', '27b9bedf75ccA2eC')		
	);
	
	static private $KEYS_PLAYER = 'yumUsWUfrAPraRaNe2ru2exAXEfaP6Nugubepreb68REt7daS79fase9haqar9sa';
	
	static private $KEYS_V = '888324234';
	
	static private $HMAC = 'f6daaa397d51f568dd068709b0ce8e93293e078f7dfc3b40dd8c32d36d2b3ce1';
	
	static private $KEYS_FP = 'Genuine Adobe Flash Player 001';
	
	private $_cachedSearch = array();
	private $_location = null;

	private $_fetched = false;
	
	private $options;
	
	/**
	 * @var Zend_Http_Client
	 */
	private $http = null;
	
	function __construct(Zend_Config $options = null) {
		
		if ( $options == null ) {
			$options = new Zend_Config(array('username' => '', 'password' => '', 'premium' => false));
		}
		$this->options = $options;
	}
	
	
	/**
	 * Set source location
	 * 
	 * @param $location Hulu ID (must be an id)
	 * @return X_VlcShares_Plugins_Helper_Hulu
	 */
	function setLocation($location) {
		if ( $this->_location != $location ) {
			$this->_location = $location;
			if ( array_key_exists($location, $this->_cachedSearch) ) {
				$this->_fetched = $this->_cachedSearch[$location];
			} else {
				$this->_fetched = false;
			}
		}
		return $this;
	}
	
	// TODO set protected
	public function fetch() {
		if ( $this->_location == null ) {
			X_Debug::w('Trying to fetch a hulu location without a location');
			throw new Exception('Trying to fetch a hulu location without a location');
		}
		if ( $this->_fetched === false ) {
			

			// ported code form http://gitorious.org/get-flash-videos-plugins/gfv-plugins/blobs/raw/release/Hulu.pm
			
			$http = $this->getHttpClient("http://www.hulu.com/watch/{$this->_location}");
			
			$response = $http->request();
			$datas = $response->getBody();
			
			X_Debug::i("Fetching main page for: {{$this->_location}}");
			
			//echo "<b>REQUESTED PAGE</b><textarea>".htmlentities($datas)."</textarea><br/>";
			
			$pageUrl = str_replace('.com:80/', '.com/', $http->getUri(true));
			
			X_Debug::i("Last location is {{$pageUrl}}");
			
			/*
			$matches = array();
			if ( !preg_match('/content_id\"\, (?P<CID>[0-9]+?)\)/', $datas, $matches) ) {
				X_Debug::e("Content_ID pattern failure");
				throw new Exception("Content_ID pattern failure");
			}
			$content_id = $matches['CID'];
			
			echo "<b>CONTENT ID FOUND:</b><textarea>".htmlentities($content_id)."</textarea><br/>";
			*/

			$matches = array();
			if ( !preg_match('/videoEmbedId = "(?P<EID>[^\"]+?)"/', $datas, $matches) ) {
				X_Debug::e("EID pattern failure");
				X_Debug::i("Searching EID in: \n$datas");
				throw new Exception("EID pattern failure");
			}
			$eid = $matches['EID'];
				
			//echo "<b>EID FOUND:</b><textarea>".htmlentities($eid)."</textarea><br/>";
			X_Debug::i("EID Found {{$eid}}");
				
			
			// fetch information about the player, required by rtmp
			//list($swfsize, $swfhash, $swfUrl) = $this->getPlayerData();
			
			//$http->setHeaders('Host', 'r.hulu.com');
			
			//echo "<b>API URL:</b><textarea>".htmlentities("http://r.hulu.com/videos?content_id=$content_id")."</textarea><br/>";
			
			//$http->setUri("http://r.hulu.com/videos?content_id=$content_id");
			
			$apiUrl = 'http://r.hulu.com/videos?eid='.$eid;
			
			X_Debug::i("Fetching video info from {{$apiUrl}}");
			
			$http->setUri($apiUrl);
			
			//echo "<b>API URL:</b><textarea>".htmlentities($apiUrl)."</textarea><br/>";
			
			$datas = $http->request()->getBody();
			
			//$http->setHeaders('Host', null);
			//echo "<b>API RESPONSE 1</b><textarea>".htmlentities($datas)."</textarea><br/>";
			
			$xml = new SimpleXMLElement($datas);
			
			if ( $xml->video == null ) {
				X_Debug::e("Invalid response for EID {{$eid}}");
				X_Debug::i("Response: \n$datas");
				throw new Exception("Invalid response for EID {{$eid}}");
			}
			
			//echo "<b>ENCODED PID</b><textarea>".htmlentities((string) $xml->video->pid[0])."</textarea><br/>";
			X_Debug::i("Encoded PID: {{$xml->video[0]->pid[0]}}");
			
			$pid = $this->decodePid( (string) $xml->video[0]->pid[0]);
			
			X_Debug::i("Decoded PID: {{$pid}}");
			
			//echo "<b>DECRYPTED PID</b><textarea>".htmlentities($pid)."</textarea><br/>";
			
			$title = (string) $xml->video->title[0];
			
			if ( $xml->video->{'media-type'} == "TV" ) {
			    $show_name      = $xml->video->show->name[0];
			    $season         = $xml->video->{"season-number"}[0];
			    $episode_number = $xml->video->{"episode-number"}[0];
			
			    $title = sprintf('%s - S%02dE%02d - %s', $show_name, $season, $episode_number, $title);
			}
			
			$description = (string) $xml->video->description[0];
			
			$length = X_Env::formatTime((int)$xml->video->duration[0]);
			
			$thumbnail = (string) $xml->video->{"thumbnail-url"}[0];
			
			$needProxy = $xml->video->{"allow-international"}[0] == 'false' ? true : false;
			
			X_Debug::i("Video info: ".print_r(array(
				'title' => $title,
				'description' => $description,
				'length' => $length,
				'thumbnail' => $thumbnail,
				'needProxy' => $needProxy
			), true));
			
			// new focus on smil infos
			
			//$auth = md5($pid.self::$KEYS_PLAYER);
			
			//echo "<b>AUTH</b><pre>$auth</pre><br/>";
			
			//echo "<b>REQUEST</b><pre>http://s.hulu.com/select.ashx?pid=$pid&auth=$auth&v=".self::$KEYS_V."</pre><br/>";
			
			//$http->setUri("http://s.hulu.com/select.ashx?pid=$pid&auth=$auth&v=".self::$KEYS_V);
			
			$now = (int) time();
			
			$parameters = array(
				'video_id' => $pid,
				'v' => self::$KEYS_V,
				'ts' => ((string) $now),
				'np' => '1',
				'vp' =>'1',
				'device_id' => '',	
				'pp' => 'Desktop',
				'dp_id' => 'Hulu',
				'region' => 'US',
				'ep' => '1',
				'language' => 'en'
			);
			
			$paramKeys = array_keys($parameters);
			
			$sortedParams = $parameters;
			ksort($sortedParams);
			
			$bcsl = '';
			foreach ($sortedParams as $key => $value) {
				$bcsl .= $key.$value;
			}
			
			$bcs = hash_hmac('md5', $bcsl, self::$HMAC);
			
			$smil_file_url = 'http://s.hulu.com/select?'.http_build_query($sortedParams)."&bcs={$bcs}";
			
			X_Debug::i("Fetching SMIL file from {{$smil_file_url}}");
			
			//echo "<b>SMIL FILE URL</b><textarea>".htmlentities($smil_file_url)."</textarea><br/>";
			
			$http->setUri($smil_file_url);
			
			$datas = $http->request()->getBody();
			
			//echo "<b>ENCODED SMIL</b><textarea>".htmlentities($datas)."</textarea><br/>";
			
			$datas = $this->decodeSmil($datas);
			
			//echo "<b>DECRYPTED SMIL</b><textarea>".htmlentities($datas)."</textarea><br/>";
			X_Debug::i("Decoded SMIL: \n$datas");
			
			$xml = new SimpleXMLElement($datas);
			
			//$vids = $xml->body->switch[1]->video[0];
			$vid = $xml->body->switch[1]->video[0];
			//$ref = $xml->body->switch[1]->ref[0]; // choose the better source... ignored now
			
			//echo "<b>VIDEOS</b><pre>".print_r($vid, true)."</pre><br/>";
			
			
			
			if ( is_null($vid) ) {
				X_Debug::e('No video tag. Video requires HULU Plus or you are not in US');
				X_Debug::i("Video info: ".print_r($vid, true));
				throw new Exception('No video tag. Video requires HULU Plus or you are not in US');
			}
			
			X_Debug::i("First video info: ".print_r($vid, true));
			
			// ported code: http://code.google.com/p/bluecop-xbmc-repo/source/browse/trunk/plugin.video.hulu/resources/lib/stream_hulu.py?spec=svn157&r=157
			
			
	        $stream = (string) $vid['stream'];
	        $server = (string) $vid['server'];
	        $token = (string) $vid['token'];
	        $cdn = (string) $vid['cdn'];
	        
	        $hostname = "";
	        $appName = "";
	        $protocol = "";
	        
	        $pattern = '/^(?P<protocol>[a-zA-z]+:\/\/)(?P<hostname>[^\/]+)\/(?P<appname>.*)$/';
	        
	        $matches = array();
	        if ( preg_match($pattern, $server, $matches ) ) {
	        	$protocol = $matches['protocol'];
	        	$hostname = $matches['hostname'];
	        	$appName = $matches['appname'];
	        }
	        
			/*	        
	        $matches = array();
	        if ( preg_match('#^rtmpe?://[^/]+/#', $server, $matches) ) {
	        	$app = substr($server, strlen($matches[0]));
	        }
	        
			$app = "$app?$token";
			$rtmp = "$server?$token";
	        $playpath = (string) $stream;
	        */
	        
	        // rtmp params based on cdn
	        
	        switch ($cdn) {
	        	case 'level3': 
	        		$appName .= "?sessionid=sessionId&$token";
	        		$stream = substr($stream, 0, -4); // remove .mp4
	        		$server = "$server?sessionid=sessionId&$token";
	        		break;

	        	case 'limelight': 
	        		$appName .= "?sessionid=sessionId&$token";
	        		$stream = substr($stream, 0, -4); // remove .mp4
	        		$server = "$server?sessionid=sessionId&$token";
	        		break;
	        		
	        	case 'akamai': 
	        		$appName .= "?sessionid=sessionId&$token";
	        		$server = "$server?sessionid=sessionId&$token";
	        		break;
	        	default :;
	        	
	        }
	        
			
			$this->_fetched = array(
				// general info
				'title' => $title,
				'description' => $description,
				'length' => $length,
				'thumbnail' => $thumbnail,
			
				// resource info
				'eid' => $eid,
				'pid' => $pid,
				'needProxy' => $needProxy,

				// rtmp
				'stream' => $stream,
				'server' => $server,
				'token' => $token,
				'cdn' => $cdn,
			
				'hostname' => $hostname,
				'protocol' => $protocol,
				'appName' => $appName,
				'playpath' => $stream,
				'pageUrl' => self::HULU_PLAYER,
				'swfUrl' => self::HULU_PLAYER,
			
				// rtmp specific
				
				//'pageUrl' => $pageUrl,
				//'swfsize' => $swfsize,
				//'swfhash' => $swfhash,
				//'swfUrl' => $swfUrl,
				//'smil' => $datas
				
			);
			
			$this->_fetched['url'] = X_RtmpDump::buildUri(array(
				'rtmp' => $server,
				'app' => $appName,
				'playpath' => $stream,
				'swfUrl' => self::HULU_PLAYER,
				'pageUrl' => self::HULU_PLAYER,
				'swfVfy' => self::HULU_PLAYER,
				//'socks' => '24.30.7.103:1705'
			));
			
			$this->_cachedSearch[$this->_location] = $this->_fetched;
			
			X_Debug::i("All info: ".print_r($this->_fetched, true));
			
			return $this->_fetched;
		}
	}
	
	protected function decodePid($pid) {

		/*
		my @keys = @{ read_keys()->{pid} };
		my @data = split /~/, $encrypted_pid;
		return $encrypted_pid if $data[1] eq '';
		*/
		
		$splitted = preg_split('/~/', $pid);
		
		if ( count($splitted) == 1 ) {
			if ( X_Env::startWith($splitted[0], "NO_MORE_RELEASES_PLEASE_" ) ) {
				return substr($splitted[0], strlen("NO_MORE_RELEASES_PLEASE_"));
			} else {
				return $splitted[0];
			}
		}
		
		
		/*
		my $cipher = Crypt::Rijndael->new(pack("H*", $data[1]));
		my $tmp = $cipher->decrypt(pack("H*", $data[0]));
		*/
		$tmp = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, @pack("H*", $splitted[1]), pack("H*", $splitted[0]), MCRYPT_MODE_ECB);
		
		foreach (self::$KEYS_PID as $key) {
			/*
		    my $cipher = Crypt::Rijndael->new(pack("H*", $key));
		    my $unencrypted_pid = $cipher->decrypt($tmp);
			*/
			$unencrypted_pid = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, pack("H*", $key), $tmp, MCRYPT_MODE_ECB);
			
			// if ($unencrypted_pid =~ /[0-9A-Za-z_-]{32}/) {
			if ( preg_match('/[0-9A-Za-z_-]{32}/', $unencrypted_pid)) {
				if ( X_Env::startWith($unencrypted_pid, "NO_MORE_RELEASES_PLEASE_" ) ) {
					return substr($unencrypted_pid, strlen("NO_MORE_RELEASES_PLEASE_"));
				} else {
					return $unencrypted_pid;
				}
			}
		}

		if ( X_Env::startWith($pid, "NO_MORE_RELEASES_PLEASE_" ) ) {
			return substr($pid, strlen("NO_MORE_RELEASES_PLEASE_"));
		} else {
			return $pid;
		}
		
	}
	
	protected function decodeSmil($encrypted_smil) {

		// TODO PORT THIS CODE
		/* 		
		# Also used to decrypt subtitles
		sub decrypt_smil {
		  my $encrypted_smil = shift;
		  $encrypted_smil = $smil
		*/
		
		// my $encrypted_data = pack("H*", $encrypted_smil);
		$encrypted_data = pack("H*", $encrypted_smil);

		//  my @xml_decrypt_keys = @{ read_keys()->{smil} };
		//  foreach my $key (@xml_decrypt_keys) {
		foreach ( self::$KEYS_SMIL as $couple ) {
			list($key, $iv) = $couple;

			// debug "XML decrypt key: $key->[0], IV: $key->[1]";
			
			// my $smil = "";
			$smil = '';

			/*
		    my $ecb = Crypt::Rijndael->new(pack("H*", @{$key}[0]));
		    my $unaes = $ecb->decrypt($encrypted_data);
			 */
			$uneas = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, @pack("H*", $key), $encrypted_data, MCRYPT_MODE_ECB);
			
			// my $xorkey = pack("Z*", @{$key}[1]);
			$xorkey = @pack("a*", $iv);
			
			// $xorkey = substr($xorkey, 0, 16);
			$xorkey = substr($xorkey, 0, 16);
			
			// for (my $i = 0; $i < ceil(length($encrypted_smil) / 32); $i++) {
			for ( $i = 0; $i < ceil( strlen($encrypted_smil) / 32 ); $i++ ) {
				// my $res = $xorkey ^ substr($unaes, $i*16, 16);
				$res = $xorkey ^ substr($uneas, $i * 16, 16);
				// $xorkey = substr($encrypted_data, $i*16, 16);
				$xorkey = substr($encrypted_data, $i * 16, 16);
				// $smil = "$smil$res";
				$smil .= $res;
			}
			
			// my $lastchar = ord(substr($smil, -1));
			$lastchar = ord(substr($smil, -1));
			
			// if (substr($smil, -$lastchar) == chr($lastchar) x $lastchar) {
			if ( substr($smil, -$lastchar) == str_repeat(chr($lastchar), $lastchar) ) {
				// substr($smil, -$lastchar) = "";
				$smil = substr($smil, 0, -$lastchar);
			}
			// ? > = remove the space
			// if ($smil =~ /^(?:<smil|\s*<.+? >.*<\/.+? >)/i) { # Fix for transcripts			
			if ( preg_match('/^(?:<smil|\s*<.+?>.*<\/.+?>)/i', $smil) ) {
				return $smil;
			}
		}
		
		throw new Exception("SMIL decryption failed");
	
	} 
	
	protected function getPlayerData() {
		
		// TODO implement this
		// ref: https://github.com/monsieurvideo/get-flash-videos/blob/master/lib/FlashVideo/Utils.pm
		// ref: Hulu.pm
		
		$http = $this->getHttpClient(self::HULU_PLAYER);
		
		$swfData = $http->request()->getBody();

		if ( substr($swfData, 0, 3) != 'CWS' ) {
			echo substr($swfData, 0, 3);
			return array('null', 'null', 'null');
		} 
		
		$swfData = "F".substr($swfData, 1, 7).gzuncompress(substr($swfData, 8));
		
		$swfsize = strlen($swfData);
		$swfhash = hash_hmac('sha256', $swfData, self::$KEYS_FP );
		$swfUrl = self::HULU_PLAYER;
		
		return array(
			$swfsize,
			$swfhash,
			$swfUrl 
		);
	}
	
	private function signParameters($params) {
		
		$sorted = $params;
		ksort($sorted);
		
		$imploded = '';
		foreach ($sorted as $key => $value) {
			$imploded .= "$key$value";
		}
		
		return hash_hmac('md5', $imploded, self::$HMAC);
		
	}
	
	/**
	 * @return Zend_Http_Client
	 */
	protected function getHttpClient($url = null) {
		if ( $this->http === null ) {
			$this->http = new Zend_Http_Client();
			// maybe it's better to offuscate it
			//$this->http->setHeaders('User-Agent', "vlc-shares/".X_VlcShares::VERSION." hulu/".X_VlcShares_Plugins_Hulu::VERSION);
			
			$this->http->setHeaders(
				array(
				    'User-Agent'    => 'Mozilla/5.0 (compatible; MSIE 7.0; Windows NT 6.0)',
				    'Accept'        => '*/*',
				)
			);
			// enable cookies
			$this->http->setCookieJar(true);
			
			/*
			$this->http->setAdapter("Zend_Http_Client_Adapter_Proxy");
			$this->http->setConfig(array(
				//'proxy_host' => "141.219.252.132",
				//'proxy_port' => '3128'
				
				//'proxy_host' => '131.179.150.72',
				//'proxy_port' => '3128',
				
				//'proxy_host' => '129.82.12.188',
				//'proxy_port' => '3128',
				
				//'proxy_host' => '130.49.221.40',
				//'proxy_port' => '3128',
				
				'proxy_host' => '65.55.73.222',
				'proxy_port' => '80'
			
			));
			*/
			
			
			
		}
		if ( $url !== null ) {
			$this->http->setUri($url);
		}
		return $this->http;
	}
	
}