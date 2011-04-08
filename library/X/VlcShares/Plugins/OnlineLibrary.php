<?php

/**
 * OnlineLibrary plugin
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_OnlineLibrary extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface, X_VlcShares_Plugins_BackuppableInterface {
	
	const VERSION_CLEAN = '0.1';
	const VERSION = '0.1';
	
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
	 * Add the main link for online library
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");

		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_onlinelibrary_collectionindex'));
		$link->setIcon('/images/onlinelibrary/logo.png')
			->setDescription(X_Env::_('p_onlinelibrary_collectionindex_desc'))
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
		// $catPage/X_Env::encode($category)/$itemPage/$item
		$locParts = $location != '' ? explode('/', $location, 4) : array();
		@list($catPage, $category, $itemPage, $item) = $locParts;
		$locCount = count($locParts);
		// category is double encoded so we need an extra decode
		$category = X_Env::decode($category);
		
		switch ($locCount) {
			
			case 2:
				$itemPage = 1;
			case 4:
				// we shouldn't be here, $locCount = 4 means that we have a 
				// selected video in location. We should be in browse/mode
			case 3:
				$this->fetchVideos($items, $catPage, $category, $itemPage);
				break;
			
			default:
			case 0:
				$catPage = 1;
			case 1:
				$this->fetchCategories($items, $catPage);
				break;
			
		}
		
		return $items;
	}
	
	/**
	 * Fetch categories in $catPage
	 */
	protected  function fetchCategories(X_Page_ItemList_PItem $items, $catPage = 1) {
		
		// FIXME we shouldn't use paginator. We could use db for pagination
		// it's faster
		
		$categories = Application_Model_VideosMapper::i()->fetchCategories();
		$totalPageCount = $this->helpers()->paginator()->getPages($categories);
		
		if ( $this->helpers()->paginator()->hasPrevious($categories, $catPage) ) {
			$item = new X_Page_Item_PItem($this->getId().'-previouscatpage', X_Env::_("p_onlinelibrary_previouspage", ($catPage - 1), $totalPageCount));
			$item//->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', ($catPage - 1))
				->setLink(array(
					'l'	=>	X_Env::encode(($catPage - 1))
				), 'default', false);
			$items->append($item);
		}
		
		foreach ( $this->helpers()->paginator()->getPage($categories, $catPage) as $share ) {
			$item = new X_Page_Item_PItem($this->getId().'-'.$share['category'], X_Env::_("p_onlinelibrary_categorylabel", $share['category'], $share['links']));
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$catPage/".X_Env::encode($share['category']))
				->setLink(array(
					'l'	=>	X_Env::encode("$catPage/".X_Env::encode($share['category']))
				), 'default', false);
			$items->append($item);
		}
		
		if ( $this->helpers()->paginator()->hasNext($categories, $catPage) ) {
			$item = new X_Page_Item_PItem($this->getId().'-nextcatpage', X_Env::_("p_onlinelibrary_nextpage", ($catPage + 1), $totalPageCount));
			$item//->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', ($catPage + 1))
				->setLink(array(
					'l'	=>	X_Env::encode(($catPage + 1))
				), 'default', false);
			$items->append($item);
		}
		
	} 
	
	/**
	 * Fetch videos in $category and $itemPage
	 */
	protected function fetchVideos(X_Page_ItemList_PItem $items, $catPage, $category, $itemPage) {
		
		// FIXME we shouldn't use paginator. We could use db for pagination
		// it's faster

		$videos = Application_Model_VideosMapper::i()->fetchByCategory($category);
		
		$totalPageCount = $this->helpers()->paginator()->getPages($videos);
		
		if ( $this->helpers()->paginator()->hasPrevious($videos, $itemPage) ) {
			$item = new X_Page_Item_PItem($this->getId().'-previousvidpage', X_Env::_("p_onlinelibrary_previouspage", ($itemPage - 1), $totalPageCount));
			$item//->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$catPage/".X_Env::encode($category)."/".($itemPage - 1))
				->setLink(array(
					'l'	=>	X_Env::encode("$catPage/".X_Env::encode($category)."/".($itemPage - 1))
				), 'default', false);
			$items->append($item);
		}
		
		
		foreach ($this->helpers()->paginator()->getPage($videos, $itemPage) as $video) {
			/* @var $video Application_Model_Video */
			$item = new X_Page_Item_PItem($this->getId().'-'.$video->getId(), $video->getTitle() . " [".ucfirst($video->getHoster()."]"));
			$item->setIcon("/images/icons/hosters/{$video->getHoster()}.png")
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setCustom(__CLASS__.':location', "$catPage/".X_Env::encode($category)."/$itemPage/".$video->getId())
				->setLink(array(
					'action' => 'mode',
					'l'	=>	X_Env::encode("$catPage/".X_Env::encode($category)."/$itemPage/".$video->getId())
				), 'default', false);
				
			if ( trim($video->getDescription()) != '' ) {
				$item->setDescription(trim($video->getDescription()));
			} 

			if ( trim($video->getThumbnail()) != '' ) {
				$item->setThumbnail(trim($video->getThumbnail()));
			} 
			
			$items->append($item);
		}
	
		if ( $this->helpers()->paginator()->hasNext($videos, $itemPage) ) {
			$item = new X_Page_Item_PItem($this->getId().'-previousvidpage', X_Env::_("p_onlinelibrary_nextpage", ($itemPage + 1), $totalPageCount));
			$item//->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$catPage/".X_Env::encode($category)."/".($itemPage + 1))
				->setLink(array(
					'l'	=>	X_Env::encode("$catPage/".X_Env::encode($category)."/".($itemPage + 1))
				), 'default', false);
			$items->append($item);
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
			
			$link = new X_Page_Item_PItem('core-directwatch', X_Env::_('p_onlinelibrary_watchdirectly'));
			$link->setIcon('/images/icons/play.png')
				->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
				->setLink($url);
			return new X_Page_ItemList_PItem(array($link));
		} else {
			X_Debug::i('Setting priority to filterModeItems');
			$this->setPriority('filterModeItems', 99);
			
			$link = new X_Page_Item_PItem('invalidvideo-warning', X_Env::_('p_onlinelibrary_invalidlink'));
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
	 * @see X_VlcShares_Plugins_ResolverInterface::resolveLocation
	 * @param string $location
	 * @return string real address of a resource
	 */
	function resolveLocation($location = null) {
		
		// prevent no-location-given error
		if ( $location === null || $location === '' ) return false;
		
		// location format:
		// $catPage/X_Env::encode($category)/$itemPage/$item
		$locParts = explode('/', $location, 4);
		@list($catPage, $category, $itemPage, $item) = $locParts;
		$locCount = count($locParts);
		
		if ( $locCount != 4 ) return false;
		
		if ( (int) $item < 0 ) return false; // video_model id > 0
		
		$video = new Application_Model_Video();
		Application_Model_VideosMapper::i()->find((int) $item, $video);
		
		if ( $video->getId() == null ) return false;
		
		// we use the new hoster helper api
		if ( $video->getHoster() != "direct-url" ) {
			try {
				$hoster = $this->helpers()->hoster()->getHoster($video->getHoster());
				return $hoster->getPlayable($video->getIdVideo(), true);
			} catch ( Exception $e) {
				return false;
			}
		} else {
			// direct-url type: video url is in id
			return $video->getIdVideo();
		}
		
	}
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::getParentLocation
	 * @param $location
	 */
	function getParentLocation($location = null) {
		
		if ($location == null || $location == '') return false;
		
		// location format:
		// $catPage/X_Env::encode($category)/$itemPage/$item
		$locParts = $location != '' ? explode('/', $location, 4) : array();
		//@list($catPage, $category, $itemPage, $item) = $locParts;
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
	 * Add the link add video link to actionLinks
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ActionLink
	 */
	public function getIndexActionLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ActionLink($this->getId(), X_Env::_('p_onlinelibrary_actionaddvideo'));
		$link->setIcon('/images/plus.png')
			->setLink(array(
					'controller'	=>	'onlinelibrary',
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

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_onlinelibrary_mlink'));
		$link->setTitle(X_Env::_('p_onlinelibrary_managetitle'))
			->setIcon('/images/onlinelibrary/logo.png')
			->setLink(array(
					'controller'	=>	'onlinelibrary',
					'action'		=>	'index',
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}
	
	/**
	 * Retrieve statistic from plugins
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Statistic
	 */
	public function getIndexStatistics(Zend_Controller_Action $controller) {
		
		$categories = Application_Model_VideosMapper::i()->getCountCategories(); // FIXME create count functions
		$videos = Application_Model_VideosMapper::i()->getCount(); // FIXME create count functions
		$hosters = count($this->helpers()->hoster()->getHosters());
		
		$stat = new X_Page_Item_Statistic($this->getId(), X_Env::_('p_onlinelibrary_statstitle'));
		$stat->setTitle(X_Env::_('p_onlinelibrary_statstitle'))
			->appendStat(X_Env::_('p_onlinelibrary_statcategories', $categories))
			->appendStat(X_Env::_('p_onlinelibrary_statvideos', $videos))
			->appendStat(X_Env::_('p_onlinelibrary_stathosters', $hosters));

		return new X_Page_ItemList_Statistic(array($stat));
		
	}
	
	
	/**
	 * Backup all videos in db
	 * This is not a trigger of plugin API. It's called by Backupper plugin
	 */
	function getBackupItems() {
		
		$return = array();
		$videos = Application_Model_VideosMapper::i()->fetchAll();
		
		foreach ($videos as $model) {
			/* @var $model Application_Model_Video */
			$return['videos']['video-'.$model->getId()] = array(
				'id'			=> $model->getId(), 
	            'idVideo'   	=> $model->getIdVideo(),
	            'description'	=> $model->getDescription(),
	            'category'		=> $model->getCategory(),
	        	'title'			=> $model->getTitle(),
				'thumbnail'		=> $model->getThumbnail(),
				'hoster'		=> $model->getHoster()
			);
		}
		
		return $return;
	}
	
	/**
	 * Restore backupped videos 
	 * This is not a trigger of plugin API. It's called by Backupper plugin
	 */
	function restoreItems($items) {

		//return parent::restoreItems($items);
		
		$models = Application_Model_VideosMapper::i()->fetchAll();
		// cleaning up all shares
		foreach ($models as $model) {
			Application_Model_VideosMapper::i()->delete($model);
		}
	
		foreach (@$items['videos'] as $modelInfo) {
			$model = new Application_Model_Video();
			$model->setIdVideo(@$modelInfo['idVideo']) 
				->setDescription(@$modelInfo['description'])
				->setCategory(@$modelInfo['category'])
				->setTitle(@$modelInfo['title'])
				->setHoster(@$modelInfo['hoster'])
				->setThumbnail(@$modelInfo['thumbnail'])
				;
			// i don't set id, or db adapter will try to update old data that i cleaned
			Application_Model_MegavideoMapper::i()->save($model);
		}
		
		return X_Env::_('p_onlinelibrary_backupper_restoreditems', count($items['videos']));
		
		
	}
	
}
