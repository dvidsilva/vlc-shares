<?php


/**
 * Add IceFilms site as a video source
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_IceFilms extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const VERSION = '0.1';
	const VERSION_CLEAN = '0.1';
	
	const SORT_MOVIES = 'mov';
	const SORT_TVSHOWS = 'tvs';
	const SORT_OTHER = 'oth';
	const SORT_MUSIC = 'mus';
	const SORT_STANDUP = 'sdu';
	
	const TYPE_MEGAVIDEO = 'mv';
	const TYPE_MEGAUPLOAD = 'mu';
	
	public function __construct() {
		$this->setPriority('gen_beforeInit')
			->setPriority('getCollectionsItems')
			->setPriority('preRegisterVlcArgs')
			->setPriority('getShareItems')
			->setPriority('preGetModeItems')
			->setPriority('getIndexManageLinks')
			->setPriority('getIndexMessages')
			;
	}
	
	/**
	 * Inizialize translator for this plugin
	 * @param Zend_Controller_Action $controller
	 */
	function gen_beforeInit(Zend_Controller_Action $controller) {
		$this->helpers()->language()->addTranslation(__CLASS__);
	}
	
	/**
	 * Add the main link for icefilms
	 * @param Zend_Controller_Action $controller
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");
		
		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_icefilms_collectionindex'));
		$link->setIcon('/images/icefilms/logo.png')
			->setDescription(X_Env::_('p_icefilms_collectionindex_desc'))
			->setType(X_Page_Item_PItem::TYPE_CONTAINER)
			->setLink(
				array(
					'controller' => 'browse',
					'action' => 'share',
					'p' => $this->getId(),
				), 'default', true
			);
		return new X_Page_ItemList_PItem(array($link));
	}
	
	/**
	 * Get category/video list
	 * @param unknown_type $provider
	 * @param unknown_type $location
	 * @param Zend_Controller_Action $controller
	 */
	public function getShareItems($provider, $location, Zend_Controller_Action $controller) {
		
		if ( $provider != $this->getId() ) return;
		
		X_Debug::i('Plugin triggered');
		
		X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_SortItems');
		
		$items = new X_Page_ItemList_PItem();
		
		X_Debug::i("Requested location: $location");
		
		// location format:
		// sortType/subType/page/thread/linkType:linkId
		
		$split = $location != '' ? @explode('/', $location, 6) : array();
		@list($sortType, $subType, $page, $series, $thread, $linkTypeId) = $split;
		
		X_Debug::i("Exploded location: ".var_export($split, true));
		
		
		switch ( count($split) ) {
			case 6:
				// we shouldn't be here!
			case 5:
				$this->_fetchVideos($items, $sortType, $subType, $page, $series, $thread);
				break;
			case 4:
				$this->_fetchEpisodes($items, $sortType, $subType, $page, $series);
				break;
			case 2:
				$page = 1;
			case 3:
				$this->_fetchThreads($items, $sortType, $subType, $page);
				break;
				
			case 1:
				$this->_fetchClassification($items, $sortType);
				break;
			
			case 0:
			default: 
				$this->disableCache();
				$this->_fetchSortType($items);
			
		}
				
		return $items;
		
	}
	
	/**
	 * This hook can be used to add low priority args in vlc stack
	 * 
	 * @param X_Vlc $vlc vlc wrapper object
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to stream
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 */
	public function preRegisterVlcArgs(X_Vlc $vlc, $provider, $location, Zend_Controller_Action $controller) {
	
		// this plugin inject params only if this is the provider
		if ( $provider != $this->getId() ) return;

		// i need to register source as first, because subtitles plugin use source
		// for create subfile
		
		X_Debug::i('Plugin triggered');
		
		$location = $this->resolveLocation($location);
		
		if ( $location !== null ) {
			// TODO adapt to newer api when ready
			$vlc->registerArg('source', "\"$location\"");			
		} else {
			X_Debug::e("No source o_O");
		}
	
	}
	
	/**
	 *	Add button -watch megavideo stream directly-
	 * 
	 * @param string $provider
	 * @param string $location
	 * @param Zend_Controller_Action $controller
	 */
	public function preGetModeItems($provider, $location, Zend_Controller_Action $controller) {

		if ( $provider != $this->getId()) return;
		
		X_Debug::i("Plugin triggered");
		
		$url = $this->resolveLocation($location);
		
		if ( $url ) {
			$link = new X_Page_Item_PItem('core-directwatch', X_Env::_('p_icefilms_watchdirectly'));
			$link->setIcon('/images/icons/play.png')
				->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
				->setLink($url);
			return new X_Page_ItemList_PItem(array($link));
		} else {
			// if there is no link, i have to remove start-vlc button
			// and replace it with a Warning button
			
			X_Debug::i('Setting priority to filterModeItems');
			$this->setPriority('filterModeItems', 99);
			
			$link = new X_Page_Item_PItem('megavideo-warning', X_Env::_('p_icefilms_invalidlink'));
			$link->setIcon('/images/msg_error.png')
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(array (
					'controller' => 'browse',
					'action' => 'share',
					'p'	=> $this->getId(),
					'l' => X_Env::encode($this->getParentLocation($location)),
				), 'default', true);
			return new X_Page_ItemList_PItem(array($link));

		}
	}
	
	/**
	 * Remove vlc-play button if location is invalid
	 * @param X_Page_Item_PItem $item,
	 * @param string $provider
	 * @param Zend_Controller_Action $controller
	 */
	public function filterModeItems(X_Page_Item_PItem $item, $provider,Zend_Controller_Action $controller) {
		if ( $item->getKey() == 'core-play') {
			X_Debug::i('plugin triggered');
			X_Debug::w('core-play flagged as invalid because the link is invalid');
			return false;
		}
	}
	
	
	private $cachedLocation = array();
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::resolveLocation
	 * @param string $location
	 * @return string real address of a resource
	 */
	function resolveLocation($location = null) {
		if ( $location == '' || $location == null ) return false;
		
		if ( array_key_exists($location, $this->cachedLocation) ) {
			return $this->cachedLocation[$location];
		}
		
		X_Debug::i("Requested location: $location");

		$split = $location != '' ? @explode('/', $location, 6) : array();
		@list($sortType, $subType, $page, $serie, $thread, $linkTypeId) = $split;
		
		@list($videoType, $videoId) = @explode(':', $linkTypeId, 2);
		
		//@list($letter, $thread, $href) = explode('/', $location, 3);

		X_Debug::i("SortType: $sortType, SubType: $subType, Page: $page, Serie: $serie, Thread: $thread, VType: $videoType, VID: $videoId");
		
		if ( $videoType == null || $videoId == null ) {
			$this->cachedLocation[$location] = false;
			return false;	
		}

		$return = false;
		switch ($videoType) {
			
			case self::TYPE_MEGAVIDEO:
				try {
					/* @var $megavideoHelper X_VlcShares_Plugins_Helper_Megavideo */
					$megavideoHelper = $this->helpers('megavideo');
					
					X_Debug::i("Megavideo ID: $videoId");
					if ( $megavideoHelper->setLocation($videoId)->getServer() ) {
						$return = $megavideoHelper->getUrl();
					}
				} catch (Exception $e) {
					X_Debug::e("Megavideo helper isn't installed or enabled: {$e->getMessage()}");
				}
				break;
				
			case self::TYPE_MEGAUPLOAD:				
				try {
					/* @var $megauploadHelper X_VlcShares_Plugins_Helper_Megaupload */
					$megauploadHelper = $this->helpers('megaupload');
					
					X_Debug::i("Megaupload ID: $videoId");
					if ( $megauploadHelper->setMegauploadLocation($videoId)->getServer() ) {
						$return = $megauploadHelper->getUrl();
					}
				} catch (Exception $e) {
					X_Debug::e("Megaupload helper isn't installed or enabled: {$e->getMessage()}");
				}
				break;
		}
		
		$this->cachedLocation[$location] = $return;
		return $return;
		
		
	}
	
	/**
	 * Support for parent location
	 * @see X_VlcShares_Plugins_ResolverInterface::getParentLocation
	 * @param $location
	 */
	function getParentLocation($location = null) {
		if ( $location == '' || $location == null ) return false;
		
		//X_Debug::i($location);
		
		$exploded = explode('/', $location);
		
		if ( count($exploded) == 5  && $exploded[0] != self::SORT_TVSHOWS ) {
			// i have to add an extra pop
			// if series == null
			array_pop($exploded);
		}		
		
		//X_Debug::i(var_export($exploded, true));
		// sort/sub/page/serie/thread/video
		if ( count($exploded) == 3 ) {
			// i have to add an extra pop
			// to jump from threads to sub page
			array_pop($exploded);
		} 

		if ( count($exploded) == 2  && ( $exploded[0] == self::SORT_OTHER || $exploded[0] == self::SORT_STANDUP || $exploded[0] == self::SORT_MUSIC  ) ) {
			// i have to add an extra pop
			// if series == null
			array_pop($exploded);
		}		
		
		
		array_pop($exploded);
		
		//X_Debug::i(var_export($exploded, true));
		
		if ( count($exploded) >= 1 ) {
			return implode('/', $exploded);
		} else {
			return null;
		}			
		
	}
	
	
	/**
	 * Add the link for -manage-megavideo-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_icefilms_mlink'));
		$link->setTitle(X_Env::_('p_icefilms_managetitle'))
			->setIcon('/images/icefilms/logo.png')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'icefilms'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}
	
	/**
	 * Show an error message if megavideo plugin version < 0.2.1
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Message
	 */
	public function getIndexMessages(Zend_Controller_Action $controller) {
		
		try {
			$megauploadHelper = $this->helpers('megaupload');
		} catch (Exception $e) {
			X_Debug::i('Plugin triggered');
		
			$m = new X_Page_Item_Message($this->getId(), X_Env::_('p_icefilms_dashboardwarning'));
			$m->setType(X_Page_Item_Message::TYPE_ERROR);
			return new X_Page_ItemList_Message(array($m));
		}
	}
	
	
	private function _fetchClassification(X_Page_ItemList_PItem $items, $sortType) {
		
		$cType = array();
		$lets = strtoupper('1,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z');
		$cType = explode(',', $lets);
		
		foreach ( $cType as $type ) {
			$item = new X_Page_Item_PItem($this->getId()."-$sortType-$type", ($type == '1' ? '#' : $type));
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$sortType/$type")
				->setDescription(APPLICATION_ENV == 'development' ? "$sortType/$type" : null)
				->setLink(array(
					'l'	=>	X_Env::encode("$sortType/$type")
				), 'default', false);
				
			$items->append($item);
		}
	}

	private function _fetchSortType(X_Page_ItemList_PItem $items) {
		
		$sorts = array(
			self::SORT_MOVIES => X_Env::_('p_icefilms_sort_movies'),
			self::SORT_TVSHOWS => X_Env::_('p_icefilms_sort_tvshows'),
			self::SORT_OTHER."/1" => X_Env::_('p_icefilms_sort_other'),
			self::SORT_STANDUP."/1" => X_Env::_('p_icefilms_sort_standup'),
			self::SORT_MUSIC."/1" => X_Env::_('p_icefilms_sort_music'),
		);
		
		foreach ( $sorts as $sortL => $sortLabel ) {
			$item = new X_Page_Item_PItem($this->getId()."-sort-$sortL", $sortLabel);
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$sortL")
				->setDescription(APPLICATION_ENV == 'development' ? "$sortL" : null)
				->setLink(array(
					'l'	=>	X_Env::encode("$sortL")
				), 'default', false);
				
			$items->append($item);
		}
	
	}
	
	
	
	private function _fetchThreads(X_Page_ItemList_PItem $items, $sortType, $subType, $page = 1) {
		
		X_Debug::i("Fetching threads for $sortType/$subType/$page");
		
		switch ( $sortType ) {
			
			case self::SORT_MOVIES:
				// subType = letter
				$url = $this->config('index.movies.url', "http://www.icefilms.info/movies/a-z/" ).$subType;
				$pattern = '/<a href=\/ip\.php\?v\=([^\&]+)\&>([^\<]+)<\/a>/';
				break;
			case self::SORT_TVSHOWS:
				// subType = letter
				$url = $this->config('index.tvshows.url', "http://www.icefilms.info/tv/a-z/" ).$subType;
				$pattern = '/<a href=\/tv\/series\/([^\>]+)>([^\<]+)<\/a>([^\<]+)<br>/';
				break;
			case self::SORT_OTHER:
				// subType = letter
				$url = $this->config('index.other.url', "http://www.icefilms.info/other/a-z/" ).$subType;
				$pattern = '/<a href=\/ip\.php\?v\=([^\&]+)\&>([^\<]+)<\/a>/';
				break;
			case self::SORT_MUSIC:
				// subType = letter
				$url = $this->config('index.music.url', "http://www.icefilms.info/music/a-z/" ).$subType;
				$pattern = '/<a href=\/ip\.php\?v\=([^\&]+)\&>([^\<]+)<\/a>/';
				break;
			case self::SORT_STANDUP:
				// subType = letter
				$url = $this->config('index.standup.url', "http://www.icefilms.info/standup/a-z/" ).$subType;
				$pattern = '/<a href=\/ip\.php\?v\=([^\&]+)\&>([^\<]+)<\/a>/';
				break;
				
		}
		
		/*
		if ( $page > 1 ) {
			// adding page param
			$url .= "&page=$page";
		}
		*/
		
		$htmlString = $this->_loadPage($url);

		
		$matches = array();
		if ( preg_match_all($pattern, $htmlString, $matches, PREG_SET_ORDER) ) {
			X_Debug::i("Threads found: ".count($matches));
			X_Debug::i("Threads: ".var_export($matches, true));
			
			foreach ($matches as $thread) {
				
				if ( $sortType == self::SORT_TVSHOWS ) { 
					@list(, $threadId, $label, $episodes) = $thread;
					$threadId = str_replace('/', ':', $threadId);
					$label .= " [$episodes]";
				} else {
					@list(, $threadId, $label) = $thread;
					$threadId = "null/$threadId";
				} 
				
				$item = new X_Page_Item_PItem($this->getId()."-$sortType-$subType-$threadId", $label);
				$item->setIcon('/images/icons/folder_32.png')
					->setType(X_Page_Item_PItem::TYPE_CONTAINER)
					->setCustom(__CLASS__.':location', "$sortType/$subType/$page/$threadId")
					->setLink(array(
						'l'	=>	X_Env::encode("$sortType/$subType/$page/$threadId")
					), 'default', false);
					
				if ( APPLICATION_ENV == 'development' ) {
					$item->setDescription("$sortType/$subType/$page/$threadId");
				}
				
				$items->append($item);
				
			}
			
		} else {
			X_Debug::e("Regex failed {{$pattern}}");
		}
		
	}	
	
	
	private function _fetchEpisodes(X_Page_ItemList_PItem $items, $sortType, $subType, $page = 1, $serie) {
		
		X_Debug::i("Fetching episodes for $sortType/$subType/$page/$serie");
		
		$url = $this->config('index.episodes.url', "http://www.icefilms.info/tv/series/" ).str_replace(':','/', $serie);
		$pattern = '/<a href=\/ip\.php\?v\=([^\&]+)&>([^\<]+)<\/a>/';
		
		
		$htmlString = $this->_loadPage($url);

		
		$matches = array();
		if ( preg_match_all($pattern, $htmlString, $matches, PREG_SET_ORDER) ) {
			X_Debug::i("Threads found: ".count($matches));
			
			foreach ($matches as $thread) {
				
				@list(, $epId, $label) = $thread;
				//$threadId .= '/0';
				
				$item = new X_Page_Item_PItem($this->getId()."-$sortType-$subType-$serie-$epId", $label);
				$item->setIcon('/images/icons/folder_32.png')
					->setType(X_Page_Item_PItem::TYPE_CONTAINER)
					->setCustom(__CLASS__.':location', "$sortType/$subType/$page/$serie/$epId")
					->setLink(array(
						'l'	=>	X_Env::encode("$sortType/$subType/$page/$serie/$epId")
					), 'default', false);
					
				if ( APPLICATION_ENV == 'development' ) {
					$item->setDescription("$sortType/$subType/$page/$serie/$epId");
				}
				
				$items->append($item);
				
			}
			
		} else {
			X_Debug::e("Regex failed {{$pattern}}");
		}
		
	}	
		
	
	private function _fetchVideos(X_Page_ItemList_PItem $items, $sortType, $subType, $page, $serie, $thread) {
		
		X_Debug::i("Fetching videos for $sortType/$subType/$page/$serie/$thread");
		
		$url = $this->config('index.video.url', 'http://www.icefilms.info/membersonly/components/com_iceplayer/video.php?vid=').$thread;
		
		$htmlString = $this->_loadPage($url);
		
		//$htmlString = str_replace(array("\n", "\r", "\t", chr(0xC2), chr(0xA0), chr(157)), '', $htmlString);
		
		//X_Debug::i($htmlString);
		$catPattern = '/(*ANY)<div class\=ripdiv><b>([^\<]+)<\/b>/';
		$urlPatten = '/url\=http\:\/\/www.megaupload.com\/\?d\=([^\&]+)&([^\>]+)>([^\<]+)</';
		
		$matches = array();
		$links = array();
		if ( preg_match_all($catPattern, $htmlString, $matches, PREG_OFFSET_CAPTURE) ) {
			for ($i = 0; $i < count($matches[0]); $i++ ) {
				//$links[$matches[1][$i][0]] = array();
				$catLabel = $matches[1][$i][0];
				if ( array_key_exists($i+1, $matches[0]) ) {
					if ( !preg_match_all($urlPatten, substr($htmlString, $matches[0][$i][1], $matches[0][$i+1][1] - $matches[0][$i][1] ), $lMatches, PREG_SET_ORDER) ) {
						X_Debug::e("Pattern failure: {$urlPatten}");
						continue;
					}
				} else {
					if ( !preg_match_all($urlPatten, substr($htmlString, $matches[0][$i][1]), $lMatches, PREG_SET_ORDER) ) {
						X_Debug::e("Pattern failure: {$urlPatten}");
						continue;
					}
				}
				X_Debug::i(var_export($lMatches, true));
				
				foreach ($lMatches as $lm) {
					@list(, $muId, , $label) = $lm;
					
					$label = "$label ($catLabel) [Megaupload]";
					$videoId = self::TYPE_MEGAUPLOAD.":$muId";
					
					$item = new X_Page_Item_PItem($this->getId()."-megaupload", "$label");
					$item->setIcon('/images/icons/file_32.png')
						->setType(X_Page_Item_PItem::TYPE_ELEMENT)
						->setCustom(__CLASS__.':location', "$sortType/$subType/$page/$serie/$thread/$videoId")
						->setLink(array(
							'action'	=> 'mode',
							'l'	=>	X_Env::encode("$sortType/$subType/$page/$serie/$thread/$videoId")
						), 'default', false);
						
					if ( APPLICATION_ENV == 'development' ) {
						$item->setDescription("$sortType/$subType/$page/$serie/$thread/$videoId");
					}
						
					$items->append($item);
					
				}
				
			}
		} else {
			X_Debug::i("Pattern failure {$catPattern} or no video found");
		}

	}
	
	
	private function _loadPage($uri) {

		X_Debug::i("Loading page $uri");
		
		$http = new Zend_Http_Client($uri, array(
			'maxredirects'	=> $this->config('request.maxredirects', 10),
			'timeout'		=> $this->config('request.timeout', 25)
			//'keepalive' => true
		));
		
		$http->setHeaders(array(
			$this->config('hide.useragent', false) ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION .' icefilms/'.self::VERSION_CLEAN : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1',
			//'Content-Type: application/x-www-form-urlencoded'
		));
		
		$response = $http->request();
		$htmlString = $response->getBody();
		
		return $htmlString;
	}
	
	private function disableCache() {
		
		if ( X_VlcShares_Plugins::broker()->isRegistered('cache') ) {
			$cache = X_VlcShares_Plugins::broker()->getPlugins('cache');
			if ( method_exists($cache, 'setDoNotCache') ) {
				$cache->setDoNotCache();
			}
		}
		
	}
	
}


