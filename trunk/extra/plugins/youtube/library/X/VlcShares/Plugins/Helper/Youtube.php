<?php 

require_once 'X/VlcShares/Plugins/Helper/Abstract.php';
require_once 'Zend/Gdata/YouTube.php';

class X_VlcShares_Plugins_Helper_Youtube extends X_VlcShares_Plugins_Helper_Abstract {

	const VERSION = '0.2.1';
	const VERSION_CLEAN = '0.2.1';
	
	const ITEMS_PER_PAGE = 50;
	
	private $_cachedSearch = array();
	private $_location = null;
	/**
	 * @var 
	 */
	private $_fetched = false;
	
	/**
	 * @var Zend_Gdata_YouTube
	 */
	private $yt = null;
	
	
	public function __construct() {
		$this->yt = new Zend_Gdata_YouTube(
				null,
				'vlc-shares/'.X_VlcShares::VERSION.' youtube/'.self::VERSION, //client id
				null, // client api
				'AI39si4HbHoRBg1vlyLlRARR1Bl2TWqUy4LuCFHpS6ZnZ2LxlqbCLrgh8kDBj-7h2lkDs99cvaOZRm-4p-GlEP2rxtD6BZ9dcg' // dev key
			);
		$this->yt->setMajorProtocolVersion(2);
		$this->yt->getHttpClient()->setHeaders('User-Agent', 'vlc-shares/'.X_VlcShares::VERSION.' youtube/'.self::VERSION );
		$this->yt->setGzipEnabled(true);

	}
	
	
	/**
	 * get videos uploaded by username
	 * @param string $username
	 * @return Zend_Gdata_YouTube_VideoFeed 
	 */
	public function getVideosByUser($user, $page = 0) {
		
		/* @var $ytq Zend_Gdata_YouTube_VideoQuery */
		$ytq = $this->yt->newVideoQuery(Zend_Gdata_YouTube::USER_URI . '/' . $user . '/' .
                   Zend_Gdata_YouTube::UPLOADS_URI_SUFFIX);
        $page = $page * self::ITEMS_PER_PAGE;
		$ytq->setStartIndex($page == 0 ? $page : $page + 1);
		$ytq->setMaxResults(self::ITEMS_PER_PAGE);
		$ytq->setOrderBy('published');
		
		return $this->yt->getUserUploads(null , $ytq);
		
	}
	
	/**
	 * get videos favorited by username
	 * @param string $username
	 * @return Zend_Gdata_YouTube_VideoFeed 
	 */
	public function getFavoritesByUser($user, $page = 0) {
		/* @var $ytq Zend_Gdata_YouTube_VideoQuery */
		$ytq = $this->yt->newVideoQuery(Zend_Gdata_YouTube::USER_URI . '/' . $user . '/' .
                   Zend_Gdata_YouTube::FAVORITES_URI_SUFFIX);
        $page = $page * self::ITEMS_PER_PAGE;
		$ytq->setStartIndex($page == 0 ? $page : $page + 1);
		$ytq->setMaxResults(self::ITEMS_PER_PAGE);
		$ytq->setOrderBy('published');
		
		return $this->yt->getUserFavorites(null, $ytq);
	}
	
	/**
	 * get the list of subscription by a user
	 * @param username
	 * @return Zend_Gdata_YouTube_SubscriptionFeed
	 */
	public function getSubscriptionsByUser($user, $page = 0) {
		
		/* @var $ytq Zend_Gdata_YouTube_VideoQuery */
		$ytq = $this->yt->newVideoQuery(Zend_Gdata_YouTube::USER_URI . '/' . $user . '/subscriptions');
        $page = $page * self::ITEMS_PER_PAGE;
		$ytq->setStartIndex($page == 0 ? $page : $page + 1);
		$ytq->setMaxResults(self::ITEMS_PER_PAGE);
		$ytq->setOrderBy('published');
		
		return $this->yt->getSubscriptionFeed(null, $ytq);
	}

	/**
	 * get activities by a user
	 * @param username
	 * @return Zend_Gdata_YouTube_ActivityFeed
	 */
	public function getActivitiesByUser($user) {
		
		/* @var $ytq Zend_Gdata_YouTube_VideoQuery */
		//$ytq = $this->yt->newQuery(Zend_Gdata_YouTube::ACTIVITY_FEED_URI . '?author=' . $user);
		//$ytq->setStartIndex($page * self::ITEMS_PER_PAGE);
		//$ytq->setMaxResults(self::ITEMS_PER_PAGE);
		
		return $this->yt->getActivityForUser($user);
	}
	
	/**
	 * get playlists by a user
	 * @param username
	 * @return Zend_Gdata_YouTube_PlaylistListFeed
	 */
	public function getPlaylistsByUser($user, $page = 0) {
		
		/* @var $ytq Zend_Gdata_YouTube_VideoQuery */
		$ytq = $this->yt->newVideoQuery(Zend_Gdata_YouTube::USER_URI . '/' . $user . '/playlists');
		$page = $page * self::ITEMS_PER_PAGE;
		$ytq->setStartIndex($page == 0 ? $page : $page + 1);
		$ytq->setMaxResults(self::ITEMS_PER_PAGE);
		$ytq->setOrderBy('published');
				
		return $this->yt->getPlaylistListFeed(null, $ytq);
	}
	
	public function getVideosByPlaylist($playlistId, $page = 0) {
		
		/* @var $ytq Zend_Gdata_YouTube_VideoQuery */
		$ytq = $this->yt->newVideoQuery("http://gdata.youtube.com/feeds/api/playlists/".$playlistId);
		$page = $page * self::ITEMS_PER_PAGE;
		$ytq->setStartIndex($page == 0 ? $page : $page + 1);
		$ytq->setMaxResults(self::ITEMS_PER_PAGE);
		$ytq->setOrderBy('position');
		
		return $this->yt->getPlaylistVideoFeed($ytq);
		
	}
	
	/**
	 * Get VideoEntry from a videoId
	 * @param string $videoId
	 * @return Zend_Gdata_YouTube_VideoEntry
	 */
	public function getVideo($videoId) {
		
		return $this->yt->getVideoEntry($videoId);
		
	}
	
    /**
     * Retrieves a user's profile as an entry
     *
     * @param string $user The username of interest
     * @return Zend_Gdata_YouTube_UserProfileEntry The user profile entry
     */
	public function getAccountInfo($username) {
		return $this->yt->getUserProfile($username);
	}

	/**
	 * Get an array of subtitles in the format of
	 * 		languageCode => sub info
	 * Doesn't use Google GData api
	 * @param $videoId
	 * @return array 
	 */
	public function getSubtitlesNOAPI($videoId) {
		
		$this->setLocation($videoId)->fetchSubtitlesNOAPI();
		return $this->_fetched['subtitles'];
		
	}
	
	/**
	 * Get a sub info for the $languageCode.
	 * If the $languageCode sub isn't specified
	 * will throw an exception
	 * @param $videoId
	 * @param $languageCode
	 * @return string
	 * @throws Exception
	 */
	public function getSubtitleNOAPI($videoId, $languageCode) {
		
		$subs = $this->getSubtitlesNOAPI($videoId);
		if ( array_key_exists($languageCode, $subs)) {
			return $subs[$languageCode];
		} else {
			throw new Exception("There is no '$languageCode' subtitle");
		}
	}
	
	/**
	 * Get a list (array) of video source for the videoId.
	 * Array format is:
	 * 		$formatId => $streamUrl
	 * @param $videoId
	 * @return array
	 */
	public function getFormatsNOAPI($videoId) {
		
		$this->setLocation($videoId)->fetchFormatsNOAPI();
		
		return $this->_fetched['formats'];
		
	}
	
	
	protected function setLocation($videoId) {
		$this->_location = $videoId;
		if ( array_key_exists($videoId, $this->_cachedSearch) ) {
			$this->_fetched = $this->_cachedSearch[$videoId];
		} else {
			$this->_fetched = false;
		}
		return $this;
	}
	
	protected function fetchSubtitlesNOAPI() {
		
		if ( $this->_location == null || $this->_location == false ) {
			throw new Exception("Location value not setted");
		}
		// if there is no cached value
		if ( $this->_fetched === false || !array_key_exists( 'subtitles',  $this->_cachedSearch[$this->_location]) ) {
			if ( is_array($this->_fetched) ) {
				$toBeCached = $this->_fetched;
			} else {
				$toBeCached = array();
			}
			
			$uri = "http://video.google.com/timedtext?type=list&v=".$this->_location;
			
			$http = $this->getHttpClient($uri);
						
			$response = $http->request();
			
			$xmlString = $response->getBody();

			$doc = new Zend_Dom_Query($xmlString);
			
			$results = $doc->queryXpath('//track');

			$subs = array();
			
			while ( $results->valid() ) {
				
				$current = $results->current();
				
				// <track id="0" name="" lang_code="en" lang_original="English" lang_translated="English" lang_default="true"/>
				
				$sub = array(
					'id' => $current->getAttribute('id'),
					'name' => $current->getAttribute('name'),
					'lang_code' => $current->getAttribute('lang_code'),
					'lang_original' => $current->getAttribute('lang_original'),
					'lang_translated' => $current->getAttribute('lang_translated'),
					'lang_default' => $current->getAttribute('lang_default'),
				);
				
				$sub['xml_url'] = 'http://video.google.com/timedtext?type=track&' 
					.'lang='.urlencode(utf8_encode($sub['lang_code'])).'&'
					.'name='.urlencode(utf8_encode($sub['name'])).'&'
					.'v='.$this->_location;
				//        "name=" + URLEncoder.encode(this.name, "UTF-8") +
                //        "&lang=" + URLEncoder.encode(this.lang, "UTF-8") +
                //        "&v=" + URLEncoder.encode(this.id, "UTF-8");
				
				$sub['srt_url'] = array(
					'controller' 	=> 'youtube',
					'action'		=> 'convert',
					//'n'			=> X_Env::encode(utf8_encode($sub['name'])),
					'l'				=> X_Env::encode(utf8_encode($sub['lang_code'])),
					//'id'			=> $sub['id'],
					'v'				=> $this->_location,
					'f'		=> 'file.srt',
				); 
					
				$subs[$sub['lang_code']] = $sub;
				
				$results->next();
			}
			
			
			//X_Debug::i("Preg digest: ".print_r($matches, true));
			
			
			//X_Debug::i("Subs found: ".print_r($subs, true));
			
			$toBeCached['subtitles'] = $subs;
			
			$this->_fetched = $toBeCached;
			
			$this->_cachedSearch[$this->_location] = $toBeCached;
			
		}
		
		return $this;
		
	}
	
	protected function fetchFormatsNOAPI() {
		if ( $this->_location == null || $this->_location == false ) {
			throw new Exception("Location value not setted");
		}
		// if there is no cached value
		if ( $this->_fetched === false || !array_key_exists( 'formats',  $this->_cachedSearch[$this->_location]) ) {
			if ( is_array($this->_fetched) ) {
				$toBeCached = $this->_fetched;
			} else {
				$toBeCached = array();
			}
			
			$uri = "http://www.youtube.com/watch?v=".$this->_location;
			
			$http = $this->getHttpClient($uri);
						
			$response = $http->request();
			
			$htmlString = $response->getBody();
			
			//$start = strpos($htmlString, 'swfHTML');
			$start = strpos($htmlString, 'url_encoded_fmt_stream_map=');
			
			if ( $start !== false) {
				// reduce string (for old page format)
				//$htmlString = substr($htmlString, $start, 16384); // 16384 has been taken from wiimc menu.ccp
				$end = strpos($htmlString, '&amp;watermark', $start);
				if ( $end !== false ) {
					$htmlString = substr($htmlString, $start, $end);
				} else {
					$htmlString = substr($htmlString, $start, 16384); // 16384 has been taken from wiimc menu.ccp
				}
			}
			
			$matches = array();
			
			//preg_match_all('/(\\d+)%7C(http.*?)(%2C|&|%7C%7C)/', $htmlString, $matches, PREG_SET_ORDER);
			
			preg_match_all('/url%3D(?P<url>.*?)%26quality(.*?)%26itag%3D(?P<fmt>\\d+)%2C/', $htmlString, $matches, PREG_SET_ORDER);
			
			//X_Debug::i("Preg digest: ".print_r($matches, true));
			
			$formats = array();
			
			foreach ($matches as $match) {
				//@list(, $format, $link,  ) = $match;
				$format = $match['fmt'];
				$link = $match['url'];
				if ( !array_key_exists($format, $formats) ) {
					$formats[$format] = urldecode(urldecode(urldecode($link)));
				}
			}
			
			X_Debug::i("Links found: ".print_r($formats, true));
			
			$toBeCached['formats'] = $formats;
			
			$this->_fetched = $toBeCached;
			
			$this->_cachedSearch[$this->_location] = $toBeCached;
			
		}
		
		return $this;
	}
	
	/**
	 * @var Zend_Http_Client
	 */
	private $http = null;
	
	/**
	 * Factory of httpClient
	 * @param string $uri Http uri
	 * @param boolean $reset if create a new instance
	 * @return Zend_Http_Client
	 */
	public function getHttpClient($uri, $reset = false) {
		if ( $this->http === null || $reset ) {
			$this->http = new Zend_Http_Client($uri, array(
				'maxredirects'	=> 10, // hardcoded
				'timeout'		=> 20 // hardcoded
			));
			$this->http->setHeaders('User-Agent', 'vlc-shares/'.X_VlcShares::VERSION.' youtube/'.self::VERSION);
		} else {
			$this->http->setUri($uri);
		}
		return $this->http;
	}
	
}
