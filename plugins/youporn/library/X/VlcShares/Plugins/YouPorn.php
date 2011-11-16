<?php 

class X_VlcShares_Plugins_YouPorn extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const VERSION = '0.1beta';
	const VERSION_CLEAN = '0.1';
	
	const URL_INDEX_VIDEOS = 'http://www.youporn.com/browse/time?page=%s';
	
	const LOCATION = '/^(?P<page>[0-9]+)\/(?P<id>[0-9]+)$';
	
	//{{{NODE DEFINITION
	private $nodes = array(
		'exact:' => array(
				'function'	=> 'menuVideos',
				'params'	=> array(1)
			),
		'regex:/^(?P<page>[0-9]+?)$/' => array(
				'function'	=> 'menuVideos',
				'params'	=> array('$page')
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
		$this->helpers()->hoster()->registerHoster(
				new X_VlcShares_Plugins_Helper_Hoster_YouPorn($this->config('hide.useragent', false)
		));
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

		$split = $location != '' ? @explode('/', $location, 2) : array();
		@list($page, $id) = $split;

		X_Debug::i("Page: $page, Id: $id");
		
		if ( $id == null ) {
			// location isn't a valid video id, so we return fals
			// and insert the query result in the cache
			$this->cachedLocation[$location] = false;
			return false;	
		}

		try {
			// find an hoster which can handle the url type and revolve the real url
			$return = $this->getLinkHosterUrl($id);
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

	
	public function menuVideos(X_Page_ItemList_PItem $items, $pageN = 1) {
		$pageN = X_VlcShares_Plugins_Utils::isset_or($pageN, 1);
		$page = X_PageParser_Page::getPage(
				sprintf(self::URL_INDEX_VIDEOS, $pageN),
				new X_PageParser_Parser_YouPorn(X_PageParser_Parser_YouPorn::MODE_VIDEOS)
		);
		$this->preparePageLoader($page);
		$parsed = $page->getParsed();
		
		if ($pageN > 1 ) {
			// add -previous-page-
			$previousPage = $pageN - 1;
			$items->append(X_VlcShares_Plugins_Utils::getPreviousPage("$previousPage", $previousPage));
		}
		
		foreach ($parsed as $match) {
			
			$id = $match['id'];
			$label = $match['label'];
			$thumbnail = array_key_exists('thumbnail', $match) ? $match['thumbnail'] : null;
			
			$item = new X_Page_Item_PItem($this->getId()."-{$pageN}-{$id}", $label );
			$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setCustom(__CLASS__.':location', "$pageN/$id")
				->setDescription(APPLICATION_ENV == 'development' ? "$pageN/$id" : null)
				->setIcon("/images/icons/hosters/youporn.png")
				->setLink(array(
					'action' => 'mode',
					'l'	=>	X_Env::encode("$pageN/$id")
				), 'default', false);
			
			if ( $thumbnail ) $item->setThumbnail($thumbnail);
			
			$items->append($item);
			
		}
		
		if ( $page->getParsed(new X_PageParser_Parser_YouPorn(X_PageParser_Parser_YouPorn::MODE_NEXTPAGE)) ) {
			$nextPage = $pageN + 1;
			$items->append(X_VlcShares_Plugins_Utils::getNextPage("$nextPage", $nextPage));
		}
		
	}
	
	private function getLinkHosterUrl($linkId) {
		
		try {
			/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
			$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
	
			$response = $cacheHelper->retrieveItem("youporn::$linkId");
			X_Debug::i("Valid cache entry found: $response");
			return $response;
	
		} catch (Exception $e) {
			// no cache plugin or no entry in cache, it's the same
			X_Debug::i("Cache disabled or no valid entry found");
		}
	
		$linkUrl = $this->helpers()->hoster()->getHoster('youporn')->getPlayable($linkId, true);
		
		X_Debug::i("Hoster location resolved: $linkUrl");
		
		try {
			/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
			$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
		
			$cacheHelper->storeItem("youporn::$linkId", $linkUrl, 15); // store for the next 15 min
		
			X_Debug::i("Value stored in cache for 15 min: {key = youporn::$linkId, value = $linkUrl}");
		
		} catch (Exception $e) {
			// no cache plugin, next time i have to repeat the request
		}
		
		return $linkUrl;
	
	}	
	
	
	private function preparePageLoader(X_PageParser_Page $page) {

		$loader = $page->getLoader();
		if ( $loader instanceof X_PageParser_Loader_Http || $loader instanceof X_PageParser_Loader_HttpAuthRequired ) {
		
			$http = $loader->getHttpClient()->setConfig(array(
				'maxredirects'	=> $this->config('request.maxredirects', 10),
				'timeout'		=> $this->config('request.timeout', 25)
			));
			
			$http->setMethod(Zend_Http_Client::POST);
			$http->setParameterPost('user_choice', 'Enter');
			
			$http->setHeaders(array(
				$this->config('hide.useragent', false) ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION .' youporn/'.self::VERSION : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1',
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
