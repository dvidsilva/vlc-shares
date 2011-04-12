<?php

require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'Zend/Dom/Query.php';


/**
 * Add GoGoCinema site as a video source
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_GoGoCinema extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const VERSION = '0.2';
	const VERSION_CLEAN = '0.2';
	
	const SORT_ALPHA = 'a';
	const SORT_YEAR = 'y';
	const SORT_TYPE = 't';
	const SORT_SEARCH = 's';
	
	const TYPE_MEGAVIDEO = 'm';
	const TYPE_YOUTUBE = 'y';
	
	public function __construct() {
		$this->setPriority('gen_beforeInit')
			->setPriority('getCollectionsItems')
			->setPriority('preRegisterVlcArgs')
			->setPriority('getShareItems')
			->setPriority('preGetModeItems')
			->setPriority('getIndexManageLinks')
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
	 * Add the main link for jigoku
	 * @param Zend_Controller_Action $controller
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");
		
		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_gogocinema_collectionindex'));
		$link->setIcon('/images/gogocinema/logo.png')
			->setDescription(X_Env::_('p_gogocinema_collectionindex_desc'))
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
		
		//$baseUrl = $this->config('base.url', 'http://www.gogocinema.net/mediacenter/index.php?page=ajax_show_folder&id=');
		
		$items = new X_Page_ItemList_PItem();
		
		X_Debug::i("Requested location: $location");
		
		// location format:
		// sortType/subType/page/thread/linkType:linkId
		
		$split = $location != '' ? @explode('/', $location, 5) : array();
		@list($sortType, $subType, $page, $thread, $linkTypeId) = $split;
		
		X_Debug::i("Exploded location: ".var_export($split, true));

		
		// special case: if sortType = search the subType param can be in
		// gogocinema:search
		if ( $sortType == self::SORT_SEARCH && is_null($subType) ) {
			$this->disableCache();
			$searchValue = $controller->getRequest()->getParam("{$this->getId()}:search", null);
			if ( $searchValue == null ) {
				// go back in index
				$split = array();
			} else {
				$subType = $searchValue;
				$split[1] = $searchValue; // in this way sort search doesn't go in classification
			}
		}
		
		switch ( count($split) ) {
			
			case 5:
				// we shouldn't be here!
			case 4:
				
				$this->_fetchVideos($items, $sortType, $subType, $page, $thread);
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
			$link = new X_Page_Item_PItem('core-directwatch', X_Env::_('p_gogocinema_watchdirectly'));
			$link->setIcon('/images/icons/play.png')
				->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
				->setLink($url);
			return new X_Page_ItemList_PItem(array($link));
		} else {
			// if there is no link, i have to remove start-vlc button
			// and replace it with a Warning button
			
			X_Debug::i('Setting priority to filterModeItems');
			$this->setPriority('filterModeItems', 99);
			
			$link = new X_Page_Item_PItem('megavideo-warning', X_Env::_('p_gogocinema_invalidlink'));
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

		$split = $location != '' ? @explode('/', $location, 5) : array();
		@list($sortType, $subType, $page, $thread, $linkTypeId) = $split;
		
		@list($videoType, $videoId) = @explode(':', $linkTypeId, 2);
		
		//@list($letter, $thread, $href) = explode('/', $location, 3);

		X_Debug::i("SortType: $sortType, SubType: $subType, Page: $page, Thread: $thread, VType: $videoType, VID: $videoId");
		
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
					
					// fetch megavideo ?d= links
					@list($mvType, $mvId) = @explode('=', $videoId);

					if ( $mvType == 'd') {
						
						// convert megaupload->megavideo id in megavideo id
						
						$http = new Zend_Http_Client("http://www.megavideo.com/?d=$mvId");
						$response = $http->request();
						
						$body = $response->getBody();
						$matches = array();
						if ( preg_match('/flashvars\.v \= \"([^\"]*)\";/', $body, $matches) ) {
							$videoId = $matches[1];
						} else {
							// conversion failed
							break;	
						}
						
					} elseif ($mvType == 'v' ) {
						$videoId = $mvId;	
					}
					
					X_Debug::i("Megavideo ID: $videoId");
					if ( $megavideoHelper->setLocation($videoId)->getServer() ) {
						$return = $megavideoHelper->getUrl();
					}
				} catch (Exception $e) {
					X_Debug::e("Megavideo helper isn't installed or enabled: {$e->getMessage()}");
				}
				break;
				
			case self::TYPE_YOUTUBE:
				try {
					/* @var $youtubeHelper X_VlcShares_Plugins_Helper_Youtube */
					$youtubeHelper = $this->helpers('youtube');
					/* @var $youtubePlugin X_VlcShares_Plugins_Youtube */
					$youtubePlugin = X_VlcShares_Plugins::broker()->getPlugins('youtube');

					X_Debug::i("Youtube ID: $videoId");					
					
					// THIS CODE HAVE TO BE MOVED IN YOUTUBE HELPER
					// FIXME
					
					$formats = $youtubeHelper->getFormatsNOAPI($videoId/*->getVideoId()*/);
				
					$returned = null;
					
					$qualityPriority = explode('|', $youtubePlugin->config('quality.priority', '5|34|18|35'));
					
					foreach ($qualityPriority as $quality) {
						if ( array_key_exists($quality, $formats)) {
							$returned = $formats[$quality];
							X_Debug::i('Video format selected: '.$quality);
							break;
						}
					}
					if ( $returned === null ) {
						// for valid video id but video with restrictions
						// alternatives formats can't be fetched by youtube page.
						// i have to fallback to standard api url
						$apiVideo = $youtubeHelper->getVideo($videoId);
						
						foreach ($apiVideo->mediaGroup->content as $content) {
							if ($content->type === "video/3gpp") {
								$returned = $content->url;
								X_Debug::w('Content restricted video, fallback to api url:'.$returned);
								break;
							}
						}
	
						if ( $returned === null ) {
							$returned = false;
						}
					}					
					
					$return = $returned;
					
				} catch (Exception $e) {
					X_Debug::e("Youtube helper isn't installed or enabled: {$e->getMessage()}");
				}
				break;
			default:
				X_Debug::i("Using new hoster api");
				try {
					$hoster = $this->helpers()->hoster()->getHoster($videoType);
					$return = $hoster->getPlayable($videoId);
				} catch (Exception $e) {
					X_Debug::e("Hoster api hasn't a valid handler for {{$videoType}:{$videoId}}: {$e->getMessage()}");
					$return = false;
				}
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
		
		//X_Debug::i(var_export($exploded, true));
		// sort/sub/page/thread/video
		if ( count($exploded) == 3 ) {
			// i have to add an extra pop
			// to jump from threads to sub page
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

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_gogocinema_mlink'));
		$link->setTitle(X_Env::_('p_gogocinema_managetitle'))
			->setIcon('/images/gogocinema/logo.png')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'gogocinema'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}
	
	private function _fetchClassification(X_Page_ItemList_PItem $items, $sortType) {
		
		$cType = array();
		
		
		
		switch ( $sortType ) {
			case self::SORT_TYPE:
				$url = $this->config('index.url', 'http://www.gogocinema.com/index.php');
				$pattern = '/<a href=\"disp_genre\.php\?genre\=([^\"]+)\"\>/';
				break;
				
			case self::SORT_YEAR:
				$url = $this->config('index.url', 'http://www.gogocinema.com/index.php');
				$pattern = '/<a style\=\"color\:\#993300\;\" href=\"disp_year\.php\?year\=([^\"]+)\"\>/';
				break;
				
			case self::SORT_ALPHA:
				$url = null;		
				$lets = strtoupper('a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z');
				$cType = explode(',', $lets);
				break;
		}
		
		if ( $url !== null ) {
			// fetch items
			$page = $this->_loadPage($url);
			if ( preg_match_all($pattern, $page, $cType) !== false ) {
				$cType = @$cType[1];
				X_Debug::i("Classification items for $sortType found: ".var_export($cType, true));
			} else {
				X_Debug::e("Regex failed for pattern {{$pattern}}");
			}
		}
		
		foreach ( $cType as $type ) {
			$item = new X_Page_Item_PItem($this->getId()."-$sortType-$type", $type);
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
			self::SORT_ALPHA => X_Env::_('p_gogocinema_sort_alpha'),
			self::SORT_TYPE => X_Env::_('p_gogocinema_sort_type'),
			self::SORT_YEAR => X_Env::_('p_gogocinema_sort_year'),
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

		if ( $this->helpers()->devices()->isWiimc() ) {
		
			$item = new X_Page_Item_PItem($this->getId()."-sort-".self::SORT_SEARCH, X_Env::_('p_gogocinema_sort_search'));
			$item->setIcon('/images/gogocinema/search.png')
				->setType(X_Page_Item_PItem::TYPE_REQUEST)
				->setCustom(__CLASS__.':location', self::SORT_SEARCH)
				->setDescription(APPLICATION_ENV == 'development' ? self::SORT_SEARCH : null)
				->setLink(array(
					'l'	=>	X_Env::encode(self::SORT_SEARCH),
					// search key have to be the last one or wiimc will append the string to something to unknown
					"{$this->getId()}:search" => ''
				), 'default', false);
				
			$items->append($item);
		}
	
	}
	
	
	
	private function _fetchThreads(X_Page_ItemList_PItem $items, $sortType, $subType, $page = 1) {
		
		X_Debug::i("Fetching threads for $sortType/$subType/$page");
		
		switch ( $sortType ) {
			
			case self::SORT_ALPHA:
				// subType = letter
				$url = $this->config('index.alpha.url', "http://www.gogocinema.com/disp_abcd.php?letter=" ).$subType;
				break;
			case self::SORT_YEAR:
				// subType = year
				$url = $this->config('index.year.url', "http://www.gogocinema.com/disp_year.php?year=" ).$subType;
				break;
			case self::SORT_TYPE:
				// subType = year
				$url = $this->config('index.type.url', "http://www.gogocinema.com/disp_genre.php?genre=" ).$subType;
				break;
			case self::SORT_SEARCH:
				// subType = year
				$url = $this->config('index.search.url', "http://www.gogocinema.com/disp_search.php?search=" ).urlencode($subType);
				break;
			
		}
		
		if ( $page > 1 ) {
			// adding page param
			$url .= "&page=$page";
		}
		
		$htmlString = $this->_loadPage($url);

		$pattern = '/<a href\=\"movie\.php\?movie_id\=([^\"]+)\" style\=\"color\:\#E95C24\;\"><img src\=\"([^\"]+)\" width\=\"68\" height\=\"100\" alt\=\"Watch ([^\"]+) Online\"/';
		$patternNext = '/<span class\=\"disabled\"\>Next\<\/span\>/';
		$patternPrevious = '/<span class\=\"disabled\"\>Previous\<\/span\>/';
		$patternPagination = '/<div class\=\"pagination\"\>/';

		
		
		$matches = array();
		if ( preg_match_all($pattern, $htmlString, $matches, PREG_SET_ORDER) ) {
			X_Debug::i("Threads found: ".count($matches));
			
			$paginationEnabled = (preg_match($patternPagination, $htmlString) ? true : false);
			
			if ( $paginationEnabled && !preg_match($patternPrevious, $htmlString, $submatch) ) {
				
				//X_Debug::i("Previous page allowed: ".var_export($submatch, true));
				
				$ppage = $page > 2 ? "/".($page - 1) : '';

				$item = new X_Page_Item_PItem($this->getId()."-previouspage", X_Env::_('p_gogocinema_previouspage', ($page - 1)) );
				$item//->setIcon('/images/icons/folder_32.png')
					//->setThumbnail($thumbnail)
					->setType(X_Page_Item_PItem::TYPE_CONTAINER)
					->setCustom(__CLASS__.':location', "$sortType/$subType$ppage")
					->setLink(array(
						// if needed / is already in $ppage
						'l'	=>	X_Env::encode("$sortType/$subType$ppage")
					), 'default', false);
					
				if ( APPLICATION_ENV == 'development' ) {
					$item->setDescription("$sortType/$subType$ppage");
				}
				
				$items->append($item);
				
				
			}
			
			
			foreach ($matches as $thread) {
				
				@list(, $threadId, $thumbnail, $label) = $thread; 
				
				$item = new X_Page_Item_PItem($this->getId()."-$sortType-$subType-$threadId", $label);
				$item->setIcon('/images/icons/folder_32.png')
					->setThumbnail($thumbnail)
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

			if ( $paginationEnabled &&  !preg_match($patternNext, $htmlString, $submatch) ) {

				//X_Debug::i("Next page allowed: ".var_export($submatch, true));
				
				$item = new X_Page_Item_PItem($this->getId()."-nextpage", X_Env::_('p_gogocinema_nextpage', ($page + 1)) );
				$item//->setIcon('/images/icons/folder_32.png')
					//->setThumbnail($thumbnail)
					->setType(X_Page_Item_PItem::TYPE_CONTAINER)
					->setCustom(__CLASS__.':location', "$sortType/$subType/".($page + 1))
					->setLink(array(
						'l'	=>	X_Env::encode("$sortType/$subType/".($page + 1))
					), 'default', false);
					
				if ( APPLICATION_ENV == 'development' ) {
					$item->setDescription("$sortType/$subType/".($page + 1));
				}
				
				$items->append($item);
				
				
			}
			
		} else {
			X_Debug::e("Regex failed {{$pattern}}");
		}
		
	}	
	
	
	private function _fetchVideos(X_Page_ItemList_PItem $items, $sortType, $subType, $page, $thread) {
		
		X_Debug::i("Fetching videos for $sortType/$subType/$page/$thread");
		
		$url = $this->config('index.video.url', 'http://www.gogocinema.com/movie.php?movie_id=').$thread;
		
		$htmlString = $this->_loadPage($url);
		
		$htmlString = str_replace(array("\n", "\r", "\t", chr(0xC2), chr(0xA0), chr(157)), '', $htmlString);
		
		//X_Debug::i($htmlString);
		
		//$megavideoPattern = '/href\=\"http:\/\/megavideo\.com\/\?([^\"]+)\" /';
		//$megavideoPattern = '/<tr bgcolor\=\"#043F6E\"><td>\# ([^\"]+)\<\/td><\/tr><tr><td align\=\"center\"><a style\=\"color\:\#FFFFFF\;\" href\=\"http:\/\/megavideo\.com\/\?([^\"]+)\" target\=\"\_blank\">/';
		//$youtubePattern = '/<tr bgcolor\=\"#043F6E\"><td>\# ([^\"]+)\<\/td><\/tr><tr><td align\=\"center\"><a style\=\"color\:\#FFFFFF\;\" href\=\"http\:\/\/youtube\.com\/watch\?v\=([^\"]+)\" target\=\"\_blank\">/';
		$globalPattern = '/<tr bgcolor\=\"#043F6E\"><td>\# (?P<label>[^\"]+)\<\/td><\/tr><tr><td align\=\"center\"><a style\=\"color\:\#FFFFFF\;\" href\=\"(?P<link>[^\"]+)\" target\=\"\_blank\">/';

		$movietitlePattern = '/<td class\=\"movietitle\" colspan \= \"3\"><a href\=\"movie\.php\?movie_id=([^\"]+)\" style=\"color\:\#E95C24\;\">([^\"]+)<\/a>/';
		
		$matches = array();
		if ( preg_match($movietitlePattern, $htmlString, $matches) ) {
			$movieTitle = "{$matches[2]}: ";
		} else {
			$movieTitle = '';
		}
		
		$matches = array();
		if ( preg_match_all($globalPattern, $htmlString, $matches, PREG_SET_ORDER ) ) {
			X_Debug::i("Megavideo videos found: ".count($matches));
			
			X_Debug::i(var_export($matches, true));
			
			
			foreach ($matches as $video) {
				
				//@list(,$label, $videoId) = $video;
				$label = $video['label'];
				$link = $video['link'];
				
				try {

					$hoster = $this->helpers()->hoster()->findHoster($link);
					
					$label = strip_tags($label). " [".ucfirst($hoster->getId())."]";
					//$videoId = self::TYPE_MEGAVIDEO.":$videoId";
					
					$videoId = "{$hoster->getId()}:{$hoster->getResourceId($link)}";
					
					$item = new X_Page_Item_PItem($this->getId()."-{$hoster->getId()}", "$movieTitle$label");
					$item->setIcon('/images/icons/file_32.png')
						->setType(X_Page_Item_PItem::TYPE_ELEMENT)
						->setCustom(__CLASS__.':location', "$sortType/$subType/$page/$thread/$videoId")
						->setLink(array(
							'action'	=> 'mode',
							'l'	=>	X_Env::encode("$sortType/$subType/$page/$thread/$videoId")
						), 'default', false);
						
					if ( APPLICATION_ENV == 'development' ) {
						$item->setDescription("$sortType/$subType/$page/$thread/$videoId");
					}
						
					$items->append($item);
					
				} catch (Exception $e) {
					// no valid hoster for this link
				}
				
			}
			
		} else {
			X_Debug::i("Megavideo videos NOT found: ".var_export($matches, true));
			X_Debug::i("No megavideo links found");
		}
		
		/*
		$matches = array();
		if ( preg_match_all($youtubePattern, $htmlString, $matches, PREG_SET_ORDER) ) {
			X_Debug::i("Youtube videos found: ".count($matches));
			
			foreach ($matches as $video) {
				
				@list(,$label,$videoId) = $video;
				
				$label = strip_tags($label);
				$videoId = self::TYPE_YOUTUBE.":$videoId";
				
				$item = new X_Page_Item_PItem($this->getId()."-youtube", "$movieTitle$label");
				$item->setIcon('/images/icons/file_32.png')
					->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setCustom(__CLASS__.':location', "$sortType/$subType/$page/$thread/$videoId")
					->setLink(array(
						'action'	=> 'mode',
						'l'	=>	X_Env::encode("$sortType/$subType/$page/$thread/$videoId")
					), 'default', false);
					
				if ( APPLICATION_ENV == 'development' ) {
					$item->setDescription("$sortType/$subType/$page/$thread/$videoId");
				}
					
				$items->append($item);
				
			}
			
		}
		*/

	}
	
	
	private function _loadPage($uri) {

		X_Debug::i("Loading page $uri");
		
		$http = new Zend_Http_Client($uri, array(
			'maxredirects'	=> $this->config('request.maxredirects', 10),
			'timeout'		=> $this->config('request.timeout', 25)
			//'keepalive' => true
		));
		
		$http->setHeaders(array(
			$this->config('hide.useragent', false) ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION .' gogocinema/'.self::VERSION_CLEAN : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1',
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
