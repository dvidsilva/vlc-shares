<?php 


class X_VlcShares_Plugins_MyP2P extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
    const VERSION = '0.1';
    const VERSION_CLEAN = '0.1';
    
    const GROUP_LIVE = 'l';
    const GROUP_EVENTS = 'e';
    
    const INDEX_LIVE = 'http://www.myp2p.eu/channel.php';
    const INDEX_EVENTS = 'http://www.myp2p.eu/index.php?part=sports';
    
    const INDEX_MATCH = 'http://www.myp2p.eu/broadcast.php?part=sports&matchid=';
    
	function __construct() {
		
		$this
			->setPriority('gen_beforeInit')
			->setPriority('getCollectionsItems')
			->setPriority('getShareItems')
			->setPriority('preGetModeItems')
			->setPriority('preRegisterVlcArgs')
			;
		
	}
	
	
	/**
	 * Registers a language file
	 */
	public function gen_beforeInit(Zend_Controller_Action $controller) {
		
		$this->helpers()->language()->addTranslation(__CLASS__);
		
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
		// $group/$thread/$hoster:$id
		$locParts = explode('/', $location, 3);
		@list($group, $thread, $item) = $locParts;
		$locCount = count($locParts);
		
		if ( $locCount != 3 ) return false;
		
		@list($hoster, $id) = explode(':', $item, 2);
		
		// we use the new hoster helper api
		if ( $hoster == 'direct-url' ) {
			return $id;
		} else {
			try {
				return $this->helpers()->hoster()->getHoster($hoster)->getPlayable($id, true);
			} catch ( Exception $e) {
				return false;
			}
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
		$locParts = $location != '' ? explode('/', $location, 3) : array();
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

		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_myp2p_collectionindex'));
		$link->setIcon('/images/myp2p/logo.jpg')
			->setDescription(X_Env::_('p_myp2p_collectionindex_desc'))
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
		// $group/$thread/$hoster:$id
		$locParts = $location != '' ? explode('/', $location, 3) : array();
		@list($group, $thread, $item) = $locParts;
		$locCount = count($locParts);
		
		switch ($locCount) {

			
			case 2:
				$this->fetchItems($items, $group, $thread);
				break;
			case 1:
				$this->fetchThreads($items, $group);
				break;
			default:
			case 0:
				$this->disableCache();
				$this->fetchGroups($items);
				break;
			
			
		}
		
		return $items;
	}
	
	protected function fetchGroups(X_Page_ItemList_PItem $items) {
		
		$groups = array(
			array(
				'title'		=> X_Env::_('p_myp2p_group_livechannels'),
				'l'			=> self::GROUP_LIVE,
			),
			array(
				'title'		=> X_Env::_('p_myp2p_group_events'),
				'l'			=> self::GROUP_EVENTS,
			),
		);
		
		foreach ($groups as $group) {
			$item = new X_Page_Item_PItem($this->getId()."-group-{$group['l']}", $group['title']);
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "{$group['l']}")
				->setLink(array(
					'l'	=>	X_Env::encode("{$group['l']}")
				), 'default', false);
			$items->append($item);
		}
		
	}
	
	protected function fetchThreads(X_Page_ItemList_PItem $items, $group) {
		
		if ( $group == self::GROUP_LIVE ) {
			
			// LIVE CHANNELS PAGE
			
			$html = $this->_loadPage(self::INDEX_LIVE, 10);
			$pattern = '/<td class\=\"competition\">(?P<label>[^\<]{1}[^\<]*)<\/td>/';
			$matches = array();
			
			if ( preg_match_all($pattern, $html, $matches, PREG_SET_ORDER) ) {
				
				foreach ($matches as $i => $match ) {
					
					$item = new X_Page_Item_PItem("{$this->getId()}-live-{$i}", $match['label']);
					$item->setIcon('/images/icons/folder_32.png')
						->setType(X_Page_Item_PItem::TYPE_CONTAINER)
						->setCustom(__CLASS__.":location", "{$group}/{$i}" )
						->setLink(array(
							'l' =>	X_Env::encode("{$group}/{$i}")
						), 'default', false);
					$items->append($item);
				} 
				
			} else {
				X_Debug::e("Group pattern failure {{$pattern}}");
			}
			
			
		} elseif( $group == self::GROUP_EVENTS ) {
			
			// EVENTS PAGE
			
			$html = $this->_loadPage(self::INDEX_EVENTS, 10);
			
			$pattern = '/<td class\=\"competition\">(?P<label>[^\<]{1}[^\<]*)<\/td>/';
			
			$dom = new Zend_Dom_Query($html);
			
			$results = $dom->queryXpath('//table[@class="itemlist"]//tr[position() > 1]');
			
			while($results->valid()) {
				// 1 item = 2 tr
				$tr1 = $results->current();
				$results->next();
				if ( !$results->valid() ) {
					// this is very unusual, items could be % 2
					break;
				}
				$tr2 = $results->current();
				
				$tr1 = simplexml_import_dom($tr1);
				$tr2 = simplexml_import_dom($tr2);
				
				$label = implode(' - ', array(
					'['.ereg_replace("[^A-Za-z0-9 ]", "", (string) $tr1->td[2]->b[0]->span[0]).']', 
					trim(ereg_replace("[^A-Za-z0-9 ]", "",  $tr2->td[2]->b[0]) .' '. ereg_replace("[^A-Za-z0-9 ]", "", (string) $tr2->td[3] ) .' '. ereg_replace("[^A-Za-z0-9 ]", "", (string) $tr2->td[4]->b[0] )), 
					'('.trim((string) $tr1->td[1]->b[0]).')'
				));
				$href = ((string) $tr1->td[4]->a['href']);
				
				$matches = array();
				if ( preg_match('/broadcast\.php\?matchid\=(?P<id>[0-9]+)\&part\=sports/', $href, $matches) ) {
					
					// we have all info needed
					
					$id = $matches['id'];
					
					$item = new X_Page_Item_PItem("{$this->getId()}-events-{$id}", $label);
					$item->setIcon('/images/icons/folder_32.png')
						->setType(X_Page_Item_PItem::TYPE_CONTAINER)
						->setCustom(__CLASS__.":location", "{$group}/{$id}" )
						->setLink(array(
							'l' =>	X_Env::encode("{$group}/{$id}")
						), 'default', false);
					$items->append($item);
					
				} else {
					X_Debug::w("Match id not found in href {{$href}}");
				}
				
				$results->next();
			}
			
			if ( preg_match_all($pattern, $html, $matches, PREG_SET_ORDER) ) {
				
				foreach ($matches as $i => $match ) {
					
					$item = new X_Page_Item_PItem("{$this->getId()}-live-{$i}", $match['label']);
					$item->setIcon('/images/icons/folder_32.png')
						->setType(X_Page_Item_PItem::TYPE_CONTAINER)
						->setCustom(__CLASS__.":location", "{$group}/{$i}" )
						->setLink(array(
							'l' =>	X_Env::encode("{$group}/{$i}")
						), 'default', false);
					$items->append($item);
				} 
			
			}
		}
	}
	
	protected function fetchItems(X_Page_ItemList_PItem $items, $group, $thread) {
		
		if ( $group == self::GROUP_LIVE ) {
			
			$html = $this->_loadPage(self::INDEX_LIVE, 10);
			$pattern = '/<td class\=\"competition\">(?P<label>[^\<]{1}[^\<]*)<\/td>/';
			$matches = array();
			
			if ( preg_match_all($pattern, $html, $matches, PREG_OFFSET_CAPTURE) ) {
			
				$labels = $matches['label'];
				
				if ( count($labels) <= $thread ) {
					// invalid thread values, emptyyyyyy!!!
					return;
				}
				
				$start = $labels[$thread][1];
				$end = null;
				if ( array_key_exists($thread + 1, $labels)) {
					$end = $labels[$thread + 1][1];
				}
				
				// reducing the search area
				if ( $end !== null ) {
					$html = substr($html, $start, $end - $start);
				} else {
					$html = substr($html, $start);
				}
				
				$pattern = '/window\.open\(\'(?P<url>[^\\\']*)\'\)[^\>]*>(?P<label>[^\<]*)/';
				$matches = array();
				if ( preg_match_all($pattern, $html, $matches, PREG_SET_ORDER) ) {
					
					foreach ($matches as $i => $match ) {
						
						$item = new X_Page_Item_PItem("{$this->getId()}-live-{$thread}-{$i}", $match['label']);
						$item->setIcon('/images/icons/hosters/direct-url.png')
							->setType(X_Page_Item_PItem::TYPE_ELEMENT)
							->setCustom(__CLASS__.":location", "{$group}/{$thread}/direct-url:{$match['url']}" )
							//->setDescription("{$group}/{$thread}/direct-url:{$match['url']}")
							->setLink(array(
								'action' => 'mode',
								'l' =>	X_Env::encode("{$group}/{$thread}/direct-url:{$match['url']}")
							), 'default', false);
						$items->append($item);
					} 
						
				} else {
					X_Debug::e("Thread pattern failure {{$pattern}}");
				}
				
			} else {
				X_Debug::e("Group pattern failure {{$pattern}}");
			}	
		} elseif ( $group == self::GROUP_EVENTS ) {
			
			$html = $this->_loadPage(self::INDEX_MATCH.$thread, 10);
			$pattern = '/<span class\=\"subtext\"><b>(?P<label>[^\<]+)<\/b><\/span><\/td><td width\=\"50\" class\=\"itemlist\_alt.\"><span class\=\"subtext\"><a href\=\"(?P<url>[^\"]+)\" target\=\"\_BLANK\">Play<\/a>/';
			$matches = array();
			if ( preg_match_all($pattern, $html, $matches, PREG_SET_ORDER) ) {
				foreach ($matches as $i => $match ) {
					try {
						$hoster = $this->helpers()->hoster()->findHoster($match['url']);
						
						$id = $hoster->getResourceId($match['url']);
						$hosterId = $hoster->getId();
						$label = "{$match['label']} [" . ucfirst($hosterId) ."]";
						
						$item = new X_Page_Item_PItem("{$this->getId()}-events-{$thread}-{$i}", $label);
						$item->setIcon("/images/icons/hosters/{$hosterId}.png")
							->setType(X_Page_Item_PItem::TYPE_ELEMENT)
							->setCustom(__CLASS__.":location", "{$group}/{$thread}/{$hosterId}:{$id}" )
							//->setDescription("{$group}/{$thread}/{$hosterId}:{$id}")
							->setLink(array(
								'action' => 'mode',
								'l' =>	X_Env::encode("{$group}/{$thread}/{$hosterId}:{$id}")
							), 'default', false);
						$items->append($item);
					} catch ( Exception $e) {
						X_Debug::i("No supported hoster for url {{$match['url']}}");
					}
				} 
			} else {
				X_Debug::e("Thread pattern failure {{$pattern}}");
			}			
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
			$this->config('hide.useragent', false) ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION .' myp2p/'.self::VERSION : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1',
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
	
	private function disableCache() {
		try {
			$cachePlugin = X_VlcShares_Plugins::broker()->getPlugins('cache');
			if ( method_exists($cachePlugin, 'setDoNotCache') ) {
				$cachePlugin->setDoNotCache();
			}
		} catch (Exception $e) {
			// cache plugin not registered, no problem
		}
	}
	
}
