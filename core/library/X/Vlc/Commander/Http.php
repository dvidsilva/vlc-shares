<?php

require_once 'X/Vlc/Commander.php';
require_once 'Zend/Config.php';
require_once 'Zend/Dom/Query.php';
require_once 'X/Env.php';

class X_Vlc_Commander_Http extends X_Vlc_Commander {
	
	private $http_command = '';
	private $http_timeout = 1;
	private $http_host = '';
	private $http_port = '';
	
	public function __construct($options = array()) {
		parent::__construct($options);

		//X_Debug::i(var_export($options->toArray(), true));
		
		$this->http_command = $this->options->commander->http->get('command', 'http://{%host%}:{%port%}/requests/status.xml{%command%}');
		
		$host = $this->options->commander->http->get('host', '127.0.0.1');
		$port = $this->options->commander->http->get('port', '8080');
		$this->http_host = $host;
		$this->http_port = $port;
		$this->http_timeout = (int) $this->options->commander->http->get('timeout', 5);
		$this->http_command = str_replace(array('{%host%}', '{%port%}'), array($host, $port), $this->http_command);
		
	}
	
	/**
	 * @param unknown_type $resource
	 */
	public function play($resource = null) {
		if ( !is_null($resource) /*&& file_exists($resource)*/ ) {
			// non posso controllare l'esistenza per i flussi
			// remoti
			//$this->_send('clear');
			$this->_send('command=in_play&input='.$this->encodeInput($resource));
		} else {
			$this->_send('command=play_play&id=0');
		}
	}

	/**
	 * 
	 */
	public function stop() {
		$this->_send('command=pl_stop');
	}

	/**
	 * 
	 */
	public function pause() {
		$this->_send('command=pl_pause&id=0');
	}

	/**
	 * @param int $time
	 * @param bool $relative
	 */
	public function seek($time, $relative = false) {
		if ( $relative ) {
			$cTime = $this->getCurrentTime();
			$time = $cTime + $time;
			if ( $time < 0 ) $time = 0;
		}
		$this->_send('command=seek&val='.$time);
	}

	/**
	 * 
	 */
	public function next() {
		$this->_send('command=pl_next');
	}

	/**
	 * 
	 */
	public function previous() {
		$this->_send('command=pl_previous');
	}

	/**
	 * 
	 */
	public function getInfo($infos = null) {
		// uso per fare il parsing
		if ( is_null($infos ) ) { 
			$infos = $this->_send('');
		}
		/**
		<root> 
		  <volume>256</volume> 
		  <length>234</length> 
		  <time>12</time> 
		  <state>playing</state> 
		  <position>5</position> 
		  <fullscreen></fullscreen> 
		  <random>0</random> 
		  <loop>0</loop> 
		  <repeat>0</repeat> 
		  <information> 
		    
		      <category name="Diffusione 0"> 
		        
		          <info name="Tipo">Video</info> 
		        
		          <info name="Codifica">XVID</info> 
		        
		          <info name="Risoluzione">720x576</info> 
		        
		          <info name="Risoluzione video">720x576</info> 
		        
		          <info name="Immagini al secondo">25</info> 
		        
		      </category> 
		    
		      <category name="Diffusione 1"> 
		        
		          <info name="Tipo">Audio</info> 
		        
		          <info name="Codifica">mpga</info> 
		        
		          <info name="Canali">Mono</info> 
		        
		          <info name="Campionamento">48000 Hz</info> 
		        
		          <info name="Bitrate">80 kb/s</info> 
		        
		      </category> 
		    
			   <meta-information> 
				    <title><![CDATA[/media/Windows/video.avi]]></title> 
				    <artist><![CDATA[]]></artist> 
				    <genre><![CDATA[]]></genre> 
				    <copyright><![CDATA[]]></copyright> 
				    <album><![CDATA[]]></album> 
				    <track><![CDATA[]]></track> 
				    <description><![CDATA[]]></description> 
				    <rating><![CDATA[]]></rating> 
				    <date><![CDATA[]]></date> 
				    <url><![CDATA[]]></url> 
				    <language><![CDATA[]]></language> 
				    <now_playing><![CDATA[]]></now_playing> 
				    <publisher><![CDATA[]]></publisher> 
				    <encoded_by><![CDATA[]]></encoded_by> 
				    <art_url><![CDATA[]]></art_url> 
				    <track_id><![CDATA[]]></track_id> 
				    </meta-information> 
			   </information> 
		  <stats> 
		    <readbytes>1498004</readbytes> 
		    <inputbitrate>0,107826</inputbitrate> 
		    <demuxreadbytes>1335729</demuxreadbytes> 
		    <demuxbitrate>0,139785</demuxbitrate> 
		    <decodedvideo>166</decodedvideo> 
		    <displayedpictures>467</displayedpictures> 
		    <lostpictures>27</lostpictures> 
		    <decodedaudio>528</decodedaudio> 
		    <playedabuffers>528</playedabuffers> 
		    <lostabuffers>3</lostabuffers> 
		    <sentpackets>0</sentpackets> 
		    <sentbytes>0</sentbytes> 
		    <sendbitrate>0,000000</sendbitrate> 
		  </stats> 
		</root> 
		*/
		$rInfos = array();
		try {
			$dom = new Zend_Dom_Query($infos);
			// lunghezza
			$results = $dom->queryXpath('/root/length');
			if ( count($results) > 0 ) {
				$rInfos['length'] = $results->current()->nodeValue;
			}
			// posizione ora
			$results = $dom->queryXpath('/root/time');
			if ( count($results) > 0 ) {
				$rInfos['time'] = $results->current()->nodeValue;
			}
			// posizione name
			$results = $dom->queryXpath('/root/information/meta-information/title');
			if ( count($results) > 0 ) {
				$rInfos['name'] = $results->current()->nodeValue;
			}
			
			/* l'aggiungo in 0.5 forse
			// posizione ora
			$results = $dom->queryXpath('/root/information/category');
			if ( count($results) > 0 ) {
				$rInfos['streams'] = array();
				//$rInfos['length'] = $results[0]->nodeValue;
				foreach ( $results as $category ) {
					$stream = array();
					//$stream
					$rInfos['streams'][] = $stream;
				}
			}
			*/
		} catch (Exception $e) {
			X_Debug::w("Catch exception: {$e->getMessage()}");
		}
		
		
		return $rInfos;
	}

	/**
	 * 
	 */
	public function getTotalTime() {
		$output = $this->getInfo();
		return @$output['length'];
	}

	/**
	 * 
	 */
	public function getCurrentTime() {
		$output = $this->getInfo();
		return @$output['time'];
	}

	/**
	 * 
	 */
	public function getCurrentName() {
		$output = $this->getInfo();
		return @$output['name'];
	}

	/**
	 * @param unknown_type $signal
	 * @param unknown_type $values
	 */
	public function sendCustomSignal($signal, $values = null) {
		return $this->_send($signal . (!is_null($values) ? ' ' . "&$values" : ''));
	}


	public function getDefaultVlcArg() {
		$host = $this->http_host;
		$port = $this->http_port;
		
		switch ( $this->options->version ) {
			case '1.2.x': // default future use
			case '1.2-git': // http:port is broken in 1.2-git
				return '-I http --http-host="'.$host.'" --http-port="'.$port.'"';
			case '1.1.x':
			default:
				return '-I http --http-host="'.$host.':'.$port.'"';
		}
	}
	
	private function _send($command) {
		X_Env::debug(__METHOD__.": sending message $command");
		$command = str_replace('{%command%}', "?$command", $this->http_command);
		$ctx = stream_context_create(array( 
		    'http' => array( 
		        'timeout' => $this->http_timeout
		        ) 
		    ) 
		); 		
		//$return = X_Env::execute($command, $outtype, X_Env::EXECUTE_PS_WAIT);
		$return = @file_get_contents($command, false, $ctx);
		return $return;
	}
	
	private $_translate = array(
		'tipo' => 'type',
		'codifica' => 'codec',
		'risoluzione' => 'resolution',
		'risoluzione video' => 'v_resolution',
		'immagini al secondo' => 'fps',
		'canali' => 'channel',
		'campionamento' => 'freq',
		'bitrate' => 'bitrate'
	);
	
	private function _reverseTranslate($name) {
		$name = strtolower($name);
		if ( array_key_exists($name, $this->_translate) ) {
			return $this->_translate[$name];
		} else {
			return lcfirst(str_replace(' ', '', ucwords($name))); // camelcase
		}
	}
	
	private function encodeURIComponent($str) {
		$revert = array ('%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')' );
		return strtr ( rawurlencode ( $str ), $revert );
	}
	
	private function addslashes($str) {
		$pattern = <<<PARAM
/\'/g
PARAM;
		$pattern = trim($pattern);
		$sub = <<<PARAM
\\\'
PARAM;
		$sub = trim($sub);
		return preg_replace($pattern, $sub, $str);
	}
	
	private function escapebackslashes($str) {
		$pattern = <<<PARAM
/\\/g
PARAM;
		$pattern = trim($pattern);
		$sub = <<<PARAM
\\\\
PARAM;
		$sub = trim($sub);
		return preg_replace($pattern, $sub, $str);
	}
	
	private function encodeInput ( $str ) {
		return $this->encodeURIComponent($this->addslashes($this->escapebackslashes($str)));
	}
}

if ( !function_exists('lcfirst') ) {
	function lcfirst($str) {
		return strtolower(substr($str, 0, 1)).substr($str, 1);
	}
}
