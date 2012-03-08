<?php

/**
 * Bookmarks plugin
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Bookmarks extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface, X_VlcShares_Plugins_BackuppableInterface {
	
	const VERSION_CLEAN = '0.1';
	const VERSION = '0.1';
	
	const LOCATION = '/^(?P<page>[^\/]+?)\/(?P<url>.+)$/';
	
	private $locationCache = array();
	
	//{{{NODE DEFINITION
	private $nodes = array(
			'exact:' => array(
					'function'	=> 'menuBookmarks',
					'params'	=> array()
			),
			'regex:/^(?P<page>[^\/]+?)$/' => array(
					'function'	=> 'menuLinks',
					'params'	=> array('$page')
			),
		);
	//}}}
	
	public function __construct() {
		$this
			->setPriority('getCollectionsItems')
			->setPriority('preRegisterVlcArgs')
			->setPriority('getShareItems')
			->setPriority('preGetModeItems')
			->setPriority('getIndexActionLinks')
			->setPriority('getIndexStatistics')
			->setPriority('getIndexManageLinks')
			;
	}
	
	/**
	 * Add the main link for bookmarks
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		return X_VlcShares_Plugins_Utils::getCollectionsEntryList($this->getId(), null, null, "/images/bookmarklets/addpage.png");
	}
	
	/**
	 * Menu proxy
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
	 * Fill $items of bookmarks
	 * @param X_Page_ItemList_PItem $items
	 */
	public function menuBookmarks(X_Page_ItemList_PItem $items) {
		$this->disableCache();
		
		$bookmarks = Application_Model_BookmarksMapper::i()->fetchAll();
		
		foreach ( $bookmarks as $bookmark ) {
			/* @var $bookmark Application_Model_Bookmark */
			$item = new X_Page_Item_PItem($this->getId().'-'.$bookmark->getId(), $bookmark->getTitle() );
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', $bookmark->getId())
				->setLink(array(
					'l'	=>	X_Env::encode($bookmark->getId())
				), 'default', false);
			$items->append($item);
		}
		
	} 
	
	/**
	 * Fill $items of links in page
	 * @param X_Page_ItemList_PItem $items
	 */
	public function menuLinks(X_Page_ItemList_PItem $items, $id) {
			
		$bookmark = new Application_Model_Bookmark();
		Application_Model_BookmarksMapper::i()->find($id, $bookmark);

		// invalid pageid!
		if ( $bookmark->isNew() ) return;
		
		
		$page = X_PageParser_Page::getPage(
				$bookmark->getUrl(),
				new X_PageParser_Parser_HosterLinks($this->helpers()->hoster())
		);
		$loader = $page->getLoader();
		if ( $loader instanceof X_PageParser_Loader_Http || $loader instanceof X_PageParser_Loader_HttpAuthRequired ) {
			$http = $loader->getHttpClient()->setConfig(array(
					'maxredirects'	=> $this->config('request.maxredirects', 10),
					'timeout'		=> $this->config('request.timeout', 25)
			));
			
			if ( $bookmark->getUa() ) {
				X_Debug::i("Setting User-Agent...");
				$http->setHeaders(array(
						"User-Agent: {$bookmark->getUa()}",
				));
			}
			
			if ( $bookmark->getCookies() ) {
				X_Debug::i("Setting Cookies...");
				$http->setHeaders("Cookie", $bookmark->getCookies() );
			}
			 	
		}
		$links  = $page->getParsed();
		
		foreach ( $links as $i => $link ) {
			/* @var $bookmark Application_Model_Bookmark */
			$item = new X_Page_Item_PItem("{$this->getId()}-{$bookmark->getId()}-{$i}", "{$link['label']} [{$link['hoster']->getId()}]");
			$item->setIcon("/images/icons/hosters/{$link['hoster']->getId()}.png")
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setCustom(__CLASS__.':location', "{$bookmark->getId()}/{$link['url']}")
				->setLink(array(
					'l'	=>	X_Env::encode("{$bookmark->getId()}/{$link['url']}"),
					'action' => 'mode'
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
	 * @see X_VlcShares_Plugins_ResolverInterface::resolveLocation
	 * @param string $location
	 * @return string real address of a resource
	 */
	function resolveLocation($location = null) {
		
		// prevent no-location-given error
		if ( $location === null || $location === '' ) return false;
		
		// location format:
		// pageid/url
		$locParts = explode('/', $location, 2);
		@list($page, $url) = $locParts;
		
		if ( !$url ) return false;
		
		if ( isset($this->locationCache[$url]) ) return $this->locationCache[$url];
		
		$return = false;
		// we use the new hoster helper api
		try {
			$hoster = $this->helpers()->hoster()->findHoster($url);
			$return = $hoster->getPlayable($url, false);
		} catch ( Exception $e) {
			$return = false;
		}
		
		$this->locationCache[$url] = $return;
		return $return;
	}
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::getParentLocation
	 * @param $location
	 */
	function getParentLocation($location = null) {
		
		if ($location == null || $location == '') return false;
		
		// location format:
		// $catPage/X_Env::encode($category)/$itemPage/$item
		$locParts = $location != '' ? explode('/', $location, 2) : array();
		//@list($catPage, $category, $itemPage, $item) = $locParts;
		$locCount = count($locParts);
		
		array_pop($locParts);
		
		if ( count($locParts) >= 1 ) {
			return implode('/', $locParts);
		} else {
			return false;
		}			
	}
	
	/**
	 * Add the link add video link to actionLinks
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ActionLink
	 */
	public function getIndexActionLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ActionLink($this->getId(), X_Env::_('p_bookmarks_actionaddpage'));
		$link->setIcon('/images/plus.png')
			->setLink(array(
					'controller'	=>	'bookmarks',
					'action'		=>	'add',
				), 'default', true);
		return new X_Page_ItemList_ActionLink(array($link));
	}
	
	/**
	 * Add the link for -manage-onlinelibrary-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {
		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_bookmarks_mlink'));
		$link->setTitle(X_Env::_('p_bookmarks_managetitle'))
			->setIcon('/images/bookmarklets/addpage.png')
			->setLink(array(
					'controller'	=>	'bookmarks',
					'action'		=>	'index',
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
	}
	
	/**
	 * Backup all videos in db
	 * This is not a trigger of plugin API. It's called by Backupper plugin
	 */
	function getBackupItems() {
		
		$return = array();
		$pages = Application_Model_BookmarksMapper::i()->fetchAll();
		
		foreach ($pages as $model) {
			/* @var $model Application_Model_Bookmark */
			$return['pages']['page-'.$model->getId()] = $model->toArray();
		}
		
		return $return;
	}
	
	/**
	 * Restore backupped videos 
	 * This is not a trigger of plugin API. It's called by Backupper plugin
	 */
	function restoreItems($items) {

		$models = Application_Model_BookmarksMapper::i()->fetchAll();
		// cleaning up all shares
		foreach ($models as $model) {
			Application_Model_BookmarksMapper::i()->delete($model);
		}
	
		foreach (@$items['pages'] as $modelInfo) {
			$model = new Application_Model_Bookmark();
			$model->setOptions($modelInfo);
			$model->setId(false);
			// i don't set id, or db adapter will try to update old data that i cleaned
			Application_Model_BookmarksMapper::i()->save($model);
		}
		
		return X_Env::_('p_bookmarks_backupper_restoreditems', count($items['pages']));
		
		
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
