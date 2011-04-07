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
			//->setPriority('gen_beforeInit')
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
	 * Registers a megavideo helper inside the helper broker
	 */
	public function gen_beforeInit(Zend_Controller_Action $controller) {
		
		/*
		$this->helpers()->language()->addTranslation(__CLASS__);
		
		$helper_conf = new Zend_Config(array(
			'premium' => $this->config('premium.enabled', false),
			'username' => $this->config('premium.username', ''),
			'password' => $this->config('premium.password', '')
		)); 
		
		$helper = new X_VlcShares_Plugins_Helper_Megaupload($helper_conf);
		
		$this->helpers()->registerHelper('megavideo', $helper);
		$this->helpers()->registerHelper('megaupload', $helper);
		
		if ( $this->config('premium.enabled', true) && $this->config('premium.username', false) && $this->config('premium.password', false) ) {
			// allow to choose quality for videos if premium user
			$this
				->setPriority('getModeItems')
				->setPriority('preGetSelectionItems')
				->setPriority('getSelectionItems');
		}
		
		$this->helpers()->hoster()->registerHoster(new X_VlcShares_Plugins_Helper_Hoster_Megavideo());
		$this->helpers()->hoster()->registerHoster(new X_VlcShares_Plugins_Helper_Hoster_Megaupload());
		*/
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
	 * @param unknown_type $provider
	 * @param unknown_type $location
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
		
		$items = new X_Page_ItemList_PItem();
		
		if ( $location != '' ) {
			
			X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_SortItems');
			
			//list($shareType, $linkId) = explode(':', $location, 2);
			// $location is the categoryName

			$videos = Application_Model_VideosMapper::i()->fetchByCategory($location);
			
			foreach ($videos as $video) {
				/* @var $video Application_Model_Video */
				$item = new X_Page_Item_PItem($this->getId().'-'.$video->getId(), $video->getTitle() . " [".ucfirst($video->getHoster()."]"));
				$item->setIcon("/images/icons/hosters/{$video->getHoster()}.png")
					->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setCustom(__CLASS__.':location', $video->getId())
					->setLink(array(
						'action' => 'mode',
						'l'	=>	X_Env::encode($video->getId())
					), 'default', false);
					
				if ( trim($video->getDescription()) != '' ) {
					$item->setDescription(trim($video->getDescription()));
				} 

				if ( trim($video->getThumbnail()) != '' ) {
					$item->setThumbnail(trim($video->getThumbnail()));
				} 
				
				$items->append($item);
			}
			
		} else {
			// if location is not specified,
			// show collections
			$categories = Application_Model_VideosMapper::i()->fetchCategories();
			foreach ( $categories as $share ) {
				$item = new X_Page_Item_PItem($this->getId().'-'.$share['category'], "{$share['category']} ({$share['links']})");
				$item->setIcon('/images/icons/folder_32.png')
					->setType(X_Page_Item_PItem::TYPE_CONTAINER)
					->setCustom(__CLASS__.':location', $share['category'])
					->setLink(array(
						'l'	=>	X_Env::encode($share['category'])
					), 'default', false);
				$items->append($item);
			}
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
		if ( $location === null ) return false;
		if ( (int) $location < 0 ) return false; // video_model id > 0
		
		$video = new Application_Model_Video();
		Application_Model_VideosMapper::i()->find((int) $location, $video);
		
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
		
		if ( is_numeric($location) && ((int) $location > 0 ) ) {
			// should be a video id.
			$model = new Application_Model_Video();
			Application_Model_VideosMapper::i()->find((int) $location, $model);
			if ( $model->getId() !== null ) {
				return $model->getCategory();
			} else {
				return null;
			}
		} else {
			// should be a category name
			return null;
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
