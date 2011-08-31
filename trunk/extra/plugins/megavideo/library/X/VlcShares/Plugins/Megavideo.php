<?php

/**
 * Megavideo plugin
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Megavideo extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface, X_VlcShares_Plugins_BackuppableInterface {
	
	const VERSION_CLEAN = '0.3.1';
	const VERSION = '0.3.1';
	
	public function __construct() {
		$this
			->setPriority('gen_beforeInit')
			//->setPriority('getCollectionsItems')
			->setPriority('preRegisterVlcArgs')
			->setPriority('getShareItems')
			->setPriority('preGetModeItems')
			//->setPriority('getIndexActionLinks')
			//->setPriority('getIndexStatistics')
			->setPriority('getIndexManageLinks')
			->setPriority('prepareConfigElement');
		
	}
	
	/**
	 * Registers a megavideo helper inside the helper broker
	 */
	public function gen_beforeInit(Zend_Controller_Action $controller) {
		
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
	}
	
	/**
	 * Add the main link for megavideo library
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");

		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_megavideo_collectionindex'));
		$link->setIcon('/images/megavideo/logo.png')
			->setDescription(X_Env::_('p_megavideo_collectionindex_desc'))
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
			
			//list($shareType, $linkId) = explode(':', $location, 2);
			// $location is the categoryName

			$videos = Application_Model_MegavideoMapper::i()->fetchByCategory($location);
			
			foreach ($videos as $video) {
				/* @var $video Application_Model_Megavideo */
				$item = new X_Page_Item_PItem($this->getId().'-'.$video->getId(), $video->getLabel());
				$item->setIcon('/images/icons/file_32.png')
					->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setCustom(__CLASS__.':location', $video->getId())
					->setLink(array(
						'action' => 'mode',
						'l'	=>	X_Env::encode($video->getId())
					), 'default', false);
				$items->append($item);
			}
			
		} else {
			// if location is not specified,
			// show collections
			$categories = Application_Model_MegavideoMapper::i()->fetchCategories();
			foreach ( $categories as $share ) {
				/* @var $share Application_Model_FilesystemShare */
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
			
			$link = new X_Page_Item_PItem('core-directwatch', X_Env::_('p_megavideo_watchdirectly'));
			$link->setIcon('/images/icons/play.png')
				->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
				->setLink($url);
			return new X_Page_ItemList_PItem(array($link));
		}
		
	}
	

	/**
	 * Add the link for megavideo quality change
	 * 
	 * @param string $provider
	 * @param string $location
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function getModeItems($provider, $location, Zend_Controller_Action $controller) {

		try {
		
			/* @var $megavideoHelper X_VlcShares_Plugins_Helper_Megavideo */
			$megavideoHelper = $this->helpers('megavideo');
			// check megavideo helper to be sure that location has been setted for check
			
			// if location is not setted, helper throwns an exception.
			// i don't care for the returned value
			$megavideoHelper->getServer();
			
			X_Debug::i('Plugin triggered. Location could be provided by Megavideo');
			
			$urlHelper = $controller->getHelper('url');
	
			$subLabel = X_Env::_('p_megavideo_qualityselection_normal');
	
			$subParam = $controller->getRequest()->getParam($this->getId().':quality', false);
			
			if ( $subParam !== false ) {
				//$subParam = X_Env::decode($subParam);
				//list($type, $source) = explode(':', $subParam, 2);
				$subLabel = X_Env::_("p_megavideo_qualitycode_$subParam");
				if ( $subLabel == "p_megavideo_qualitycode_$subParam" ) {
					$subLabel = $subParam;
				}
			}
			
			$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_megavideo_qualityselected').": $subLabel");
			$link->setIcon('/images/megavideo/logo.png')
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(array(
						'action'	=>	'selection',
						'pid'		=>	$this->getId()
					), 'default', false);
	
			return new X_Page_ItemList_PItem(array($link));
				
		} catch (Exception $e) {
			X_Debug::i("Location is not provided by Megavideo Helper");
		}
	}
	
	/**
	 * Set the header of selection page if needed
	 * @param string $provider
	 * @param string $location
	 * @param string $pid
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function preGetSelectionItems($provider, $location, $pid, Zend_Controller_Action $controller) {
		// we want to expose items only if pid is this plugin
		if ( $this->getId() != $pid ) return;
		
		X_Debug::i('Plugin triggered');	
		
		$urlHelper = $controller->getHelper('url');
		$link = new X_Page_Item_PItem($this->getId().'-header', X_Env::_('p_megavideo_qualityselection_title'));
		$link->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setLink(X_Env::completeUrl($urlHelper->url()));
		return new X_Page_ItemList_PItem(array($link));
		
	}
	
	/**
	 * Show a list of valid subs for the selected location
	 * @param string $provider
	 * @param string $location
	 * @param string $pid
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function getSelectionItems($provider, $location, $pid, Zend_Controller_Action $controller) {
		// we want to expose items only if pid is this plugin
		if ( $this->getId() != $pid ) return;
		
		X_Debug::i('Plugin triggered');
		
		$urlHelper = $controller->getHelper('url');
		
		// i try to mark current selected sub based on $this->getId() param
		// in $currentSub i get the name of the current profile
		$currentSub = $controller->getRequest()->getParam($this->getId().':quality', false);

		$return = new X_Page_ItemList_PItem();
		$item = new X_Page_Item_PItem($this->getId().'-normal', X_Env::_('p_megavideo_qualityselection_normal'));
		$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setLink(array(
					'action'				=> 'mode',
					$this->getId().':quality'	=> null, // unset this plugin selection
					'pid'					=> null
				), 'default', false)
			->setHighlight($currentSub === false || $currentSub == X_VlcShares_Plugins_Helper_Megavideo::QUALITY_NORMAL);
		$return->append($item);

		$item = new X_Page_Item_PItem($this->getId().'-full', X_Env::_('p_megavideo_qualityselection_full'));
		$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setLink(array(
					'action'				=> 'mode',
					$this->getId().':quality'	=> X_VlcShares_Plugins_Helper_Megavideo::QUALITY_FULL, // unset this plugin selection
					'pid'					=> null
				), 'default', false)
			->setHighlight($currentSub == X_VlcShares_Plugins_Helper_Megavideo::QUALITY_FULL);
		$return->append($item);

		$item = new X_Page_Item_PItem($this->getId().'-full', X_Env::_('p_megavideo_qualityselection_nopremium'));
		$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setLink(array(
					'action'				=> 'mode',
					$this->getId().':quality'	=> X_VlcShares_Plugins_Helper_Megavideo::QUALITY_NOPREMIUM, // unset this plugin selection
					'pid'					=> null
				), 'default', false)
			->setHighlight($currentSub == X_VlcShares_Plugins_Helper_Megavideo::QUALITY_NOPREMIUM);
		$return->append($item);
		
		
		return $return;
	}	
	
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::resolveLocation
	 * @param string $location
	 * @return string real address of a resource
	 */
	function resolveLocation($location = null) {

		// prevent no-location-given error
		if ( $location === null ) return false;
		if ( (int) $location < 0 ) return false; // megavideo_model id > 0
		
		$video = new Application_Model_Megavideo();
		Application_Model_MegavideoMapper::i()->find((int) $location, $video);
		
		// TODO prevent ../
		if ( $video->getId() == null ) return false;
		/*
		$megavideo = new Megavideo($video->getIdVideo());
		
		return $megavideo->get('URL');
		*/
		/* @var $helper X_VlcShares_Plugins_Helper_Megavideo */
		$helper = $this->helpers('megavideo');
		return $helper->setLocation($video->getIdVideo())->getUrl();
		
	}
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::getParentLocation
	 * @param $location
	 */
	function getParentLocation($location = null) {
		if ($location == null || $location == '') return false;
		
		if ( is_numeric($location) && ((int) $location > 0 ) ) {
			// should be a video id.
			$model = new Application_Model_Megavideo();
			Application_Model_MegavideoMapper::i()->find((int) $location, $model);
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
	 * Add the link Add megavideo link to actionLinks
	 * @param Zend_Controller_Action $this
	 * @return array The format of the array should be:
	 * 		array(
	 * 			array(
	 * 				'label' => ITEM LABEL,
	 * 				'link'	=> HREF,
	 * 				'highlight'	=> true|false,
	 * 				'icon'	=> ICON_HREF
	 * 			), ...
	 * 		)
	 */
	public function getIndexActionLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ActionLink($this->getId(), X_Env::_('p_megavideo_actionaddvideo'));
		$link->setIcon('/images/plus.png')
			->setLink(array(
					'controller'	=>	'megavideo',
					'action'		=>	'add',
				), 'default', true);
		return new X_Page_ItemList_ActionLink(array($link));
	}
	
	/**
	 * Add the link for -manage-megavideo-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_megavideo_mlink'));
		$link->setTitle(X_Env::_('p_megavideo_managetitle'))
			->setIcon('/images/megavideo/logo.png')
			->setLink(array(
					//'controller'	=>	'megavideo',
					//'action'		=>	'index',
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'megavideo'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}
	
	/**
	 * Retrieve statistic from plugins
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Statistic
	 */
	public function getIndexStatistics(Zend_Controller_Action $controller) {
		
		$categories = count(Application_Model_MegavideoMapper::i()->fetchCategories()); // FIXME create count functions
		$videos = count(Application_Model_MegavideoMapper::i()->fetchAll()); // FIXME create count functions
		
		$stat = new X_Page_Item_Statistic($this->getId(), X_Env::_('p_megavideo_statstitle'));
		$stat->setTitle(X_Env::_('p_megavideo_statstitle'))
			->appendStat(X_Env::_('p_megavideo_statcategories').": $categories")
			->appendStat(X_Env::_('p_megavideo_statvideos').": $videos");

		return new X_Page_ItemList_Statistic(array($stat));
		
	}
	
	
	/**
	 * Backup all videos in db
	 * This is not a trigger of plugin API. It's called by Backupper plugin
	 */
	function getBackupItems() {
		
		$return = array();
		$videos = Application_Model_MegavideoMapper::i()->fetchAll();
		
		foreach ($videos as $model) {
			/* @var $model Application_Model_Megavideo */
			$return['videos']['video-'.$model->getId()] = array(
				'id'			=> $model->getId(), 
	            'idVideo'   	=> $model->getIdVideo(),
	            'description'	=> $model->getDescription(),
	            'category'		=> $model->getCategory(),
	        	'label'			=> $model->getLabel(),
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
		
		$models = Application_Model_MegavideoMapper::i()->fetchAll();
		// cleaning up all shares
		foreach ($models as $model) {
			Application_Model_MegavideoMapper::i()->delete($model->getId());
		}
	
		foreach (@$items['videos'] as $modelInfo) {
			$model = new Application_Model_Megavideo();
			$model->setIdVideo(@$modelInfo['idVideo']) 
				->setDescription(@$modelInfo['description'])
				->setCategory(@$modelInfo['category'])
				->setLabel(@$modelInfo['label'])
				;
			// i don't set id, or db adapter will try to update old data that i cleaned
			Application_Model_MegavideoMapper::i()->directSave($model);
		}
		
		return X_Env::_('p_megavideo_backupper_restoreditems'). ": " .count($items['videos']);
		
		
	}
	
	
	/**
	 * Remove cookie.jar if configs change and convert form password to password element
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
			// i have to convert it to a password element
			case 'plugins_megavideo_premium_password':
				$password = $form->createElement('password', 'plugins_megavideo_premium_password', array(
					'label' => $element->getLabel(),
					'description' => $element->getDescription(),
					'renderPassword' => true,
				));
				$form->plugins_megavideo_premium_password = $password;
				break;
		}
		
		// remove cookie.jar if somethings has value
		if ( !$form->isErrors() && !is_null($element->getValue()) && file_exists(APPLICATION_PATH . '/../data/megavideo/cookie.jar') ) {
			if ( @!unlink(APPLICATION_PATH . '/../data/megavideo/cookie.jar') ) {
				X_Debug::e("Error removing cookie.jar");
			}
		}
	}
}
