<?php 

class X_VlcShares_Plugins_Own3d extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const VERSION = '0.1beta';
	const VERSION_CLEAN = '0.1';
	
	const URL_SOURCE = 'http://www.own3d.tv/live/?all';
	
	const LOCATION = '/^(?P<channel>.+)$';
	
	// pattern: #<a href="/live/(?P<ID>[0-9]+)" class="font-size_14 font-farbe_1" title="(?P<LABEL>[^\"]+?)" >#
	
	//{{{NODE DEFINITION
	private $nodes = array(
		'exact:' => array(
				'function'	=> 'menuChannels',
				'params'	=> array()
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
		if ( $this->helpers()->rtmpdump()->isEnabled() ) {
			$this->setPriority('getCollectionsItems');
			$this->helpers()->hoster()->registerHoster(new X_VlcShares_Plugins_Helper_Hoster_Own3dLive(array(
				'quality' => $this->config('default.quality', '2'),
				'base' => $this->config('cdn.base', '${cdn2}')
			)));
		}
		
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

		list($hoster, $id) = explode(':', $location, 2);
		
		try {
			$return = $this->helpers()->hoster()->getHoster($hoster)->getPlayable($id, true);
		} catch (Exception $e) {
			X_Debug::e("Own3dLive hoster error: {$e->getMessage()}");
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
		
		return null;
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
			} else {
				if ( !$this->helpers()->rtmpdump()->isEnabled() ) {
					$messages->append(X_VlcShares_Plugins_Utils::getMessageEntry(
							$this->getId(), 
							'p_own3d_err_rtmpdump_disabled',
							X_Page_Item_Message::TYPE_WARNING
					));
				}
			}
		} else {
			$message = new X_Page_Item_Message($this->getId(),"PageParser API is required from Own3d. Please, install PageParserLib plugin (0.1alpha2 or later required)");
			$message->setType(X_Page_Item_Message::TYPE_FATAL);
			$messages->append($message);
		}
		return $messages;
	}

	
	public function menuChannels(X_Page_ItemList_PItem $items) {
		
		/* @var $parsed SimpleXmlElement */
		$page = $this->getChannelsListRaw();
		
		$parser = new X_PageParser_Parser_Own3dLive();
		$parsed = $parser->parse($page);
		
		if ( !$parsed ) return;
		
		foreach ($parsed as $match) {

			/* @var $match array */
			
			$id = "{$match['hoster']}:{$match['id']}";
			$label = $match['label'];
			
			$item = new X_Page_Item_PItem($this->getId()."-{$id}", $label );
			$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setCustom(__CLASS__.':location', "$id")
				->setDescription(APPLICATION_ENV == 'development' ? "$id" : null)
				->setIcon("/images/icons/hosters/own3dlive.png")
				->setLink(array(
					'action' => 'mode',
					'l'	=>	X_Env::encode("$id")
				), 'default', false);
			
			$items->append($item);
			
		}
		
	}
	
	private function getChannelsListRaw() {
		
		try {
			/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
			$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
	
			$response = $cacheHelper->retrieveItem("own3d::channelslist");
			X_Debug::i("Valid cache entry found: $response");
			return $response;
	
		} catch (Exception $e) {
			// no cache plugin or no entry in cache, it's the same
			X_Debug::i("Cache disabled or no valid entry found");
		}
	
		$http = new Zend_Http_Client($this->config('source', self::URL_SOURCE), array(
			'timeout'	=> $this->config('request.timeout', 25)
		));
		
		$http->setHeaders(array(
			$this->config('hide.useragent', false) ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION .' own3d/'.self::VERSION : 'User-Agent: Mozilla',
		));
		
		
		X_Debug::i("Fetching: ".$this->config('source', self::URL_SOURCE));
		
		$list = $http->request()->getBody(); 
		
		try {
			/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
			$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
		
			$cacheHelper->storeItem("own3d::channelslist", $list, 15); // store for the next 15 min
		
			X_Debug::i("Value stored in cache for 15 min: {key = own3d::channelslist, value = %OMISSIS%}");
		
		} catch (Exception $e) {
			// no cache plugin, next time i have to repeat the request
		}
		
		return $list;
	
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
