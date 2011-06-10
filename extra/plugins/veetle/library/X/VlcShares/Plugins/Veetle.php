<?php 


class X_VlcShares_Plugins_Veetle extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
    const VERSION = '0.1.1';
    const VERSION_CLEAN = '0.1.1';
	
    const CHANNELS_INDEX = "http://www.veetle.com/iphone-channel-listing-cross-site.js";
    
	function __construct() {
		
		$this
			->setPriority('gen_beforeInit')
			->setPriority('getCollectionsItems')
			->setPriority('getShareItems')
			->setPriority('preGetModeItems')
			->setPriority('prepareConfigElement')
			->setPriority('getIndexManageLinks')
			->setPriority('preRegisterVlcArgs')
			;
		
	}
	
	
	/**
	 * Registers a veoh hoster inside the hoster broker
	 */
	public function gen_beforeInit(Zend_Controller_Action $controller) {
		
		$this->helpers()->language()->addTranslation(__CLASS__);
		$this->helpers()->hoster()->registerHoster(new X_VlcShares_Plugins_Helper_Hoster_Veetle($this->config('server.ip', '213.254.245.212')));
		
	}

	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::resolveLocation
	 * @param string $location
	 * @return string real address of a resource
	 */
	function resolveLocation($location = null) {
		
		// prevent no-location-given error
		if ( $location === null || $location === '' ) return false;
		
		// location format:
		// $page/veetle:$channelId
		$locParts = explode('/', $location, 2);
		@list($page, $item) = $locParts;
		$locCount = count($locParts);
		
		if ( $locCount != 2 ) return false;
		
		@list(, $channelId) = explode(':', $item);
		
		// we use the new hoster helper api
		try {
			return $this->helpers()->hoster()->getHoster('veetle')->getPlayable($channelId, true);
		} catch ( Exception $e) {
			return false;
		}
		
	}
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::getParentLocation
	 * @param $location
	 */
	function getParentLocation($location = null) {
		
		if ($location == null || $location == '') return false;
		
		// location format:
		// $page/veetle:$channelId
		$locParts = $location != '' ? explode('/', $location, 2) : array();
		$locCount = count($locParts);
		
		switch ($locCount) {
			default:
				array_pop($locParts);
		}
		
		if ( count($locParts) >= 1 ) {
			return implode('/', $locParts);
		} else {
			return false;
		}			
	}	
	
	
	/**
	 * Add the main link for megavideo library
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");

		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_veetle_collectionindex'));
		$link->setIcon('/images/veetle/logo.jpg')
			->setDescription(X_Env::_('p_veetle_collectionindex_desc'))
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
	 * @param string $provider
	 * @param string $location
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function getShareItems($provider, $location, Zend_Controller_Action $controller) {
		// this plugin add items only if it is the provider
		if ( $provider != $this->getId() ) return;
		
		X_Debug::i("Plugin triggered");

		// disabling cache plugin
		try {
			$cachePlugin = X_VlcShares_Plugins::broker()->getPlugins('cache');
			if ( method_exists($cachePlugin, 'setDoNotCache') ) {
				$cachePlugin->setDoNotCache();
			}
		} catch (Exception $e) {
			// cache plugin not registered, no problem
		}
		
		$urlHelper = $controller->getHelper('url');
		
		X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_SortItems');
		
		$items = new X_Page_ItemList_PItem();

		// location format:
		// $page/veetle:$channelId
		$locParts = $location != '' ? explode('/', $location, 2) : array();
		@list($page, $channel) = $locParts;
		$locCount = count($locParts);
		
		switch ($locCount) {

			default:
			case 0:
				$page = 1;
			
			case 2:
				// we shouldn't be here, $locCount = 2 means that we have a 
				// selected channelid in location. We should be in browse/mode
			case 1:
				$this->fetchChannels($items, $page);
				break;
			
			
		}
		
		return $items;
	}
	
	protected function fetchChannels(X_Page_ItemList_PItem $items, $page = 1) {
		
		$json = $this->_loadPage(self::CHANNELS_INDEX, $this->config('channels.cache.validity', 3));
		
		$channels = Zend_Json::decode($json);
		
		$totalPageCount = $this->helpers()->paginator()->getPages($channels);
		
		if ( $this->helpers()->paginator()->hasPrevious($channels, $page) ) {
			$item = new X_Page_Item_PItem('previouspage', X_Env::_("previouspage", ($page - 1), $totalPageCount));
			$item//->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', ($page - 1))
				->setLink(array(
					'l'	=>	X_Env::encode(($page - 1))
				), 'default', false);
			$items->append($item);
		}
		
		foreach ( $this->helpers()->paginator()->getPage($channels, $page) as $channel ) {
			$item = new X_Page_Item_PItem($this->getId().'-'.$channel['channelId'], $channel['title']);
			$item->setIcon('/images/icons/hosters/veetle.png')
				->setThumbnail(@$channel['logo']['lg'] ? @$channel['logo']['lg'] : @$channel['logo']['sm'])
				->setDescription(@$channel['description'])
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setCustom(__CLASS__.':location', "$page/veetle:{$channel['channelId']}")
				->setLink(array(
					'action' => 'mode',
					'l'	=>	X_Env::encode("$page/veetle:{$channel['channelId']}")
				), 'default', false);
			$items->append($item);
		}
		
		if ( $this->helpers()->paginator()->hasNext($channels, $page) ) {
			$item = new X_Page_Item_PItem('nextpage', X_Env::_("nextpage", ($page + 1), $totalPageCount));
			$item//->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', ($page + 1))
				->setLink(array(
					'l'	=>	X_Env::encode(($page + 1))
				), 'default', false);
			$items->append($item);
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
		
		if ( $url != false ) {
			
			$link = new X_Page_Item_PItem('core-directwatch', X_Env::_('watchdirectly'));
			$link->setIcon('/images/icons/play.png')
				->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
				->setLink($url);
			return new X_Page_ItemList_PItem(array($link));
		} else {
			X_Debug::i('Setting priority to filterModeItems');
			$this->setPriority('filterModeItems', 99);
			
			$link = new X_Page_Item_PItem('invalidvideo', X_Env::_('invalidlink'));
			$link->setIcon('/images/msg_error.png')
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(array (
					'controller' => 'browse',
					'action' => 'share',
					'p'	=> $this->getId(),
					'l' => X_Env::encode($location),
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
	 * Set the source param into vlc params
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
	 * Add multioptions for server ip list
	 * @param string $section
	 * @param string $namespace
	 * @param unknown_type $key
	 * @param Zend_Form_Element $element
	 * @param Zend_Form $form
	 * @param Zend_Controller_Action $controller
	 */
	public function prepareConfigElement($section, $namespace, $key, Zend_Form_Element $element, Zend_Form  $form, Zend_Controller_Action $controller) {
		// nothing to do if this isn't the right section
		if ( $namespace != $this->getId() ) return;
		
		switch ($key) {
			// add multioptions for veetle server ip selection
			case 'plugins_veetle_server_ip':
				if ( $element instanceof Zend_Form_Element_Select ) {
					$element->setMultiOptions(array(
						'213.254.245.212' => '213.254.245.212'
					));
				}
				break;
		}
		
	}	
	
	
	/**
	 * Add the link for -configure-veetle-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_veetle_mlink'));
		$link->setTitle(X_Env::_('p_veetle_managetitle'))
			->setIcon('/images/veetle/logo.jpg')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'veetle'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}

	/**
	 * Load an $uri performing an http request (or from cache if possible/allowed)
	 */
	private function _loadPage($uri, $validityCache = 0) {
		$cachePlugin = null;
		if ( $validityCache > 0 ) {
			try {
				/* @var $cachePlugin X_VlcShares_Plugins_Cache */
				$cachePlugin = X_VlcShares_Plugins::broker()->getPlugins('cache');
				X_Debug::i("Retrieving cache entry for {{$uri}}");
				return $cachePlugin->retrieveItem($uri);
			} catch (Exception $e) {
				X_Debug::i("No valid cache entry for $uri");
			}
		}
		
		X_Debug::i("Loading page $uri");
		
		$http = new Zend_Http_Client($uri, array(
			'maxredirects'	=> $this->config('request.maxredirects', 10),
			'timeout'		=> $this->config('request.timeout', 25)
			//'keepalive' => true
		));
		
		$http->setHeaders(array(
			$this->config('hide.useragent', false) ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION .' veetle/'.self::VERSION_CLEAN : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1',
			//'Content-Type: application/x-www-form-urlencoded'
		));
		
		$response = $http->request();
		$htmlString = $response->getBody();

		if ( $validityCache > 0 && !is_null($cachePlugin) ) {
			X_Debug::i("Caching page {{$uri}} with validity {{$validityCache}}");
			$cachePlugin->storeItem($uri, $htmlString, (int) $validityCache);
		}
		
		return $htmlString;
	}	
	
}
