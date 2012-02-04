<?php 

class X_VlcShares_Plugins_TvLinks extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const VERSION = '0.1';
	const VERSION_CLEAN = '0.1';
	
	const URL_INDEX_TITLES = 'http://www.tv-links.eu/%s/%s.html';
	const URL_INDEX_EPISODES = 'http://www.tv-links.eu/%s/%s/';
	const URL_INDEX_LINKS = 'http://www.tv-links.eu/%s/%s/season_%s/episode_%s/video-results/?apg=%s';
	const URL_INDEX_LINKS_DOCUMENTARIES =  'http://www.tv-links.eu/%s/%s/video-results/?apg=%s';
	const URL_LINKRESOLVER = 'http://www.tv-links.eu/gateway.php?data=%s';
	
	const LOCATION = '/^(?P<type>[^/]*?)\/(?P<filter>[A-Z#])\/(?P<season>[0-9]+)\/(?P<episode>[0-9])+\/(?P<page>[0-9])+\/(?P<link>[0-9]+)$';
	
	//{{{NODE DEFINITION
	private $nodes = array(
		'exact:' => array(
				'function'	=> 'menuTypes',
				'params'	=> array()
			),
		'regex:/^(?P<type>[^\/]+?)$/' => array(
				'function'	=> 'menuFilters',
				'params'	=> array('$type')
			),
		'regex:/^(?P<type>[^\/]+?)\/(?P<filter>[A-Z#])$/' => array(
				'function'	=> 'menuTitles',
				'params'	=> array('$type', '$filter')
			),
		// special nodes for web and documentaries
		'regex:/^(?P<type>(web-originals|documentaries))\/(?P<filter>[A-Z#])\/(?P<title>[^\/]+)$/' => array(
				'function'	=> 'menuLinks',
				'params'	=> array('$type', '$filter', '$title', 0, 0)
			),
		// fallback regex
		'regex:/^(?P<type>[^\/]+?)\/(?P<filter>[A-Z#])\/(?P<title>[^\/]+)$/' => array(
				'function'	=> 'menuEpisodes',
				'params'	=> array('$type', '$filter', '$title' )
			),
		'regex:/^(?P<type>[^\/]+?)\/(?P<filter>[A-Z#])\/(?P<title>[^\/]+)\/(?P<season>[0-9]+)\/(?P<episode>[0-9]+)$/' => array(
				'function'	=> 'menuLinks',
				'params'	=> array('$type', '$filter', '$title', '$season', '$episode' )
			),
		'regex:/^(?P<type>[^\/]+?)\/(?P<filter>[A-Z#])\/(?P<title>[^\/]+)\/(?P<season>[0-9]+)\/(?P<episode>[0-9]+)\/(?P<page>[0-9]+)$/' => array(
				'function'	=> 'menuLinks',
				'params'	=> array('$type', '$filter', '$title', '$season', '$episode', '$page' )
			)
	);
	//}}}
	
	private $types = array(
		'tv-shows' => 'p_tvlinks_types_tvshows',
		'anime' => 'p_tvlinks_types_anime',
		//'web-originals' => 'p_tvlinks_types_weboriginals',
		'documentaries' => 'p_tvlinks_documentaries',
	);
	
	protected $cachedLocation = array();
	
	function __construct() {
		// this plugin requires:
		//		VlcShares > 0.5.4
		//			OR 
		//		VlcShares 0.5.4 + PageParserLib >= 0.1alpha2
		if ( X_VlcShares::VERSION_CLEAN != '0.5.4' || 
				(class_exists('X_VlcShares_Plugins_Utils') 
					&& method_exists('X_VlcShares_Plugins_Utils', 'menuProxy')) 
				) {
			
			$this->setPriority('getCollectionsItems');
			$this->setPriority('getShareItems');
			$this->setPriority('preGetModeItems');
			$this->setPriority('preRegisterVlcArgs');
			$this->setPriority('getIndexManageLinks');
		}
		$this->setPriority('gen_beforeInit');
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

		$split = $location != '' ? @explode('/', $location, 7) : array();
		@list($type, $filter, $title, $season, $episode, $page, $id) = $split;

		X_Debug::i("Type: $type, Filter: $filter, Title: $title, Season: $season, Episode: $episode, Page: $page, Id: $id");
		
		if ( $id == null ) {
			// location isn't a valid video id, so we return fals
			// and insert the query result in the cache
			$this->cachedLocation[$location] = false;
			return false;	
		}
		
		// resolve the hosterUrl:
		$hosterUrl = $this->getLinkHosterUrl($id);

		try {
			// find an hoster which can handle the url type and revolve the real url
			$return = $this->helpers()->hoster()->findHoster($hosterUrl)->getPlayable($hosterUrl, false);
		} catch (Exception $e) {
			$return = false;
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
		
		// 0type/1filter/2title/3season/4episode/5page/6id
		
		if ( count($exploded) == 5 ) {
			array_pop($exploded);
		}
		
		if ( count($exploded) == 6 ) {
			// move from page list to episodes list
			array_pop($exploded);
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
	 * Add the TvLinks link inside collection index
	 * @param Zend_Controller_Action $controller
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");
		
		return X_VlcShares_Plugins_Utils::getCollectionsEntryList($this->getId());
		
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
		X_Debug::i("Requested node: $location");

		X_VlcShares_Plugins_Utils::menuProxy($items, $location, $this->nodes, $this );
		
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
		return X_VlcShares_Plugins_Utils::getWatchDirectlyOrFilter($this->getId(), $this, $location);
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
		X_Debug::i('Plugin triggered');
		X_VlcShares_Plugins_Utils::registerVlcLocation($this, $vlc, $location);
	}
	
	
	/**
	 * Add the link for -manage-streamingonline-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {
		return X_VlcShares_Plugins_Utils::getIndexManageEntryList($this->getId());
	}
	
	/**
	 * Show an error message if one of the plugin dependencies is missing
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Message
	 */
	public function getIndexMessages(Zend_Controller_Action $controller) {
		$messages = new X_Page_ItemList_Message();
		
		if ( class_exists("X_VlcShares_Plugins_Utils", true) ) {
			if ( method_exists('X_VlcShares_Plugins_Utils', 'menuProxy')  ) {
				if ( count(X_VlcShares_Plugins::helpers()->hoster()->getHosters()) < 1 ) {
					$messages->append(X_VlcShares_Plugins_Utils::getMessageEntry(
							$this->getId(),
							'p_tvlinks_warning_nohosters',
							X_Page_Item_Message::TYPE_ERROR
					));
				}
			} else {
				// old version of PageParserLib
				$message = new X_Page_Item_Message($this->getId(),"PageParserLib plugin version is old. Please, update it (0.1alpha2 or later required)");
				$message->setType(X_Page_Item_Message::TYPE_FATAL);
				$messages->append($message);
			}
		} else {
			$message = new X_Page_Item_Message($this->getId(),"PageParser API is required from TvLinks. Please, install PageParserLib plugin (0.1alpha2 or later required)");
			$message->setType(X_Page_Item_Message::TYPE_FATAL);
			$messages->append($message);
		}
		return $messages;
	}
	
	
	/**
	 * Fill $items of types menu entry
	 * @param X_Page_ItemList_PItem $items
	 */
	public function menuTypes(X_Page_ItemList_PItem $items) {
		$this->disableCache();
		X_VlcShares_Plugins_Utils::fillStaticMenu($items, $this->types, "{$this->getId()}-type-");
	}
	
	/**
	 * Fill menu entries for node:
	 * 		^$type$
	 * as an alphabetic index
	 * @param X_Page_ItemList_PItem $items
	 * @param string $type
	 */
	public function menuFilters(X_Page_ItemList_PItem $items, $type) {
		$this->disableCache();
		$entries = array( "#" => "0-9" );
		for ( $i = ord("A"); $i <= ord("Z"); $i++ ) {
			$entries[chr($i)] = chr($i);
		}
		X_VlcShares_Plugins_Utils::fillStaticMenu($items, $entries, "{$this->getId()}-{$type}-filter-", "$type/");
	}
	
	public function menuTitles(X_Page_ItemList_PItem $items, $type, $filter) {
		// change filter type from # => 0-9
		$realFilter = $filter != '#' ? $filter : '0-9';
		$page = X_PageParser_Page::getPage(
				sprintf(self::URL_INDEX_TITLES, $type, $realFilter),
				new X_PageParser_Parser_TvLinks(X_PageParser_Parser_TvLinks::MODE_TITLES, $type, $filter)
		);
		$this->preparePageLoader($page);
		$parsed = $page->getParsed();
		
		foreach ( $parsed as $match ) {
			$title = $match['title'];
			$label = $match['label'];
			
			$item = new X_Page_Item_PItem($this->getId()."-{$type}-{$filter}-{$title}", $label );
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$type/$filter/$title")
				->setDescription(APPLICATION_ENV == 'development' ? "$type/$filter/$title" : null)
				->setLink(array(
						'l'	=>	X_Env::encode("$type/$filter/$title")
				), 'default', false);
			
			$items->append($item);
			
			
		}
		
	}
	
	public function menuEpisodes(X_Page_ItemList_PItem $items, $type, $filter, $title) {
		$page = X_PageParser_Page::getPage(
				sprintf(self::URL_INDEX_EPISODES, $type, $title),
				new X_PageParser_Parser_TvLinks(X_PageParser_Parser_TvLinks::MODE_EPISODES, $type)
		);
		$this->preparePageLoader($page);
		$parsed = $page->getParsed();
		
		foreach ( $parsed as $match ) {
			$episode = $match['episode'];
			$season = $match['season'];
			$thumbnail = @$match['thumbnail'] ? $match['thumbnail'] : null;
			$label = $match['label'];
		
			$item = new X_Page_Item_PItem($this->getId()."-{$type}-{$filter}-{$title}-{$season}-{$episode}", $label );
			$item->setIcon('/images/icons/folder_32.png')
			->setType(X_Page_Item_PItem::TYPE_CONTAINER)
			->setCustom(__CLASS__.':location', "$type/$filter/$title/$season/$episode")
			->setDescription(APPLICATION_ENV == 'development' ? "$type/$filter/$title/$season/$episode" : null)
			->setThumbnail($thumbnail)
			->setLink(array(
					'l'	=>	X_Env::encode("$type/$filter/$title/$season/$episode")
			), 'default', false);
		
			$items->append($item);
		}
		
	}
	
	public function menuLinks(X_Page_ItemList_PItem $items, $type, $filter, $title, $season, $episode, $pageN = 1) {
		$pageN = X_VlcShares_Plugins_Utils::isset_or($pageN, 1);
		if ( $type == 'documentaries' ) {
			$page = X_PageParser_Page::getPage(
					sprintf(self::URL_INDEX_LINKS_DOCUMENTARIES, $type, $title, $pageN),
					new X_PageParser_Parser_TvLinks(X_PageParser_Parser_TvLinks::MODE_LINKS, $type, $filter, $title, $season, $episode, $pageN)
			);
		} else {
			$page = X_PageParser_Page::getPage(
					sprintf(self::URL_INDEX_LINKS, $type, $title, $season, $episode, $pageN),
					new X_PageParser_Parser_TvLinks(X_PageParser_Parser_TvLinks::MODE_LINKS, $type, $filter, $title, $season, $episode, $pageN)
			);
		}
		$this->preparePageLoader($page);
		$parsed = $page->getParsed();
		
		if ($pageN > 1 ) {
			// add -previous-page-
			$previousPage = $pageN - 1;
			$items->append(X_VlcShares_Plugins_Utils::getPreviousPage("$type/$filter/$title/$season/$episode/$previousPage", $previousPage));
		}
		
		foreach ($parsed as $match) {
			
			$id = $match['id'];
			$hoster = $match['hoster'];
			$label = X_Env::_('p_tvlinks_watchon', ucfirst($hoster));
			
			$thumbnail = array_key_exists('thumbnail', $match) ? $match['thumbnail'] : null;
			
			$item = new X_Page_Item_PItem($this->getId()."-{$type}-{$filter}-{$title}-{$season}-{$episode}-{$id}", $label );
			
			if ( file_exists( APPLICATION_PATH . '/../public/images/icons/hosters/'.$hoster.'.png' ) ) {
				$item->setIcon("/images/icons/hosters/{$hoster}.png");
			} else {
				if ( X_VlcShares::VERSION == "0.5.4" ) {
					$item->setIcon("/images/icons/file_32.png");
				} else {
					// icons exists only in vlc-shares > 0.5.4
					$item->setIcon("/images/icons/unknown-hoster.png");
				}
			}
			
			$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setCustom(__CLASS__.':location', "$type/$filter/$title/$season/$episode/$pageN/$id")
				->setDescription(APPLICATION_ENV == 'development' ? "$type/$filter/$title/$season/$episode/$pageN/$id" : null)
				->setLink(array(
					'action' => 'mode',
					'l'	=>	X_Env::encode("$type/$filter/$title/$season/$episode/$pageN/$id")
				), 'default', false);
			
			if ( $thumbnail ) $item->setThumbnail($thumbnail);
			
			$items->append($item);
			
			
		}
		
		if ( $page->getParsed(new X_PageParser_Parser_TvLinks(
					X_PageParser_Parser_TvLinks::MODE_NEXTPAGE,
					$type, $filter, $title, $season, $episode, $pageN
				)) ) {
			$nextPage = $pageN + 1;
			$items->append(X_VlcShares_Plugins_Utils::getNextPage("$type/$filter/$title/$season/$episode/$nextPage", $nextPage));
		}
		
	}
	
	private function getLinkHosterUrl($linkId) {
		
		try {
			/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
			$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
	
			$response = $cacheHelper->retrieveItem("tvlinks::$linkId");
			X_Debug::i("Valid cache entry found: $response");
			return $response;
	
		} catch (Exception $e) {
			// no cache plugin or no entry in cache, it's the same
			X_Debug::i("Cache disabled or no valid entry found");
		}
	
		$http = new Zend_Http_Client(sprintf(self::URL_LINKRESOLVER, base64_encode($linkId)), array (
				'maxredirects'	=> 0,
				'timeout'		=> $this->config('request.timeout', 25)
		));
		
		$http->setHeaders(array(
			$this->config('hide.useragent', false) ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION .' tvlinks/'.self::VERSION : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1',
		));
		
		$hosterLocation = $http->request()->getHeader('Location');
		
		X_Debug::i("Hoster location resolved: $hosterLocation");
		
		try {
			/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
			$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
		
			$cacheHelper->storeItem("tvlinks::$linkId", $hosterLocation, 15); // store for the next 15 min
		
			X_Debug::i("Value stored in cache for 15 min: {key = tvlinks::$linkId, value = $hosterLocation}");
		
		} catch (Exception $e) {
			// no cache plugin, next time i have to repeat the request
		}
		
		return $hosterLocation;
	
	}	
	
	
	private function preparePageLoader(X_PageParser_Page $page) {

		$loader = $page->getLoader();
		if ( $loader instanceof X_PageParser_Loader_Http || $loader instanceof X_PageParser_Loader_HttpAuthRequired ) {
		
			$http = $loader->getHttpClient()->setConfig(array(
				'maxredirects'	=> $this->config('request.maxredirects', 10),
				'timeout'		=> $this->config('request.timeout', 25)
			));
			
			$http->setHeaders(array(
				$this->config('hide.useragent', false) ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION .' tvlinks/'.self::VERSION : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1',
			));
			
		}		
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
