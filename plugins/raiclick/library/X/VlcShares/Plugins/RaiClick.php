<?php 

class X_VlcShares_Plugins_RaiClick extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const VERSION = '0.1';
	const VERSION_CLEAN = '0.1';
	
	
	const URL_BASE = "http://www.rai.tv/%s";
	const URL_INDEX_CATEGORIES =  "http://www.rai.tv/dl/RaiTV/cerca_tematiche.html?%s"; // 1 category
	const URL_INDEX_TYPES = "http://www.rai.tv/dl/RaiTV/programmi/Page-%s-page.html?LOAD_CONTENTS"; // 1 content id
	const URL_INDEX_LINKS = "http://www.rai.tv/dl/RaiTV/programmi/liste/ContentSet-%s-V-%s.html"; // 1: content id, 2: page
	const URL_PLAYABLE = "http://www.rai.tv/dl/RaiTV/programmi/media/ContentItem-%s.html#p=0";
	
	
	const LOCATION = '/^(?P<category>[^/]+?)\/(?P<show>[^/]+?)\/(?P<type>[^/]+?)\/(?P<link>[^/]+?)$/';
	
	//{{{NODE DEFINITION
	private $nodes = array(
		'exact:' => array(
				'function'	=> 'menuCategories',
				'params'	=> array()
			),
		'regex:/^(?P<category>[^\/]+?)$/' => array(
				'function'	=> 'menuShows',
				'params'	=> array('$category')
			),
		'regex:/^(?P<category>[^\/]+?)\/(?P<show>[^\/]+?)$/' => array(
				'function'	=> 'menuTypes',
				'params'	=> array('$category', '$show')
			),
		'regex:/^(?P<category>[^\/]+?)\/(?P<show>[^\/]+?)\/(?P<type>[^\/]+?)$/' => array(
				'function'	=> 'menuLinks',
				'params'	=> array('$category', '$show', '$type' )
			),
	);
	//}}}
	
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

		$split = $location != '' ? @explode('/', $location, 4) : array();
		@list($category, $show, $type, $link) = $split;

		X_Debug::i("Category: $category, Show: $show, Type:$type, Link: $link");
		
		if ( $link == null ) {
			// location isn't a valid video id, so we return fals
			// and insert the query result in the cache
			$this->cachedLocation[$location] = false;
			return false;	
		}
		
		// resolve the link:

		$return = $this->getLinkHosterUrl($link);
		
		$this->cachedLocation[$location] = $return;
		return $return;
	
	}
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::getParentLocation()
	 */
	function getParentLocation($location = null) {
		if ( $location == '' || $location == null ) return false;
		
		$exploded = explode('/', $location);
		
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
			if ( !method_exists('X_VlcShares_Plugins_Utils', 'menuProxy')  ) {
				// old version of PageParserLib
				$message = new X_Page_Item_Message($this->getId(),"PageParserLib plugin version is old. Please, update it");
				$message->setType(X_Page_Item_Message::TYPE_FATAL);
				$messages->append($message);
			}
		} else {
			$message = new X_Page_Item_Message($this->getId(),"PageParser API is required from RaiClick. Please, install PageParserLib plugin");
			$message->setType(X_Page_Item_Message::TYPE_FATAL);
			$messages->append($message);
		}
		return $messages;
	}
	
	public function menuCategories(X_Page_ItemList_PItem $items ) {
		// change filter type from # => 0-9
		$page = X_PageParser_Page::getPage(
				sprintf(self::URL_INDEX_CATEGORIES, 'Nope'),
				//new X_PageParser_Parser_TvLinks(X_PageParser_Parser_TvLinks::MODE_TITLES, $type, $filter)
				new X_PageParser_Parser_Preg('/<a title=\"(?P<title>.*?)\" href=\"\?(?P<href>.*?)\">(?P<label>.*?)<\/a>/', X_PageParser_Parser_Preg::PREG_MATCH_ALL, PREG_SET_ORDER)
		);
		$this->preparePageLoader($page);
		$parsed = $page->getParsed();
	
		foreach ( $parsed as $match ) {
			$title = $match['title'];
			$label = $match['label'];
			$href = $match['href'];
				
			$item = new X_Page_Item_PItem($this->getId()."-{$label}", $label );
			$item->setIcon('/images/icons/folder_32.png')
			->setType(X_Page_Item_PItem::TYPE_CONTAINER)
			->setCustom(__CLASS__.':location', "$href")
			->setDescription(APPLICATION_ENV == 'development' ? "$href" : null)
			->setLink(array(
					'l'	=>	X_Env::encode("$href")
			), 'default', false);
				
			$items->append($item);
				
				
		}
	
	}

	public function menuShows(X_Page_ItemList_PItem $items, $category ) {
		$page = X_PageParser_Page::getPage(
				sprintf(self::URL_INDEX_CATEGORIES, $category),
				new X_PageParser_Parser_Preg(
						'/<a href=\"\/dl\/RaiTV\/programmi\/Page-(?P<href>.*?)\.html\">.*?<div class=\"internal\">(?P<label>.*?)<\/div>/s',
						 X_PageParser_Parser_Preg::PREG_MATCH_ALL, PREG_SET_ORDER)
		);
		$this->preparePageLoader($page);
		$parsed = $page->getParsed();
	
		foreach ( $parsed as $match ) {
			$label = $match['label'];
			$href = $match['href'];
	
			$item = new X_Page_Item_PItem($this->getId()."-{$href}", $label );
			$item->setIcon('/images/icons/folder_32.png')
			->setType(X_Page_Item_PItem::TYPE_CONTAINER)
			->setCustom(__CLASS__.':location', "$category/$href")
			->setDescription(APPLICATION_ENV == 'development' ? "$category/$href" : null)
			->setLink(array(
					'l'	=>	X_Env::encode("$category/$href")
			), 'default', false);
	
			$items->append($item);
	
	
		}
	
	}
	
	
	public function menuTypes(X_Page_ItemList_PItem $items, $category, $show) {
		$page = X_PageParser_Page::getPage(
				sprintf(self::URL_INDEX_TYPES, $show),
				new X_PageParser_Parser_Preg(
						'/<a target=\"_top\" href=\"#\" id=\"ContentSet-(?P<href>.*?)\">(?P<label>.*?)<\/a>/',
						 X_PageParser_Parser_Preg::PREG_MATCH_ALL, PREG_SET_ORDER)
		);
		$this->preparePageLoader($page);
		$parsed = $page->getParsed();
		
		foreach ( $parsed as $match ) {
			$label = $match['label'];
			$href = $match['href'];
			
			$item = new X_Page_Item_PItem($this->getId()."-{$show}-{$href}", $label );
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$category/$show/$href")
				->setDescription(APPLICATION_ENV == 'development' ? "$category/$show/$href" : null)
				->setLink(array(
						'l'	=>	X_Env::encode("$category/$show/$href")
				), 'default', false);
			
			$items->append($item);
			
			
		}
		
	}
	
	public function menuLinks(X_Page_ItemList_PItem $items, $category, $show, $type) {
		$i = 0;
		while ( true ) {
			$page = X_PageParser_Page::getPage(
					sprintf(self::URL_INDEX_LINKS, $type, $i++), // i incremented for next iteration
					new X_PageParser_Parser_Preg(
							'/<a .*?href=\"\/dl\/RaiTV\/programmi\/media\/ContentItem-(?P<href>.*?).html#p=0\".*?src=\"(?P<thumbnail>.*?)\".*?><h2>(?P<label>.*?)<\/h2>/s',
							X_PageParser_Parser_Preg::PREG_MATCH_ALL, PREG_SET_ORDER)
			);
			$this->preparePageLoader($page);
			$parsed = $page->getParsed();
			
			// exit first time no item found
			if ( !count($parsed) ) {
				return;
			}
			
			foreach ( $parsed as $match ) {
				$label = $match['label'];
				$href = $match['href'];
				$thumbnail = $match['thumbnail'];
				if ( $thumbnail && !X_Env::startWith($thumbnail, 'http://') ) {
					$thumbnail = sprintf(self::URL_BASE, ltrim($thumbnail, '/'));
				}
						
				$item = new X_Page_Item_PItem($this->getId()."-{$show}-{$type}-{$href}", $label );
				
				$item->setIcon("/images/icons/file_32.png");
				
				$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setCustom(__CLASS__.':location', "$category/$show/$type/$href")
					->setThumbnail($thumbnail)
					->setDescription(APPLICATION_ENV == 'development' ? "$category/$show/$type/$href" : null)
					->setLink(array(
						'action' => 'mode',
						'l'	=>	X_Env::encode("$category/$show/$type/$href")
					), 'default', false);
				
				
				$items->append($item);
				
			}

		}
	}
	
	private function getLinkHosterUrl($linkId) {
		
		try {
			/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
			$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
	
			$response = $cacheHelper->retrieveItem("raiclick::$linkId");
			X_Debug::i("Valid cache entry found: $response");
			return $response;
	
		} catch (Exception $e) {
			// no cache plugin or no entry in cache, it's the same
			X_Debug::i("Cache disabled or no valid entry found");
		}
	
				// first get the playlist url from the page
			
		$page = X_PageParser_Page::getPage(
				sprintf(self::URL_PLAYABLE, $linkId),
				new X_PageParser_Parser_Preg(
						'/videoURL = \"(?P<url>.*?)\"/',
						X_PageParser_Parser_Preg::PREG_MATCH)
		);
		$this->preparePageLoader($page);
		$parsed = $page->getParsed();
		$url = $parsed['url'];
		
		$hosterLocation = false;
		
		if ( $url ) {
			
			// check if url schema is mms
			if ( X_Env::startWith($url, 'mms://') ) {
				$hosterLocation = $url;
			} else {

				// the get the video url from the playlist
				$page = X_PageParser_Page::getPage(
						$url, // i incremented for next iteration
						new X_PageParser_Parser_Preg(
								'/HREF="(?P<url>.*?)\"/',
								X_PageParser_Parser_Preg::PREG_MATCH)
				);
				$this->preparePageLoader($page);
				$parsed = $page->getParsed();
				$hosterLocation = $parsed['url'];
				
			}
			
		}
			
		X_Debug::i("Hoster location resolved: $hosterLocation");
		
		try {
			/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
			$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
		
			$cacheHelper->storeItem("raiclick::$linkId", $hosterLocation, 15); // store for the next 15 min
		
			X_Debug::i("Value stored in cache for 15 min: {key = raiclick::$linkId, value = $hosterLocation}");
		
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
				$this->config('hide.useragent', false) ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION .' raiclick/'.self::VERSION : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1',
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
