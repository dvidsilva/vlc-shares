<?php 

class X_VlcShares_Plugins_Helper_Hoster_Own3dLive implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'own3dlive';
	const PATTERN = '/http\:\/\/((www\.)?)own3d\.tv\/((#\/)?)live\/(?P<ID>[0-9]+)/';
	
	const SOURCE_URL = 'http://www.own3d.tv/livecfg/%s?autoPlay=true';
	
	private $params = array(
		'quality' => '2',
		'base' => '${cdn2}'
	);
	
	function __construct($configs = array()) {
		if ( is_array($configs) ) {
			$this->params = array_merge($this->params, $configs);
		}
	}
	
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
		
		$client = new Zend_Http_Client(sprintf(self::SOURCE_URL, $url),	array(
				'headers' => array(
					'User-Agent' => "vlc-shares/".X_VlcShares::VERSION." own3d/".X_VlcShares_Plugins_Own3d::VERSION
				)
			)
		);
		
		$xml = $client->request()->getBody();
		
		$dom = simplexml_load_string($xml);
		
		
		$thumbnail = trim((string) $dom->player[0]->thumb[0]);
		$title = trim((string) $dom->channels[0]->channel[0]['name']);
		$description = trim((string) $dom->channels[0]->channel[0]['description']);
		
		$item = null;
		
		foreach ($dom->channels[0]->channel[0]->clip[0]->item as $f_item) {
			if ( $f_item['base'] == $this->params['base'] ) {
				$item = $f_item;
				break;
			}
		}
		
		if ( is_null($item) ) {
			X_Debug::e("This live channel has no {$this->params['base']} link");
			throw new Exception("This live channel has no {$this->params['base']} link", self::E_ID_NOTFOUND);
		}
		
		$stream = null;
		foreach ( $item->stream as $f_stream ) {
			if ( $f_stream['quality'] != $this->params['quality'] && !is_null($stream)  ) {
				// always set the first stream type
				continue;
			}

			$playpath = (string) $f_stream['name'];
			$app = '';
			if ( strpos($playpath, '?' ) !== false ) {
				$app = substr($playpath, strpos($playpath, '?' ) + 1 );
			}
			
			$stream = X_RtmpDump::buildUri(array(
				'rtmp' => "rtmp://owned.fc.llnwd.net:1935/owned?$app/$playpath",
				'live' => true
			));
			
			if ( $f_stream['quality'] == $this->params['quality'] ) {
				break;
			}
		}
		
		if ( is_null($stream) ) {
			X_Debug::e("No valid rtmp stream found");
			throw new Exception("No valid rtmp stream found", self::E_ID_NOTFOUND);
		}
		
		
		return array(
			'title' => $title,
			'description' => $description,
			'thumbnail' => $thumbnail,
			'url' => $stream,
			'length' => 0
		);
	}
	
	function getHosterUrl($playableId) {
		return "http://www.own3d.tv/live/$playableId";
	}
	
	
}