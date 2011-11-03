<?php 

class X_VlcShares_Plugins_FilmStream extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const VERSION = '0.2.1';
	const VERSION_CLEAN = '0.2.1';
	
	const TYPE_MOVIES = 'movies';
	const TYPE_TVSHOWS = 'tv';
	const TYPE_ANIME = 'anime';

	const URL_MOVIES_INDEX_AZ = 'http://film-stream.tv/film-list-a-z/%s/';
	const URL_TVSHOWS_INDEX_AZ = 'http://film-stream.tv/serietv/lista-tv/';
	const URL_ANIME_INDEX_AZ = 'http://film-stream.tv/anime/';
	
	const URL_MOVIES_INDEX_NEW = 'http://film-stream.tv/';
	const URL_TVSHOWS_INDEX_NEW = 'http://film-stream.tv/';
	const URL_ANIME_INDEX_NEW = 'http://film-stream.tv/';
	
	const URL_BASE = 'http://film-stream.tv/';
	
	const VIDEO_YOUTUBE = "yt";
	const VIDEO_MEGAVIDEO = "mv";
	const VIDEO_MEGAUPLOAD = "mu";
	
	protected $cachedLocation = array();
	
	function __construct() {
		$this->setPriority('getCollectionsItems');
		$this->setPriority('getShareItems');
		$this->setPriority('preGetModeItems');
		$this->setPriority('preRegisterVlcArgs');
		$this->setPriority('gen_beforeInit');
		$this->setPriority('getIndexManageLinks');
		$this->setPriority('getIndexMessages');
	}

	/**
	 * Inizialize translator for this plugin
	 * @param Zend_Controller_Action $controller
	 */
	function gen_beforeInit(Zend_Controller_Action $controller) {
		$this->helpers()->language()->addTranslation(__CLASS__);
	}
	
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::getLocation()
	 */
	function resolveLocation($location = null) {

		if ( $location == '' || $location == null ) return false;
		
		if ( array_key_exists($location, $this->cachedLocation) ) {
			return $this->cachedLocation[$location];
		}
		
		X_Debug::i("Requested location: $location");

		$split = $location != '' ? @explode('/', $location, 5) : array();
		@list($resourceType, $resourceGroup, $page, $resourceId, $videoId) = $split;

		// videoId overwritted by real videoId
		@list($videoType, $videoId) = @explode(':', $videoId, 2);

		X_Debug::i("Type: $resourceType, Group: $resourceGroup, Page: $page, Resource: $resourceId, VideoType: $videoType, VideoId: $videoId");
		
		if ( $videoType == null || $videoId == null ) {
			// location isn't a valid video url, so we return fals
			// and insert the query result in the cache
			$this->cachedLocation[$location] = false;
			return false;	
		}

		$return = false;
		switch ($videoType) {
			
			case self::VIDEO_MEGAVIDEO:
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
				
			case self::VIDEO_MEGAUPLOAD:				
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
			case self::VIDEO_YOUTUBE:
				try {
					/* @var $youtubeHelper X_VlcShares_Plugins_Helper_Youtube */
					$youtubeHelper = $this->helpers('youtube');
					/* @var $youtubePlugin X_VlcShares_Plugins_Youtube */
					$youtubePlugin = X_VlcShares_Plugins::broker()->getPlugins('youtube');
					
					X_Debug::i("Youtube ID: $videoId");

					// THIS CODE HAVE TO BE MOVED IN YOUTUBE HELPER
					// FIXME
					$formats = $youtubeHelper->getFormatsNOAPI($videoId);
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
					X_Debug::e("Youtube helper/plugin isn't installed or enabled: {$e->getMessage()}");
				}
				break;

			default:
				// new hoster helper
				try {
					$return = $this->helpers()->hoster()->getHoster($videoType)->getPlayable($videoId, true);
				} catch (Exception $e) {
					$return = false;
				}
				break;
				
				
		}
		
		$this->cachedLocation[$location] = $return;
		return $return;
	
	}
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::getParentLocation()
	 */
	function getParentLocation($location = null) {
		if ( $location == '' || $location == null ) return false;
		
		$exploded = explode('/', $location);
		
		// type/group/page/idres/idvideo
		if ( count($exploded) == 4 ) {
			// i have to add an extra pop
			// to jump from idres page to group page
			array_pop($exploded);
		} 
		
		array_pop($exploded);
		
		if ( count($exploded) >= 1 ) {
			return implode('/', $exploded);
		} else {
			return null;
		}			
	}
	
	/**
	 * Add the FilmStream link inside collection index
	 * @param Zend_Controller_Action $controller
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");
		
		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_filmstream_collectionindex'));
		$link->setIcon('/images/filmstream/logo.png')
			->setDescription(X_Env::_('p_filmstream_collectionindex_desc'))
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
	 * Fetch resources from filmstream site
	 * @param string $provider the plugin key of the one who should handle the request
	 * @param string $location the current $location
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return X_Page_ItemList_PItem
	 */
	public function getShareItems($provider, $location, Zend_Controller_Action $controller) {
		// this plugin fetch resources only if it's the provider
		if ( $provider != $this->getId() ) return;
		// add an info inside the debug log so we can trace this call 
		X_Debug::i('Plugin triggered');
		// disable automatic sorting, items will be already sorted in the target site
		X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_SortItems');
		// let's create the itemlist
		$items = new X_Page_ItemList_PItem();
		// show the requested location in the debug log
		// $location has been already decoded
		X_Debug::i("Requested location: $location");
		
		// location format:
		// resourceType/resourceGroup/page/resourceId/videoId
		
		$split = $location != '' ? @explode('/', $location, 5) : array();
		@list($resourceType, $resourceGroup, $page, $resourceId, $videoId) = $split;
		
		X_Debug::i("Exploded location: ".var_export($split, true));

		// Choose what to do based on the number of params
		// setted inside $location
		switch ( count($split) ) {
			case 5:
				// we shouldn't be here!
				// if we have 5 pieces (so even $videoId is setted)
				// we should be inside the browse/mode page
				// because the location is about a video URL
			case 4:
				// delegate to fetchVideos
				$this->_fetchVideos($items, $resourceType, $resourceGroup, $page, $resourceId);
				break;
			case 2:
				$page = 1;
			case 3:
				// delegate to fetchResources
				$this->_fetchResources($items, $resourceType, $resourceGroup, $page);
				break;
			case 1:
				// fetchGroups doesn't require any kind of network traffic
				// so it's useless to cache the results
				$this->disableCache();
				// delegate to fetchGroups
				$this->_fetchGroups($items, $resourceType);
				break;
			
			case 0:
			default: 
				// fetchTypes doesn't require any kind of network traffic
				// so it's useless to cache the results
				$this->disableCache();
				// delegate to fetchTypes
				$this->_fetchTypes($items);
			
		}
				
		
		return $items;
		
	}
	
	/**
	 *	Add button -watch stream directly-
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
			$link = new X_Page_Item_PItem('core-directwatch', X_Env::_('p_filmstream_watchdirectly'));
			$link->setIcon('/images/icons/play.png')
				->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
				->setLink($url);
			return new X_Page_ItemList_PItem(array($link));
		} else {
			// if there is no link, i have to remove start-vlc button
			// and replace it with a Warning button
			
			X_Debug::i('Setting priority to filterModeItems');
			$this->setPriority('filterModeItems', 99);
			
			$link = new X_Page_Item_PItem('megavideo-warning', X_Env::_('p_filmstream_invalidlink'));
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
			$vlc->registerArg('source', "\"$location\"");			
		} else {
			X_Debug::e("No source o_O");
		}
	}
	
	
	/**
	 * Add the link for -manage-filmstream-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_filmstream_mlink'));
		$link->setTitle(X_Env::_('p_filmstream_managetitle'))
			->setIcon('/images/filmstream/logo.png')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'filmstream'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}
	
	/**
	 * Show an error message if one of the plugin dependencies is missing
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Message
	 */
	public function getIndexMessages(Zend_Controller_Action $controller) {
		$messages = new X_Page_ItemList_Message();
		if ( !X_VlcShares_Plugins::broker()->isRegistered('megavideo') ) { 
			$message = new X_Page_Item_Message($this->getId(), X_Env::_('p_filmstream_warning_nomegavideo'));
			$message->setType(X_Page_Item_Message::TYPE_ERROR);
			$messages->append($message);
		}
		if ( !X_VlcShares_Plugins::broker()->isRegistered('youtube') ) { 
			$message = new X_Page_Item_Message($this->getId(), X_Env::_('p_filmstream_warning_noyoutube'));
			$message->setType(X_Page_Item_Message::TYPE_WARNING);
			$messages->append($message);
		}
		return $messages;
	}
	
	
	
	/**
	 * Fill a list of types of resoures
	 * @param X_Page_ItemList_PItem $items an empty list
	 * @return X_Page_ItemList_PItem the list filled
	 */
	private function _fetchTypes(X_Page_ItemList_PItem $items) {
		
		$types = array(
			self::TYPE_MOVIES => X_Env::_('p_filmstream_type_movies'),
			self::TYPE_TVSHOWS => X_Env::_('p_filmstream_type_tvshows'),
			self::TYPE_ANIME => X_Env::_('p_filmstream_type_anime'),
		);
		
		foreach ( $types as $typeLocParam => $typeLabel ) {
			$item = new X_Page_Item_PItem($this->getId()."-type-$typeLocParam", $typeLabel);
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$typeLocParam")
				->setDescription(APPLICATION_ENV == 'development' ? "$typeLocParam" : null)
				->setLink(array(
					'l'	=>	X_Env::encode("$typeLocParam")
				), 'default', false);
				
			$items->append($item);
		}
	
	}
	
	/**
	 * Fill a list of groups of resoures by type
	 * @param X_Page_ItemList_PItem $items an empty list
	 * @param string $resourceType the resource type selected
	 * @return X_Page_ItemList_PItem the list filled
	 */
	private function _fetchGroups(X_Page_ItemList_PItem $items, $resourceType) {

		if ( $resourceType == self::TYPE_MOVIES ) {
			$groups = 'new,0-9,a-b,c-d,e-f,g-i,i-l,m-n,o,q-s,t-v,w-z';
		} else {
			$groups = 'new,all';
		}
		$groups = explode(',', $groups);
		
		foreach ( $groups as $group ) {
			$item = new X_Page_Item_PItem($this->getId()."-$resourceType-$group", ($group == 'new' || $group == 'all' ? X_Env::_("p_filmstream_group_$group") : strtoupper($group)));
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$resourceType/$group")
				->setDescription(APPLICATION_ENV == 'development' ? "$resourceType/$group" : null)
				->setLink(array(
					'l'	=>	X_Env::encode("$resourceType/$group")
				), 'default', false);
				
			$items->append($item);
		}
	}

	
	/**
	 * Fill a list of resource by type, group and page
	 * @param X_Page_ItemList_PItem $items an empty list
	 * @param string $resourceType the resource type selected
	 * @param string $resourceGroup the resource group selected
	 * @param int $page number of the page 
	 * @return X_Page_ItemList_PItem the list filled
	 */
	private function _fetchResources(X_Page_ItemList_PItem $items, $resourceType, $resourceGroup, $page = 1) {
		
		X_Debug::i("Fetching resources for $resourceType/$resourceGroup/$page");
		
		// if resourceGroup == new, query and url are specials
		if ( $resourceGroup == 'new' ) {
			switch ( $resourceType ) {
				case self::TYPE_MOVIES:
					$url = self::URL_MOVIES_INDEX_NEW;
					$xpathQuery = '//div[@id="maincontent"]//div[@class="galleryitem"]';
					break;
				case self::TYPE_ANIME:
					$url = self::URL_ANIME_INDEX_NEW;
					$xpathQuery = '//div[@id="sidebar"]//div[@id="text-5"]//tr[11]//td';
					break;
				case self::TYPE_TVSHOWS:
					$url = self::URL_TVSHOWS_INDEX_NEW;
					$xpathQuery = '//div[@id="sidebar"]//div[@id="text-5"]//tr[position()=1 or position()=4 or position()=5]//td//a/parent::node()';
					break;
			}
		} else {
			switch ( $resourceType ) {
				case self::TYPE_MOVIES:
					$url = sprintf(self::URL_MOVIES_INDEX_AZ, $resourceGroup);
					$xpathQuery = '//div[@id="maincontent"]//p/*/a[1][node()][text()]';
					$hasThumbnail = false;
					$hasDescription = false;
					break;
				case self::TYPE_ANIME:
					$url = self::URL_ANIME_INDEX_AZ;
					$xpathQuery = '//div[@id="maincontent"]//p/*/a[1][node()][text()]';
					break;
				case self::TYPE_TVSHOWS:
					$url = self::URL_TVSHOWS_INDEX_AZ;
					$xpathQuery = '//div[@id="maincontent"]//p/*/a[1][node()][text()]';
					break;
			}
		}
		
		// fetch the page from filmstream (url is different for each $resourceType)
		$htmlString = $this->_loadPage($url);
		// load the readed page inside a DOM object, so we can user XPath for traversal
		$dom = new Zend_Dom_Query($htmlString);
		// execute the query
		$result = $dom->queryXpath($xpathQuery);
		
		if ( $result->valid() ) {
			
			X_Debug::i("Resources found: ".$result->count());

			$perPage = $this->config('items.perpage', 50);
			
			// before to slice the results, we must check if a -next-page- is needed
			$nextNeeded = ($result->count() > ($page * $perPage) ? true : false );
			
			$matches = array();
			$i = 1;
			while ( $result->valid() ) {
				if (  $i > ( ($page -1) * $perPage ) &&  $i < ($page * $perPage)  ) {
					$currentRes = $result->current();
					
					if ( $resourceGroup == 'new' ) {
						
						$IdNode = $currentRes->firstChild;
						while ( $IdNode instanceof DOMText && $IdNode != null ) {
							$IdNode = $IdNode->nextSibling;
						}
						// anime, tvshow, subbed are on the side bar, and $currentRes has 1 child only
						if ( $currentRes->childNodes->length == 1 ) {
							$labelNode = $IdNode;
						} else {
							$labelNode = $IdNode->nextSibling;
						}
						if ( is_object($labelNode) && trim($labelNode->nodeValue) == '' ) $labelNode = $labelNode->parentNode;
						
						$resourceId = str_replace('/', ':', substr( $IdNode->getAttribute('href'), strlen(self::URL_BASE), -1 ) );
						
						// i've done everthing. If all doesn't work, just skip this entry
						if ( $resourceId == "" ) {
							$i++;
							$result->next();
							continue;
						}
						
						$resourceLabel = trim(@$labelNode->nodeValue);
						if ( $resourceLabel == '' ) {
							$resourceLabel = $currentRes->nodeValue;
						}
						$resourceDescription = null;
						$resourceThumbnail = (($IdNode->firstChild != null && !($IdNode->firstChild instanceof DOMText)) ? $IdNode->firstChild->getAttribute('src') : null );
					} else {
						$resourceId = str_replace('/', ':', substr( $currentRes->getAttribute('href'), strlen(self::URL_BASE), -1 ) );
						$resourceLabel = trim($currentRes->nodeValue);
						$resourceDescription = null;
						$resourceThumbnail = null;
					}
					$matches[] = array($resourceId, $resourceLabel, $resourceDescription, $resourceThumbnail);
				}
				$i++;
				$result->next();
			}
			
			if ( $page > 1 ) {
				// we need the "previus-page" link
				$item = new X_Page_Item_PItem($this->getId()."-previouspage", X_Env::_("p_filmstream_page_previous", ($page - 1)));
				$item->setType(X_Page_Item_PItem::TYPE_CONTAINER)
					->setCustom(__CLASS__.':location', "$resourceType/$resourceGroup/".($page - 1))
					->setLink(array(
						'l'	=>	X_Env::encode("$resourceType/$resourceGroup/".($page - 1))
					), 'default', false);
				$items->append($item);
			}
			
			foreach ($matches as $resource) {
				
				@list($resourceId, $resourceLabel, $resourceDescription, $resourceThumbnail) = $resource;
				
				$item = new X_Page_Item_PItem($this->getId()."-$resourceType-$resourceGroup-$page-$resourceId", $resourceLabel);
				$item->setIcon('/images/icons/folder_32.png')
					->setType(X_Page_Item_PItem::TYPE_CONTAINER)
					->setCustom(__CLASS__.':location', "$resourceType/$resourceGroup/$page/$resourceId")
					->setLink(array(
						'l'	=>	X_Env::encode("$resourceType/$resourceGroup/$page/$resourceId")
					), 'default', false);
					
				if ( $resourceDescription != null ) {
					$item->setDescription($resourceDescription);
				} elseif ( APPLICATION_ENV == 'development' ) {
					$item->setDescription("$resourceType/$resourceGroup/$page/$resourceId");
				}
				if ( $resourceThumbnail != null ) {
					$item->setThumbnail($resourceThumbnail);
				}
				
				$items->append($item);
				
			}
			
			if ( $nextNeeded ) {
				// we need the "previus-page" link
				$item = new X_Page_Item_PItem($this->getId()."-nextpage", X_Env::_("p_filmstream_page_next", ($page + 1)));
				$item->setType(X_Page_Item_PItem::TYPE_CONTAINER)
					->setCustom(__CLASS__.':location', "$resourceType/$resourceGroup/".($page + 1))
					->setLink(array(
						'l'	=>	X_Env::encode("$resourceType/$resourceGroup/".($page + 1))
					), 'default', false);
				$items->append($item);
			}
			
		} else {
			X_Debug::e("Query failed {{$xpathQuery}}");
		}
		
	}
	
	
	private function _fetchVideos(X_Page_ItemList_PItem $items, $resourceType, $resourceGroup, $page, $resourceId) {
		
		X_Debug::i("Fetching videos for $resourceType, $resourceGroup, $page, $resourceId");
		
		// as first thing we have to recreate the resource url from resourceId
		$url = self::URL_BASE;
		// in _fetchResources we converted / in : inside $resourceId and removed the last /
		// so to come back to the real address we have to undo this changes
		$url .= str_replace(':', '/', $resourceId) . '/';
		// now url is something like http://film-stream.tv/01/01/2010/resource-name/
		
		// loading page
		$htmlString = $this->_loadPage($url);

		// it's useless to execute pattern search in the whole page
		// so we stip from $htmlString only the usefull part
		$mainContentStart = '<div id="maincontent">';
		$mainContentEnd = '<div id="sidebar">';
		
		$mainContentStart = strpos($htmlString, $mainContentStart);
		if ( $mainContentStart === false ) $mainContentStart = 0;
		$mainContentEnd = strpos($htmlString, $mainContentEnd, $mainContentStart);
		// substr get a substring of $htmlString from $mainContentStart position to $mainContentEnd position - $mainContentStart position (is the fragment length)
		$htmlString = ($mainContentEnd === false ? substr($htmlString, $mainContentStart) : substr($htmlString, $mainContentStart, ($mainContentEnd - $mainContentStart) ) );

		// let's define some pattern
		
		// $ytPattern will try to intercept
		// youtube trailer link 
		// <param name="movie" value="http://www.youtube.com/v/VIDEOID?version=3">
		// match[1] = video id
		$ytPattern = '/<param name\=\"movie\" value\=\"http\:\/\/www\.youtube\.com\/v\/([^\?\"\&]+)([^\>]*)>/';

		
		$matches = array();
		// first let's search for youtube videos
		if ( preg_match_all($ytPattern, $htmlString, $matches, PREG_SET_ORDER) ) {
			
			foreach ($matches as $match) {
				
				$videoId = self::VIDEO_YOUTUBE . ':' . $match[1];
				$videoLabel = X_Env::_("p_filmstream_video_youtubetrailer");
				
				$item = new X_Page_Item_PItem("{$this->getId()}-youtube-$videoId", $videoLabel);
				$item->setIcon('/images/icons/file_32.png')
					->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setCustom(__CLASS__.':location', "$resourceType/$resourceGroup/$page/$resourceId/$videoId")
					->setLink(array(
						'action'	=> 'mode',
						'l'	=>	X_Env::encode("$resourceType/$resourceGroup/$page/$resourceId/$videoId")
					), 'default', false);
					
				if ( APPLICATION_ENV == 'development' ) {
					$item->setDescription("$resourceType/$resourceGroup/$page/$resourceId/$videoId");
				}
					
				$items->append($item);
				
			}
			
		} else {
			X_Debug::e("Youtube pattern failure {{$ytPattern}}");
		}
		
		$matches = array();
		if ( preg_match_all('/href\=\"(?P<LINK>[^\"]+)"([^\>]*)>(?P<LABEL>[^\<]+)<\/a>/', $htmlString, $matches, PREG_SET_ORDER) ) {
			
			X_Debug::i(var_export($matches, true));
			
			foreach ($matches as $match) {
				
				try {
					
					$link = $match['LINK'];
					
					$hoster = $this->helpers()->hoster()->findHoster($link);
					$videoLabel = trim(strip_tags($match['LABEL'])). " [". ucfirst($hoster->getId()) ."]";
					$videoId = "{$hoster->getId()}:{$hoster->getResourceId($link)}";
					
					$item = new X_Page_Item_PItem("{$this->getId()}-hoster-$videoId", $videoLabel);
					$item->setIcon('/images/icons/file_32.png')
						->setType(X_Page_Item_PItem::TYPE_ELEMENT)
						->setCustom(__CLASS__.':location', "$resourceType/$resourceGroup/$page/$resourceId/$videoId")
						->setLink(array(
							'action'	=> 'mode',
							'l'	=>	X_Env::encode("$resourceType/$resourceGroup/$page/$resourceId/$videoId")
						), 'default', false);
						
					if ( APPLICATION_ENV == 'development' ) {
						$item->setDescription("$resourceType/$resourceGroup/$page/$resourceId/$videoId");
					}
					
					$items->append($item);
					
				} catch ( Exception $e) {
					// no hoster for the link, skipped
				}
			}
			
		} else {
			X_Debug::e("General pattern failure");
		}
		
	}	
	
	
	private function _loadPage($uri) {

		X_Debug::i("Loading page $uri");
		
		$http = new Zend_Http_Client($uri, array(
			'maxredirects'	=> $this->config('request.maxredirects', 10),
			'timeout'		=> $this->config('request.timeout', 25)
		));
		
		$http->setHeaders(array(
			$this->config('hide.useragent', false) ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION .' filmstream/'.self::VERSION_CLEAN : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1',
		));
		
		$response = $http->request();
		$htmlString = $response->getBody();
		
		return $htmlString;
	}
	
	
	/**
	 * Disable cache plugin is registered and enabled
	 */
	private function disableCache() {
		
		if ( X_VlcShares_Plugins::broker()->isRegistered('cache') ) {
			$cache = X_VlcShares_Plugins::broker()->getPlugins('cache');
			if ( method_exists($cache, 'setDoNotCache') ) {
				$cache->setDoNotCache();
			}
		}
		
	}
	
}
