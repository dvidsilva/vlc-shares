<?php

class X_Upnp_Announcer implements X_Threads_Runnable {

	/**
	 * @var X_Threads_Thread
	 */
	protected $thread;
	
	/* (non-PHPdoc)
	 * @see X_Threads_Runnable::run()
	 */
	public function run($params = array(), X_Threads_Thread $thread) {
		
		$this->thread = $thread;
		
		$location = isset($params['url']) ? $params['url'] : "http://{$_SERVER['SERVER_IP']}:{$_SERVER["SERVER_PORT"]}/vlc-shares/upnp/manifest/file/MediaServerServiceDesc.xml";
		$cooldown = isset($params['cooldown']) ? intval($params['cooldown']) : 5;
		$packetDelay = isset($params['packet-delay']) ? intval($params['packet-delay']) : 15;
		
		$sended = $this->_sddpSend($location, $packetDelay);
			
		$this->thread->executeNext(__CLASS__, $params);
		
		sleep($cooldown);
		
		return ($sended ? self::RETURN_NORMAL : self::RETURN_ERROR);
		
	}
	
	/**
	 * Send an UDP packet with $buf content
	 * 
	 * @param string $buf
	 * @param int $delay
	 * @param string $host
	 * @param int $port
	 * @return boolean
	 */
	private function _udpSend($buf, $delay = 15, $host = "239.255.255.250", $port = 1900) {
		
		// create socket
		$socket = socket_create ( AF_INET, SOCK_DGRAM, SOL_UDP );
		if ( !$socket ) return false;

		// bind socket
		if ( !socket_bind ( $socket, '0' ) ) {
			socket_close ( $socket );
			return false;
		}
		 
		// send packet
		if ( false === socket_sendto ( $socket, $buf, strlen ( $buf ), 0, $host, $port ) ) {
			socket_close ( $socket );
			return false;
		}
		
		usleep ( $delay * 1000 ); // microsecond * 1000 = milliseconds
		
		return true;
	}
	
	/**
	 * Send an SDDP packet for this Upnp server
	 * 
	 * @param string $location UPNP end-point service
	 * @param int $delay
	 * @param string $host
	 * @param string $port
	 * @return boolean
	 */
	private function _sddpSend($location, $delay = 15, $host = "239.255.255.250", $port = 1900) {
		
		$uuidStr = 'badbabe1-6666-6666-6666-f00d00c0ffee';
		
		$strHeader = 'NOTIFY * HTTP/1.1' . "\r\n";
		$strHeader .= 'HOST: 239.255.255.250:1900' . "\r\n";
		$strHeader .= "LOCATION: $location\r\n";
		$strHeader .= 'SERVER: MYFAKESERVER UDP 127' . "\r\n";
		$strHeader .= 'CACHE-CONTROL: max-age=7200' . "\r\n";
		$strHeader .= 'NTS: ssdp:alive' . "\r\n";
		
		$rootDevice = 'NT: upnp:rootdevice' . "\r\n";
		$rootDevice .= 'USN: uuid:' . $uuidStr . '::upnp:rootdevice' . "\r\n" . "\r\n";
		
		$buf = $strHeader . $rootDevice;
		if ( !$this->_udpSend ( $buf, $delay ) ) return false;
		
		$uuid = 'NT: uuid:' . $uuidStr . "\r\n";
		$uuid .= 'USN: uuid:' . $uuidStr . "\r\n" . "\r\n";
		$buf = $strHeader . $uuid;
		if ( !$this->_udpSend ( $buf, $delay ) ) return false;
		
		$deviceType = 'NT: urn:schemas-upnp-org:device:MediaServer:1' . "\r\n";
		$deviceType .= 'USN: uuid:' . $uuidStr . '::urn:schemas-upnp-org:device:MediaServer:1' . "\r\n" . "\r\n";
		$buf = $strHeader . $deviceType;
		if ( !$this->_udpSend ( $buf, $delay ) ) return false;
		
		$serviceCM = 'NT: urn:schemas-upnp-org:service:ConnectionManager:1' . "\r\n";
		$serviceCM .= 'USN: uuid:' . $uuidStr . '::urn:schemas-upnp-org:service:ConnectionManager:1' . "\r\n" . "\r\n";
		$buf = $strHeader . $serviceCM;
		if ( !$this->_udpSend ( $buf, $delay ) ) return false;
		
		$serviceCD = 'NT: urn:schemas-upnp-org:service:ContentDirectory:1' . "\r\n";
		$serviceCD .= 'USN: uuid:' . $uuidStr . '::urn:schemas-upnp-org:service:ContentDirectory:1' . "\r\n" . "\r\n";
		$buf = $strHeader . $serviceCD;
		if ( !$this->_udpSend ( $buf, $delay ) ) return false;
		
		return true;
	}


}
