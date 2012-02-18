<?php 


class X_VlcShares_Plugins_SopCast extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface  {
	
    const VERSION = '0.1';
    const VERSION_CLEAN = '0.1';
	
    const CHANNELS_INDEX = "http://www.sopcast.com/gchlxml";
	
	
	function __construct() {
		$this
			->setPriority('gen_afterPluginsInitialized')
			->setPriority('gen_beforeInit');
	}
	
	/**
	 * Register sopcast engine
	 * @see X_VlcShares_Plugins_Abstract::gen_afterPluginsInitialized()
	 */
	public function gen_afterPluginsInitialized(X_VlcShares_Plugins_Broker $broker) {
		if ( $this->helpers()->sopcast()->isEnabled() ) {
			$this->helpers()->streamer()->register(new X_Streamer_Engine_SopCast());
		}
	}
	
	
	public function gen_beforeInit(Zend_Controller_Action $controller) {
		if ( $this->helpers()->sopcast()->isEnabled() ) {
			$this
				// sopcast channels related
				->setPriority('getCollectionsItems')
				->setPriority('getShareItems')
				->setPriority('preGetModeItems')
				->setPriority('preRegisterVlcArgs')
			
				// general sopcast support triggers
				->setPriority('getStreamItems')
				->setPriority('preGetControlItems')
				->setPriority('execute');
			
			$this->helpers()->hoster()->registerHoster(new X_VlcShares_Plugins_Helper_Hoster_SopCast());
				
		}
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
		// $groupPage/$groupId/$itemPage/sopcast:$sopcastUrl
		$locParts = explode('/', $location, 4);
		@list($gPage, $groupId, $iPage, $item) = $locParts;
		$locCount = count($locParts);
		
		if ( $locCount != 4 ) return false;
		
		@list(, $sopcastUrl) = explode(':', $item, 2);
		
		// we use the new hoster helper api
		try {
			return $this->helpers()->hoster()->getHoster('sopcast')->getPlayable($sopcastUrl, true);
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
		// $groupPage/$groupId/$itemPage/sopcast:$sopcastUrl
		$locParts = $location != '' ? explode('/', $location, 4) : array();
		$locCount = count($locParts);
		
		switch ($locCount) {
			case 3:
				array_pop($locParts);
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
	 * Add the main link for sopcast channels
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");

		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_sopcast_collectionindex'));
		$link->setIcon('/images/sopcast/logo.png')
			->setDescription(X_Env::_('p_sopcast_collectionindex_desc'))
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
		
		$urlHelper = $controller->getHelper('url');
		
		X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_SortItems');
		
		$items = new X_Page_ItemList_PItem();

		// location format:
		// $gPage/$groupId/$iPage/sopcast:$sopcastUrl
		$locParts = $location != '' ? explode('/', $location, 5) : array();
		@list($gPage, $groupId, $iPage, $channel) = $locParts;
		$locCount = count($locParts);
		
		switch ($locCount) {

			case 2:
				$iPage = 1;
			case 4:
				// we shouldn't be here
			case 3:
				$this->fetchChannels($items, $gPage, $groupId, $iPage);
				break;
			default:				
			case 0:
				$gPage = 1;
			case 1:
				$this->fetchGroups($items, $gPage);
				break;
			
		}
		
		return $items;
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
	
	protected function fetchGroups(X_Page_ItemList_PItem $items, $page = 1) {
		
		$xml = $this->_loadPage(self::CHANNELS_INDEX, 10);
		
		$dom = new Zend_Dom_Query($xml);
		
		$_groups = $dom->queryXpath('//group');
		
		$groups = array();
		
		while($_groups->valid()) {
			$current = $_groups->current();
			
			$_group = array(
				'id' => $current->getAttribute('id'),
				'title' => $current->getAttribute('en'),
			);
			
			if ( $_group['title'] == '' ) {
				// try to get the title from the first nodetext
				for ( $i = 0; $i < $current->childNodes->length; $i++ ) {
					$_current = $current->childNodes->item($i);
					if ( $_current instanceof DOMText ) {
						$_group['title'] = $_current->nodeValue;
						break;
					}
				}
			}
			
			if ( $_group['id'] != '' && $_group['title'] != '' ) {
				$groups[] = $_group;
			}
			
			$_groups->next();
		}
		
		$totalPageCount = $this->helpers()->paginator()->getPages($groups);
		
		if ( $this->helpers()->paginator()->hasPrevious($groups, $page) ) {
			$item = new X_Page_Item_PItem('previouspage', X_Env::_("previouspage", ($page - 1), $totalPageCount));
			$item//->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', ($page - 1))
				->setLink(array(
					'l'	=>	X_Env::encode(($page - 1))
				), 'default', false);
			$items->append($item);
		}
		
		foreach ( $this->helpers()->paginator()->getPage($groups, $page) as $group ) {
			$item = new X_Page_Item_PItem($this->getId().'-'.$group['id'], $group['title']);
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$page/{$group['id']}")
				->setLink(array(
					'l'	=>	X_Env::encode("$page/{$group['id']}")
				), 'default', false);
			$items->append($item);
		}
		
		if ( $this->helpers()->paginator()->hasNext($groups, $page) ) {
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
	

	protected function fetchChannels(X_Page_ItemList_PItem $items, $gPage = 1, $groupId, $page = 1) {
		
		$xml = $this->_loadPage(self::CHANNELS_INDEX, 10);
		
		$dom = new Zend_Dom_Query($xml);
		
		$_channels = $dom->queryXpath('//group[@id="'. $groupId .'"]/channel');
		
		$channels = array();
		
		while($_channels->valid()) {
			$current = $_channels->current();
			
			$_channel = array(
				'id' => $current->getAttribute('id'),
				//'title' => $current->getAttribute('en'),
			);
			
			try {
				$nameTag = $current->getElementsByTagName('name')->item(0);
				if ( $nameTag->getAttribute('en') != '' ) {
					$_channel['title'] = $nameTag->getAttribute('en');
				} else {
					$_channel['title'] = $nameTag->nodeValue;
				}
			} catch (Exception $e) {
				$_channels->next();
				continue;
			}

			try {
				$sopTag = $current->getElementsByTagName('sop_address')->item(0);
				$_channel['url'] = trim($sopTag->nodeValue);
			} catch (Exception $e) {
				$_channels->next();
				continue;
			}
			
			if ( $_channel['id'] != '' && $_channel['title'] != '' && $_channel['url'] != '' ) {
				$channels[] = $_channel;
			}
			
			$_channels->next();
		}
		
		$totalPageCount = $this->helpers()->paginator()->getPages($channels);
		
		if ( $this->helpers()->paginator()->hasPrevious($channels, $page) ) {
			$item = new X_Page_Item_PItem('previouspage', X_Env::_("previouspage", ($page - 1), $totalPageCount));
			$item//->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$gPage/$groupId/".($page - 1))
				->setLink(array(
					'l'	=>	X_Env::encode("$gPage/$groupId/".($page - 1))
				), 'default', false);
			$items->append($item);
		}
		
		foreach ( $this->helpers()->paginator()->getPage($channels, $page) as $channel ) {
			$item = new X_Page_Item_PItem($this->getId()."-$groupId-".$channel['id'], $channel['title']);
			$item->setIcon('/images/icons/hosters/sopcast.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$gPage/$groupId/$page/sopcast:{$channel['url']}")
				->setDescription("$gPage/$groupId/$page/sopcast:{$channel['url']}")
				->setLink(array(
					'action' => 'mode',
					'l'	=>	X_Env::encode("$gPage/$groupId/$page/sopcast:{$channel['url']}")
				), 'default', false);
			$items->append($item);
		}
		
		if ( $this->helpers()->paginator()->hasNext($channels, $page) ) {
			$item = new X_Page_Item_PItem('nextpage', X_Env::_("nextpage", ($page + 1), $totalPageCount));
			$item//->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$gPage/$groupId/".($page + 1))
				->setLink(array(
					'l'	=>	X_Env::encode("$gPage/$groupId/".($page + 1))
				), 'default', false);
			$items->append($item);
		}
		
		
	}	
	
	
	/**
	 * Add the go to stream link (only if engine is sopcast)
	 * 
	 * @param X_Streamer_Engine $engine selected streamer engine
	 * @param string $uri
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to stream
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return X_Page_ItemList_PItem 
	 */
	public function getStreamItems(X_Streamer_Engine $engine, $uri, $provider, $location, Zend_Controller_Action $controller) {
		
		// ignore the call if streamer is not stopcast
		if ( !($engine instanceof X_Streamer_Engine_SopCast ) ) return; 
		
	
		X_Debug::i('Plugin triggered');
		
		$return = new X_Page_ItemList_PItem();
		
		
		$outputLink = "http://{%SERVER_NAME%}:8902/tv.asf";
		$outputLink = str_replace(
			array(
				'{%SERVER_IP%}',
				'{%SERVER_NAME%}'
			),array(
				$_SERVER['SERVER_ADDR'],
				strstr($_SERVER['HTTP_HOST'], ':') ? strstr($_SERVER['HTTP_HOST'], ':') : $_SERVER['HTTP_HOST']
			), $outputLink
		);
		
		$item = new X_Page_Item_PItem($this->getId(), X_Env::_('p_outputs_gotostream'));
		$item->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
			->setIcon('/images/icons/play.png')
			->setLink($outputLink);
		$return->append($item);		
		

		/*
		$item = new X_Page_Item_PItem('controls-stop', X_Env::_('p_controls_stop'));
		$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setIcon('/images/icons/stop.png')
			->setLink(array(
				'controller'		=>	'controls',
				'action'			=>	'execute',
				'a'					=>	'stop',
				'pid'				=>	$this->getId(),
			), 'default', false);
		$return->append($item);
		*/
		
		
		return $return;
		
	}
	

	/**
	 * Add the button BackToStream in controls page
	 *
	 * @param X_Streamer_Engine $engine
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array
	 */
	public function preGetControlItems(X_Streamer_Engine $engine, Zend_Controller_Action $controller) {
	
		// ignore if the streamer is not vlc
		if ( !($engine instanceof X_Streamer_Engine_RtmpDump ) ) return;

		$outputLink = "http://{%SERVER_NAME%}:8902/tv.asf";
		$outputLink = str_replace(
			array(
				'{%SERVER_IP%}',
				'{%SERVER_NAME%}'
			),array(
				$_SERVER['SERVER_ADDR'],
				strstr($_SERVER['HTTP_HOST'], ':') ? strstr($_SERVER['HTTP_HOST'], ':') : $_SERVER['HTTP_HOST']
			), $outputLink
		);
					
		$item = new X_Page_Item_PItem($this->getId(), X_Env::_('p_profiles_backstream'));
		$item->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
			->setIcon('/images/icons/play.png')
			->setLink($outputLink);
		return new X_Page_ItemList_PItem(array($item));
		
	}

	
	/**
	 * Load an $uri performing an http request (or from cache if possible/allowed)
	 */
	private function _loadPage($uri, $validityCache = 0) {

		if ( $validityCache > 0 ) {
			if ( X_VlcShares_Plugins::broker()->isRegistered('cache') ) {
				/* @var $cachePlugin X_VlcShares_Plugins_Cache */
				$cachePlugin = X_VlcShares_Plugins::broker()->getPlugins('cache');
			}
			try {
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
			$this->config('hide.useragent', false) ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION .' sopcast/'.self::VERSION : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1',
			//'Content-Type: application/x-www-form-urlencoded'
		));
		
		$response = $http->request();
		$htmlString = $response->getBody();

		if ( $validityCache > 0 && $cachePlugin ) {
			X_Debug::i("Caching page {{$uri}} with validity {{$validityCache}}");
			$cachePlugin->storeItem($uri, $htmlString, (int) $validityCache);
		}
		
		return $htmlString;
	}	
	
}
