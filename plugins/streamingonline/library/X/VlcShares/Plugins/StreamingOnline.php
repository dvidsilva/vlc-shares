<?php 

class X_VlcShares_Plugins_StreamingOnline extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const VERSION = '0.1beta';
	const VERSION_CLEAN = '0.1';
	
	const TYPE_MOVIESLAST = 'LastMovies';
	const TYPE_MOVIES = 'Movies';
	const TYPE_TVSHOWSLAST = 'LastTv';
	const TYPE_TVSHOWS = 'Tv';
	const TYPE_ANIMELAST = 'LastAnime';
	const TYPE_ANIME = 'Anime';
	const TYPE_UPDATES = 'Updates';
	
	const URL_MOVIES_INDEX_MONTH = 'http://www.streaming-online.biz/film/?m=%s&paged=%s';
	const URL_MOVIES_INDEX_NEW = 'http://www.streaming-online.biz/film/?paged=%s';
	const URL_MOVIES_INDEX_AZ = 'http://www.streaming-online.biz/film/';
	
	const URL_TVSHOWS_INDEX_NEW = 'http://www.streaming-online.biz/telefilm/?paged=%s';
	const URL_TVSHOWS_INDEX_AZ = 'http://www.streaming-online.biz/telefilm/?cat=1';
	
	const URL_ANIME_INDEX_NEW = 'http://www.streaming-online.biz/anime/';
	const URL_ANIME_INDEX_AZ = 'http://www.streaming-online.biz/anime/?cat=1';
	
	const URL_UPDATES_INDEX = 'http://www.streaming-online.biz/telefilm/';	
	
	const PATTERN_UPDATES ='/<p align="center"><a href="http\:\/\/www\.streaming\-online\.biz\/(?P<category>[^\/]+?)\/\?p\=(?P<id>[^\"]+?)">(?<label>[^\<]+?)<\/a><\/p>/i';
	
	const PATTERN_MOVIESLAST = '#<div class="item">.*?<img.*?src="(?P<image>[^\"]+)".*?<a href="http://www.streaming-online.biz/(?P<category>[^\/]+)/\?p=(?P<id>[^\"]+)">(?P<label>[^\<]*)<\/a>#si';
	const PATTERN_MOVIESLAST_NEXTPAGE = '#<a href="http\:\/\/www\.streaming\-online\.biz\/[^\/]+?\/\?paged=[0-9]+"[^\>]*>(?P<label>[^\&\<]+)&raquo;[^\<]*?</a>[^\<]*<\/(p|center)>#si';
		
	const PATTERN_MOVIESCATEGORIES = '#<li><a href=\'http\:\/\/www\.streaming\-online\.biz\/film\/\?m\=(?P<subtype>[^\']+?)\' title\=\'(?P<label>[^\']+?)\'>[^<]+?<\/a><\/li>#si';
	
	const PATTERN_MOVIESINCATEGORY = '#<h5 class="entrytitle".*?href="http://www.streaming-online.biz/(?P<category>[^\/]+)/\?p=(?P<id>[^\"]+)"[^\>]*?>(?P<label>[^\<]*?)<\/a>#si';
	const PATTERN_MOVIESINCATEGORY_NEXTPAGE = '#<a href="http\:\/\/www\.streaming\-online\.biz\/[^\/]+?\/\?(m|cat)=[^\&]+?\&\#038\;paged\=[0-9]+"[^\>]*>(?P<label>[^\&\<]+)&raquo;[^\<]*?<\/a>[^\<]*?<\/p>#si';
	//const PATTERN_MOVIESINCATEGORY_PREVPAGE = '#<p>[^\<]*<a href="http\:\/\/www\.streaming\-online\.biz\/film\/\?m=[^\"]+?"[^\>]*>&laquo; (?P<label>[^\&\<]+)</a>#sim';
	
	const URL_BASE = 'http://www.streaming-online.biz/%s/?p=%s';
	
	protected $cachedLocation = array();
	
	function __construct() {
		if ( class_exists('X_VlcShares_Plugins_Utils') ) {
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

		$split = $location != '' ? @explode('/', $location, 5) : array();
		@list($resourceType, $page, $resourceGroup, $resourceId, $videoId) = $split;

		// videoId overwritted by real videoId
		@list($videoType, $videoId) = @explode(':', $videoId, 2);

		X_Debug::i("Type: $resourceType, Group: $resourceGroup, Page: $page, Resource: $resourceId, VideoType: $videoType, VideoId: $videoId");
		
		if ( $videoType == null || $videoId == null ) {
			// location isn't a valid video url, so we return fals
			// and insert the query result in the cache
			$this->cachedLocation[$location] = false;
			return false;	
		}

		// new hoster helper
		try {
			$return = $this->helpers()->hoster()->getHoster($videoType)->getPlayable($videoId, true);
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
		
		// type/group/page/idres/idvideo
		if ( count($exploded) == 4 || (count($exploded) == 2 && $exploded[1] == '1' ) ) {
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
		X_Debug::i("Requested location: $location");
		
		// location format:
		// resourceType/resourceGroup/page/resourceId/videoId
		
		$split = $location != '' ? @explode('/', $location, 5) : array();
		@list($resourceType, $page, $resourceGroup, $resourceId, $videoId) = $split;
		
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
				$this->_fetchVideos($items, $resourceType, $page, $resourceGroup, $resourceId);
				break;
			case 1:
				$page = 1;
				// delegate to fetchGroups$Type
			case 3: // no $resourceGroup: so back to this;
			case 2:
				if ( method_exists($this, "_fetchGroup$resourceType") ) {
					$this->{"_fetchGroup$resourceType"}($items, $resourceType, $page);
				}
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
			if ( count(X_VlcShares_Plugins::helpers()->hoster()->getHosters()) < 1	 ) {
				$messages->append(X_VlcShares_Plugins_Utils::getMessageEntry(
						$this->getId(),
						'p_streamingonline_warning_nohosters',
						X_Page_Item_Message::TYPE_ERROR
				));
			}
		} else {
			$message = new X_Page_Item_Message($this->getId(),"PageParser API is required from Streaming-Online. Please, install PageParserLib plugin (alpha version)");
			$message->setType(X_Page_Item_Message::TYPE_FATAL);
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
			self::TYPE_MOVIESLAST 	=> X_Env::_('p_streamingonline_type_movieslast'),
			self::TYPE_MOVIES 		=> X_Env::_('p_streamingonline_type_movies'),
			self::TYPE_TVSHOWSLAST 	=> X_Env::_('p_streamingonline_type_tvshowslast'),
			self::TYPE_TVSHOWS 		=> X_Env::_('p_streamingonline_type_tvshows'),
			self::TYPE_ANIMELAST	=> X_Env::_('p_streamingonline_type_animelast'),
			self::TYPE_ANIME 		=> X_Env::_('p_streamingonline_type_anime'),
			self::TYPE_UPDATES 		=> X_Env::_('p_streamingonline_type_updates'),
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
	
	private function _fetchGroupLastMovies(X_Page_ItemList_PItem $items, $resourceType, $pageN = 1) {
		
		$page = X_PageParser_Page::getPage(
				sprintf(self::URL_MOVIES_INDEX_NEW, $pageN), 
				X_PageParser_Parser_Preg::factory(self::PATTERN_MOVIESLAST, X_PageParser_Parser_Preg::PREG_MATCH_ALL, PREG_SET_ORDER)
		);
		$this->preparePageLoader($page);
		$parsed = $page->getParsed();
		
		if ( $pageN > 1 ) {
			// append "back" as first
			$prevPage = $pageN-1;
			$items->append(X_VlcShares_Plugins_Utils::getPreviousPage("$resourceType/$prevPage", $prevPage));
		}
		
		// $parsed format = array(array('image', 'category', 'id', 'label'),..)
		foreach ($parsed as $pItem) {
			
			$thumb = $pItem['image'];
			$group = $pItem['category'];
			$label = $pItem['label'];
			$id = $pItem['id'];
			
			X_Debug::i("Parsed items: ".var_export(array($id, $group, $label, $thumb), true));
			
			$item = new X_Page_Item_PItem($this->getId()."-{$resourceType}-{$group}-{$id}", $label );
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$resourceType/$pageN/$group/$id")
				->setDescription(APPLICATION_ENV == 'development' ? "$resourceType/$pageN/$group/$id" : null)
				->setThumbnail($thumb)
				->setLink(array(
					'l'	=>	X_Env::encode("$resourceType/$pageN/$group/$id")
				), 'default', false);
				
			$items->append($item);
		}
		
		if ( count($page->getParsed(X_PageParser_Parser_Preg::factory(self::PATTERN_MOVIESLAST_NEXTPAGE, X_PageParser_Parser_Preg::PREG_MATCH))) ) {
			// append "next" as last
			$nextPage = $pageN + 1;
			$items->append(X_VlcShares_Plugins_Utils::getNextPage("$resourceType/$nextPage", $nextPage));
		}
		
	}
	
	private function _fetchGroupMovies(X_Page_ItemList_PItem $items, $resourceType, $subcat = 1) {
	
		@list($month, $pageN) = @explode(',', $subcat);
		if ( $subcat === 1 ) {
			$page = X_PageParser_Page::getPage(
					self::URL_MOVIES_INDEX_AZ,
					X_PageParser_Parser_Preg::factory(self::PATTERN_MOVIESCATEGORIES, X_PageParser_Parser_Preg::PREG_MATCH_ALL, PREG_SET_ORDER)
			);
		} else {
			// cat setted... check the group
			if ( !$pageN ) $pageN = "1";
			$page = X_PageParser_Page::getPage(
					sprintf(self::URL_MOVIES_INDEX_MONTH, $month, $pageN),
					X_PageParser_Parser_Preg::factory(self::PATTERN_MOVIESINCATEGORY, X_PageParser_Parser_Preg::PREG_MATCH_ALL, PREG_SET_ORDER)
			);			
		}
		$this->preparePageLoader($page);
		
		$parsed = $page->getParsed();

		if ( $pageN > 1 ) {
			// append "back" as first
			$prevPage = $pageN-1;
			$items->append(X_VlcShares_Plugins_Utils::getPreviousPage("$resourceType/{$month},{$prevPage}", $prevPage));
		}
		
		// $parsed format = array(array('image', 'category', 'id', 'label'),..)
		foreach ($parsed as $pItem) {
			
			$label = $pItem['label'];
			$subtype = @$pItem['subtype'] ? @$pItem['subtype'] : "{$month},{$pageN}" ;
			$group = @$pItem['category'] ? "/{$pItem['category']}" : '';
			$id = @$pItem['id'] ? "/{$pItem['id']}" : '';
			
	
			X_Debug::i("Parsed items: ".var_export(array($id, $subtype, $group, $label), true));
	
			$item = new X_Page_Item_PItem($this->getId()."-{$resourceType}-{$subtype}-{$group}-{$id}", $label );
			$item->setIcon('/images/icons/folder_32.png')
			->setType(X_Page_Item_PItem::TYPE_CONTAINER)
			->setCustom(__CLASS__.':location', "$resourceType/$group")
			->setDescription(APPLICATION_ENV == 'development' ? "$resourceType/{$subtype}{$group}{$id}" : null)
			->setLink(array(
					'l'	=>	X_Env::encode("$resourceType/{$subtype}{$group}{$id}")
			), 'default', false);
	
			$items->append($item);
		}
		
		if ( $subcat !== 1 ) {
			if ( count($page->getParsed(X_PageParser_Parser_Preg::factory(self::PATTERN_MOVIESINCATEGORY_NEXTPAGE, X_PageParser_Parser_Preg::PREG_MATCH))) ) {
				// append "next" as last
				$nextPage = $pageN + 1;
				$items->append(X_VlcShares_Plugins_Utils::getNextPage("$resourceType/{$month},{$nextPage}", $nextPage));
			}
		}
	
	}
	

	private function _fetchGroupLastTv(X_Page_ItemList_PItem $items, $resourceType, $pageN = 1) {
	
		$page = X_PageParser_Page::getPage(
				sprintf(self::URL_TVSHOWS_INDEX_NEW, $pageN),
				X_PageParser_Parser_Preg::factory(self::PATTERN_MOVIESLAST, X_PageParser_Parser_Preg::PREG_MATCH_ALL, PREG_SET_ORDER)
		);
		$this->preparePageLoader($page);
		$parsed = $page->getParsed();
	
		if ( $pageN > 1 ) {
			// append "back" as first
			$prevPage = $pageN-1;
			$items->append(X_VlcShares_Plugins_Utils::getPreviousPage("$resourceType/$prevPage", $prevPage));
		}
	
		// $parsed format = array(array('image', 'category', 'id', 'label'),..)
		foreach ($parsed as $pItem) {
	
			$thumb = $pItem['image'];
			$group = $pItem['category'];
			$label = $pItem['label'];
			$id = $pItem['id'];
	
			X_Debug::i("Parsed item: ".var_export(array($id, $group, $label, $thumb), true));
	
			$item = new X_Page_Item_PItem($this->getId()."-{$resourceType}-{$group}-{$id}", $label );
			$item->setIcon('/images/icons/folder_32.png')
			->setType(X_Page_Item_PItem::TYPE_CONTAINER)
			->setCustom(__CLASS__.':location', "$resourceType/$pageN/$group/$id")
			->setDescription(APPLICATION_ENV == 'development' ? "$resourceType/$pageN/$group/$id" : null)
			->setThumbnail($thumb)
			->setLink(array(
					'l'	=>	X_Env::encode("$resourceType/$pageN/$group/$id")
			), 'default', false);
	
			$items->append($item);
		}
		
		if ( count($page->getParsed(X_PageParser_Parser_Preg::factory(self::PATTERN_MOVIESLAST_NEXTPAGE, X_PageParser_Parser_Preg::PREG_MATCH))) ) {
			// append "next" as last
			$nextPage = $pageN + 1;
			$items->append(X_VlcShares_Plugins_Utils::getNextPage("$resourceType/$nextPage", $nextPage));
		}
	
	}
	
	private function _fetchGroupTv(X_Page_ItemList_PItem $items, $resourceType, $pageN = 1) {
	
		$page = X_PageParser_Page::getPage(
					sprintf(self::URL_TVSHOWS_INDEX_AZ, $pageN),
					X_PageParser_Parser_Preg::factory(self::PATTERN_MOVIESINCATEGORY, X_PageParser_Parser_Preg::PREG_MATCH_ALL, PREG_SET_ORDER)
			);
		$this->preparePageLoader($page);
	
		$parsed = $page->getParsed();
	
		if ( $pageN > 1 ) {
			// append "back" as first
			$prevPage = $pageN-1;
			$items->append(X_VlcShares_Plugins_Utils::getPreviousPage("$resourceType/{$prevPage}", $prevPage));
		}
	
		// $parsed format = array(array('image', 'category', 'id', 'label'),..)
		foreach ($parsed as $pItem) {
	
			$label = $pItem['label'];
			$subtype = $pageN;
			$group = @$pItem['category'] ? "/{$pItem['category']}" : '';
			$id = @$pItem['id'] ? "/{$pItem['id']}" : '';
	
	
			X_Debug::i("Parsed items: ".var_export(array($id, $subtype, $group, $label), true));
	
			$item = new X_Page_Item_PItem($this->getId()."-{$resourceType}-{$subtype}-{$group}-{$id}", $label );
			$item->setIcon('/images/icons/folder_32.png')
			->setType(X_Page_Item_PItem::TYPE_CONTAINER)
			->setCustom(__CLASS__.':location', "$resourceType/$group")
			->setDescription(APPLICATION_ENV == 'development' ? "$resourceType/{$subtype}{$group}{$id}" : null)
			->setLink(array(
					'l'	=>	X_Env::encode("$resourceType/{$subtype}{$group}{$id}")
					), 'default', false);
	
			$items->append($item);
		}
	
		if ( count($page->getParsed(X_PageParser_Parser_Preg::factory(self::PATTERN_MOVIESINCATEGORY_NEXTPAGE, X_PageParser_Parser_Preg::PREG_MATCH))) ) {
			// append "next" as last
			$nextPage = $pageN + 1;
			$items->append(X_VlcShares_Plugins_Utils::getNextPage("$resourceType/{$nextPage}", $nextPage));
		}
	
	}	
	
	
	private function _fetchGroupLastAnime(X_Page_ItemList_PItem $items, $resourceType, $pageN = 1) {
	
		$page = X_PageParser_Page::getPage(
				sprintf(self::URL_ANIME_INDEX_NEW, $pageN),
				X_PageParser_Parser_Preg::factory(self::PATTERN_MOVIESLAST, X_PageParser_Parser_Preg::PREG_MATCH_ALL, PREG_SET_ORDER)
		);
		$this->preparePageLoader($page);
		$parsed = $page->getParsed();
	
		if ( $pageN > 1 ) {
			// append "back" as first
			$prevPage = $pageN-1;
			$items->append(X_VlcShares_Plugins_Utils::getPreviousPage("$resourceType/$prevPage", $prevPage));
		}
	
		// $parsed format = array(array('image', 'category', 'id', 'label'),..)
		foreach ($parsed as $pItem) {
	
			$thumb = $pItem['image'];
			// fix for relative path:
			if ( X_Env::startWith($thumb, '../') ) $thumb = self::URL_ANIME_INDEX_NEW.$thumb;
			$group = $pItem['category'];
			$label = $pItem['label'];
			$id = $pItem['id'];
	
			X_Debug::i("Parsed item: ".var_export(array($id, $group, $label, $thumb), true));
	
			$item = new X_Page_Item_PItem($this->getId()."-{$resourceType}-{$group}-{$id}", $label );
			$item->setIcon('/images/icons/folder_32.png')
			->setType(X_Page_Item_PItem::TYPE_CONTAINER)
			->setCustom(__CLASS__.':location', "$resourceType/$pageN/$group/$id")
			->setDescription(APPLICATION_ENV == 'development' ? "$resourceType/$pageN/$group/$id" : null)
			->setThumbnail($thumb)
			->setLink(array(
					'l'	=>	X_Env::encode("$resourceType/$pageN/$group/$id")
			), 'default', false);
	
			$items->append($item);
		}
	
		if ( count($page->getParsed(X_PageParser_Parser_Preg::factory(self::PATTERN_MOVIESLAST_NEXTPAGE, X_PageParser_Parser_Preg::PREG_MATCH))) ) {
			// append "next" as last
			$nextPage = $pageN + 1;
			$items->append(X_VlcShares_Plugins_Utils::getNextPage("$resourceType/$nextPage", $nextPage));
		}
	
	}
	
	private function _fetchGroupAnime(X_Page_ItemList_PItem $items, $resourceType, $pageN = 1) {
	
		$page = X_PageParser_Page::getPage(
				sprintf(self::URL_ANIME_INDEX_AZ, $pageN),
				X_PageParser_Parser_Preg::factory(self::PATTERN_MOVIESINCATEGORY, X_PageParser_Parser_Preg::PREG_MATCH_ALL, PREG_SET_ORDER)
		);
		$this->preparePageLoader($page);
	
		$parsed = $page->getParsed();
	
		if ( $pageN > 1 ) {
			// append "back" as first
			$prevPage = $pageN-1;
			$items->append(X_VlcShares_Plugins_Utils::getPreviousPage("$resourceType/{$prevPage}", $prevPage));
		}
	
		// $parsed format = array(array('image', 'category', 'id', 'label'),..)
		foreach ($parsed as $pItem) {
	
			$label = $pItem['label'];
			$subtype = $pageN;
			$group = @$pItem['category'] ? "/{$pItem['category']}" : '';
			$id = @$pItem['id'] ? "/{$pItem['id']}" : '';
	
	
			X_Debug::i("Parsed items: ".var_export(array($id, $subtype, $group, $label), true));
	
			$item = new X_Page_Item_PItem($this->getId()."-{$resourceType}-{$subtype}-{$group}-{$id}", $label );
			$item->setIcon('/images/icons/folder_32.png')
			->setType(X_Page_Item_PItem::TYPE_CONTAINER)
			->setCustom(__CLASS__.':location', "$resourceType/$group")
			->setDescription(APPLICATION_ENV == 'development' ? "$resourceType/{$subtype}{$group}{$id}" : null)
			->setLink(array(
					'l'	=>	X_Env::encode("$resourceType/{$subtype}{$group}{$id}")
					), 'default', false);
	
			$items->append($item);
		}
	
		if ( count($page->getParsed(X_PageParser_Parser_Preg::factory(self::PATTERN_MOVIESINCATEGORY_NEXTPAGE, X_PageParser_Parser_Preg::PREG_MATCH))) ) {
			// append "next" as last
			$nextPage = $pageN + 1;
			$items->append(X_VlcShares_Plugins_Utils::getNextPage("$resourceType/{$nextPage}", $nextPage));
		}
	
	}	

	
	private function _fetchGroupUpdates(X_Page_ItemList_PItem $items, $resourceType, $pageN = 1) {
	
		$page = X_PageParser_Page::getPage(
				self::URL_UPDATES_INDEX,
				X_PageParser_Parser_Preg::factory(self::PATTERN_UPDATES, X_PageParser_Parser_Preg::PREG_MATCH_ALL, PREG_SET_ORDER)
		);
		$this->preparePageLoader($page);
		$parsed = $page->getParsed();
	
		// $parsed format = array(array('image', 'category', 'id', 'label'),..)
		foreach ($parsed as $pItem) {
	
			$group = $pItem['category'];
			$label = $pItem['label'];
			$id = $pItem['id'];
	
			X_Debug::i("Parsed item: ".var_export(array($id, $group, $label), true));
	
			$item = new X_Page_Item_PItem($this->getId()."-{$resourceType}-{$group}-{$id}", $label );
			$item->setIcon('/images/icons/folder_32.png')
			->setType(X_Page_Item_PItem::TYPE_CONTAINER)
			->setCustom(__CLASS__.':location', "$resourceType/$pageN/$group/$id")
			->setDescription(APPLICATION_ENV == 'development' ? "$resourceType/$pageN/$group/$id" : null)
			->setLink(array(
					'l'	=>	X_Env::encode("$resourceType/$pageN/$group/$id")
			), 'default', false);
	
			$items->append($item);
		}
	
	}	
	
	private function _fetchVideos(X_Page_ItemList_PItem $items, $resourceType, $pageN, $resourceGroup, $resourceId) {
		
		X_Debug::i("Fetching videos for $resourceType, $pageN, $resourceGroup, $resourceId");
		
		// as first thing we have to recreate the resource url from resourceId
		$url = sprintf(self::URL_BASE, $resourceGroup, $resourceId);
		
		$page = X_PageParser_Page::getPage($url, X_PageParser_Parser_StreamingOnlineVideos::instance()); 
		$this->preparePageLoader($page);
		
		foreach ($page->getParsed() as $link) {
			$videoId = $link['videoId'];
			$hosterId = $link['hosterId'];
			$videoCode = $link['link'];
			$videoLabel = "{$link['label']} [{$link['hosterId']}]";
			$videoThumb = $link['thumbnail'];
			
			$item = new X_Page_Item_PItem("{$this->getId()}-hoster-{$hosterId}-{$videoId}", $videoLabel);
			$item->setIcon('/images/icons/file_32.png')
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setCustom(__CLASS__.':location', "$resourceType/$pageN/$resourceGroup/$resourceId/$videoCode")
				->setLink(array(
					'action'	=> 'mode',
					'l'	=>	X_Env::encode("$resourceType/$pageN/$resourceGroup/$resourceId/$videoCode")
				), 'default', false);
				
			if ( APPLICATION_ENV == 'development' ) {
				$item->setDescription("$resourceType/$pageN/$resourceGroup/$resourceId/$videoCode");
			}
			
			if ( $videoThumb ) {
				$item->setThumbnail($videoThumb);
			}
			
			$items->append($item);
		}
		
	}	
	
	
	private function preparePageLoader(X_PageParser_Page $page) {

		$loader = $page->getLoader();
		if ( $loader instanceof X_PageParser_Loader_Http || $loader instanceof X_PageParser_Loader_HttpAuthRequired ) {
		
			$http = $loader->getHttpClient()->setConfig(array(
				'maxredirects'	=> $this->config('request.maxredirects', 10),
				'timeout'		=> $this->config('request.timeout', 25)
			));
			
			$http->setHeaders(array(
				$this->config('hide.useragent', false) ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION .' streamingonline/'.self::VERSION_CLEAN : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1',
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
