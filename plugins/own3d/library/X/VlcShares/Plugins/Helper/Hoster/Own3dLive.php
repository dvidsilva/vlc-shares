<?php 

class X_VlcShares_Plugins_Helper_Hoster_Own3dLive implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'own3dlive';
	const PATTERN = '/http\:\/\/((www\.)?)own3d\.tv\/((#\/)?)live\/(?P<ID>[0-9]+)/';
	
	const SOURCE_URL = 'http://www.own3d.tv/livecfg/%s?autoPlay=true';
	
	const PLAYER_URL = 'http://static.ec.own3d.tv/player/Own3dPlayerV2_91.swf';
	
	private $cdns = array(
			'${cdn1}' => 'rtmp://fml.2010.edgecastcdn.net:1935/202010',
			'${cdn2}' => 'rtmp://owned.fc.llnwd.net/owned',
		);
	
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
		//return $infos['url'];
		return $infos['streams']['o_0_HD'];
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
		$pageUrl = trim((string) $dom->channels[0]->channel[0]['ownerLink']);
		
		$item = null;
		
		$streams = array();

		$other_i = 0;
		
		foreach ($dom->channels[0]->channel[0]->clip[0]->item as $item) {
			
			// check base and convert cdn url
			$cdnType = X_Env::startWith((string) $item['base'], '${cdn') ? substr((string) $item['base'], 5, 1) : "o_".$other_i++;
			
			foreach ($item->stream as $stream) {
				$label = (string) $stream['label'];
				$name = (string) $stream['name'];
				// remove the part before the ? if any
				if (strpos($name, '?') !== false) {
					$name = substr($name, strpos($name, '?') + 1);
				}
				
				$url = $this->getEngineUrl((string) $item['base'], $name, $pageUrl);
				
				$sKey = "{$cdnType}_{$label}";
				
				$streams[$sKey] = $url;
				
			}
		}
		
		X_Debug::i(print_r($streams, true));

		return array(
			'title' => $title,
			'description' => $description,
			'thumbnail' => $thumbnail,
			'streams' => $streams,
			'length' => 0
		);
	}
	
	public function getHosterUrl($playableId) {
		return "http://www.own3d.tv/live/$playableId";
	}
	
	private function convertCDNUrl($cdn) {
		if ( array_key_exists($cdn, $this->cdns ) ) {
			return $this->cdns[$cdn];
		} else {
			return $cdn;
		}
			
	}
	
	private function getEngineUrl($cdn, $playpath, $pageUrl) {
		
		$params = array();
		
		switch ($cdn) {
			case '${cdn1}':
			case '${cdn2}':
				$cdn = $this->convertCDNUrl($cdn);
				$url = "{$cdn}?{$playpath}";
				break;
				
			default:
				$url = $cdn;
				break;
		}
		
		$params['rtmp'] = $url;
		$params['playpath'] = $playpath;
		$params['live'] = true;
		$params['swfVfy'] = true;
		$params['swfUrl'] = self::PLAYER_URL;
		$params['pageUrl'] = $pageUrl;
		
		return X_RtmpDumpOwn3d::buildUri($params);
	}
	
	
}