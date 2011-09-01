<?php

require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'Zend/Dom/Query.php';
require_once 'Zend/Gdata/YouTube.php';
require_once 'Zend/Gdata/YouTube/VideoEntry.php';


/**
 * Add youtube site as a video source
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Youtube extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface, X_VlcShares_Plugins_BackuppableInterface {
	
	const VERSION = '0.2.1';
	const VERSION_CLEAN = '0.2.1';
	
	const MODE_LIBRARY = "l";
	const MODE_ACCOUNTS = "a";
	
	const A_UPLOADED = 'aup';
	const A_SUBSCRIPTIONS = 'ass';
	const A_FAVORITES = 'afa';
	const A_ACTIVITIES = 'aac';
	const A_PLAYLISTS = 'apl';
	
	public function __construct() {
		$this->setPriority('gen_beforeInit')
			->setPriority('getCollectionsItems')
			->setPriority('preRegisterVlcArgs')
			->setPriority('getShareItems')
			->setPriority('preGetModeItems')
			->setPriority('getModeItems')
			->setPriority('preGetSelectionItems')
			->setPriority('getSelectionItems')
			->setPriority('registerVlcArgs')
			->setPriority('getIndexManageLinks')
			->setPriority('getIndexMessages')
			->setPriority('getTestItems');
	}
	
	//========================
	// API TRIGGERS
	//========================
	
	/**
	 * Inizialize translator for this plugin
	 * @param Zend_Controller_Action $controller
	 */
	function gen_beforeInit(Zend_Controller_Action $controller) {
		$this->helpers()->language()->addTranslation(__CLASS__);
		$this->helpers()->registerHelper('youtube', new X_VlcShares_Plugins_Helper_Youtube());
		$this->helpers()->hoster()->registerHoster(new X_VlcShares_Plugins_Helper_Hoster_Youtube());
	}
	
	/**
	 * Add the main link for youtube library
	 * @param Zend_Controller_Action $controller
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");
		
		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_youtube_collectionindex'));
		$link->setIcon('/images/youtube/logo.png')
			->setDescription(X_Env::_('p_youtube_collectionindex_desc'))
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
	 * Get item list
	 * @param unknown_type $provider
	 * @param unknown_type $location
	 * @param Zend_Controller_Action $controller
	 */
	public function getShareItems($provider, $location, Zend_Controller_Action $controller) {
		// this plugin add items only if it is the provider
		if ( $provider != $this->getId() ) return;
		
		X_Debug::i("Plugin triggered");
		
		$urlHelper = $controller->getHelper('url');
		
		$items = new X_Page_ItemList_PItem();
		
		//try to disable SortItems plugin, so link are listed as in html page
		X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_SortItems');
		
		X_Debug::i("Requested location: $location");
		
		$exploded = $location != '' ? explode('/', $location) : array(null, null, null, null);
		@list($mode, $submode, $page, $id) = $exploded;
		
		if ( $page !== null && $page > 0 ) {
			// need to insert a back one page button as first here
			$items->append($this->back($mode, $submode, $page, $id));
		}
		
		switch ($mode) {
			
			// accounts
			case self::A_UPLOADED:
				$this->uploadedMenu($items, $submode, $page);
				break;
			case self::A_SUBSCRIPTIONS:
				$this->subscriptionsMenu($items, $submode, $page);
				break;
			case self::A_PLAYLISTS:
				$this->playlistsMenu($items, $submode, $page);
				break;
			case self::A_FAVORITES:
				$this->favoritesMenu($items, $submode, $page);
				break;
			case self::A_ACTIVITIES:
				$this->activitiesMenu($items, $submode, $page);
				break;
			case self::MODE_ACCOUNTS:
				$this->accountsMenu($items, $submode);
				break;
			
			// library
			case self::MODE_LIBRARY:
				$this->libraryMenu($items, $submode);
				break;
				
			default: 
				$this->mainMenu($items);
				break;
		}
		
		
		return $items;
	}
	
	/**
	 *	Add button -watch youtube stream directly-
	 * 
	 * @param string $provider
	 * @param string $location
	 * @param Zend_Controller_Action $controller
	 */
	public function preGetModeItems($provider, $location, Zend_Controller_Action $controller) {

		if ( $provider != $this->getId()) return;
		
		X_Debug::i("Plugin triggered");
		
		// i have to remove default sub provider
		// and install the newer one in getModeItems
		
		X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_FileSubs');
		
		$url = $this->resolveLocation($location);
		
		if ( $url ) {
			// i have to check for rtsp videos, wiimc can't handle them
			if ( X_Env::startWith($url, 'rtsp') && $this->helpers()->devices()->isWiimc() ) {
				return;
			}

			$link = new X_Page_Item_PItem('core-directwatch', X_Env::_('p_youtube_watchdirectly'));
			$link->setIcon('/images/icons/play.png')
				->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
				->setLink($url);


			// add tag wiimcplus_subtitle for subtitle in direct watching
			if ( $this->config('closedcaption.enabled', true ) ) {
				
				/* @var $request Zend_Controller_Request_Http */
				$request = $controller->getRequest();
				$sub = false;
				$lc = $request->getParam($this->getId().':sub', false);
				if ( $lc !== false ) {
					@list(, , , $videoId) = explode('/', $location);
					$sub = array(
						'controller'	=> 'youtube',
						'action'		=> 'convert',
						'v'				=> $videoId,
						'l'				=> $lc,
						'f'				=> 'file.srt',
					);
				}
				/* @var $urlHelper Zend_Controller_Action_Helper_Url */
				$urlHelper = $controller->getHelper('url');
				if ( $sub !== false ) {
					$link->setCustom('subtitle', X_Env::completeUrl($urlHelper->url($sub, 'default', true)));
					X_Debug::i("CC-XML to SRT Url: ".X_Env::completeUrl($urlHelper->url($sub, 'default', true)));
				}
			}
				
			return new X_Page_ItemList_PItem(array($link));
		} else {
			// if there is no link, i have to remove start-vlc button
			// and replace it with a Warning button
			
			X_Debug::i('Setting priority to filterModeItems');
			$this->setPriority('filterModeItems', 99);
			
			$link = new X_Page_Item_PItem('youtube-warning', X_Env::_('p_youtube_invalidyoutube'));
			$link->setIcon('/images/msg_error.png')
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(array (
					'controller' => 'browse',
					'action' => 'share',
					'p'	=> $this->getId(),
					'l' => X_Env::encode($this->getParentLocation($location)),
				), 'default', true);
			return new X_Page_ItemList_PItem(array($link));

		}
		
	}
	
	/**
	 * Add the link for closed caption language change
	 * 
	 * @param string $provider
	 * @param string $location
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function getModeItems($provider, $location, Zend_Controller_Action $controller) {

		if ( $provider != $this->getId() ) return;
		
		if ( $this->config('closedcaption.enabled', true ) ) {
		
			X_Debug::i('Plugin triggered');
			
			$urlHelper = $controller->getHelper('url');
	
			$subLabel = X_Env::_('p_youtube_subselection_none');
	
			$subParam = $controller->getRequest()->getParam($this->getId().':sub', false);
			
			if ( $subParam !== false ) {
				$subParam = X_Env::decode($subParam);
				//list($type, $source) = explode(':', $subParam, 2);
				$subLabel = X_Env::_("p_youtube_langcode_$subParam");
				if ( $subLabel == "p_youtube_langcode_$subParam" ) {
					$subLabel = $subParam;
				}
			}
			
			$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_youtube_subselected').": $subLabel");
			$link->setIcon('/images/youtube/closedcaption.png')
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(array(
						'action'	=>	'selection',
						'pid'		=>	$this->getId()
					), 'default', false);
	
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
		X_Debug::i('plugin triggered');
		if ( $item->getKey() == 'core-play') {
			X_Debug::w('core-play flagged as invalid because youtube link is invalid');
			return false;
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
		// we want to expose items only if pid is this plugin and provider is this plugin
		if ( $this->getId() != $pid || $this->getId() != $provider ) return;
		
		X_Debug::i('Plugin triggered');		
		
		$urlHelper = $controller->getHelper('url');
		$link = new X_Page_Item_PItem($this->getId().'-header', X_Env::_('p_youtube_subselection_title'));
		$link->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setLink(X_Env::completeUrl($urlHelper->url()));
		return new X_Page_ItemList_PItem();
		
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
		if ( $this->getId() != $pid || $this->getId() != $provider ) return;
		
		X_Debug::i('Plugin triggered');
		
		$urlHelper = $controller->getHelper('url');
		
		// i try to mark current selected sub based on $this->getId() param
		// in $currentSub i get the name of the current profile
		$currentSub = $controller->getRequest()->getParam($this->getId().':sub', false);
		if ( $currentSub !== false ) $currentSub = X_Env::decode($currentSub);

		$return = new X_Page_ItemList_PItem();
		$item = new X_Page_Item_PItem($this->getId().'-none', X_Env::_('p_youtube_subselection_none'));
		$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setLink(array(
					'action'				=> 'mode',
					$this->getId().':sub'	=> null, // unset this plugin selection
					'pid'					=> null
				), 'default', false)
			->setHighlight($currentSub === false);
		$return->append($item);
		
		// check if infile support is enabled
		// by default infile.enabled is true
		if ( $this->config('closedcaption.enabled', true ) ) {
			// check for closed captions
			/* @var $helper X_VlcShares_Plugins_Helper_Youtube */
			$helper = $this->helpers('youtube');
			
			@list(, , , $videoId) = explode('/', $location); 
			
			$subs = $helper->getSubtitlesNOAPI($videoId);
			
			//X_Debug::i(var_export($infileSubs, true));
			foreach ($subs as $lc => $sub) {
				X_Debug::i("Valid ccaption-sub: [{$lc}] {$sub['name']} ({$sub['lang_translated']})");
				
				$subLabel = X_Env::_("p_youtube_langcode_$lc");
				if ( $subLabel == "p_youtube_langcode_$lc" ) {
					$subLabel = $lc;
				}
				if ( $sub['name'] != '' ) $subLabel .= ' ['.$sub['name'].']';
				
				$item = new X_Page_Item_PItem($this->getId().'-'.$sub['id'], $subLabel);
				$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setCustom(__CLASS__.':sub', $lc)
					->setLink(array(
							'action'	=>	'mode',
							'pid'		=>	null,
							$this->getId().':sub' => X_Env::encode($lc) // set this plugin selection as stream:$streamId
						), 'default', false)
					->setHighlight($currentSub == $lc);
				$return->append($item);
			}
		}
		return $return;
	}	
	
	private $cachedLocation = array();
	
	public function resolveLocation($location = null) {
		if ( $location == null || $location == false || $location == '') return false;
		
		if ( array_key_exists($location, $this->cachedLocation) && array_key_exists('url', $this->cachedLocation[$location]) ) {
			return $this->cachedLocation[$location]['url'];
		} else {
			$exploded = $location != '' ? explode('/', $location) : array(null, null, null, null);
			@list($mode, $submode, $page, $id) = $exploded;
			
			// prepare cache info
			$toBeCached = array();
			if (  array_key_exists($location, $this->cachedLocation) ) {
				$toBeCached = $this->cachedLocation[$location];
			}
			
			/* @var $helper X_VlcShares_Plugins_Helper_Youtube */
			$helper = $this->helpers('youtube');
			
			/* @var $foundVideo Zend_Gdata_YouTube_VideoEntry */
			$foundVideo = false;
			
			try {
				switch ( $mode ) {
					case self::MODE_ACCOUNTS:
					case self::A_ACTIVITIES:
					case self::A_FAVORITES:
					case self::A_PLAYLISTS:
					case self::A_SUBSCRIPTIONS:
					case self::A_UPLOADED:
						if ( $id !== null ) {
							// the youtube video url is inside the id
							//$foundVideo = $helper->getVideo($id);
							$foundVideo = $id;
						}
						break;
					case self::MODE_LIBRARY:
						if ( $id !== null ) {
							$video = new Application_Model_YoutubeVideo();
							Application_Model_YoutubeVideosMapper::i()->find($id, $video);
							if ( $video->getId() != null ) {
								//$foundVideo = $helper->getVideo($video->getIdYoutube());
								$foundVideo = $video->getIdYoutube();
							}
						}
						break;
				}
			} catch ( Exception $e) {
				// WTF: video query is invalid!!!
				X_Debug::e('Error in video query: '.$e->getMessage());
				return false;
			}
			
			
			//X_Debug::i("Video debug info: ". print_r($helper->getVideo($foundVideo), true));
			
			if ( $foundVideo !== false && $foundVideo !== null) {
			
				//$content = $foundVideo->getMediaGroup()->getContent();
				//print_r($foundVideo);
				//$content = $content[0];
				/* @var $content Zend_Gdata_YouTube_Extension_MediaContent */
				
				$formats = $helper->getFormatsNOAPI($foundVideo/*->getVideoId()*/);
				
				$returned = null;
				
				$qualityPriority = explode('|', $this->config('quality.priority', '5|34|18|35'));
				
				foreach ($qualityPriority as $quality) {
					if ( array_key_exists($quality, $formats)) {
						$returned = $formats[$quality];
						X_Debug::i('Video format selected: '.$quality);
						break;
					}
				}
				if ( $returned === null ) {
					// for valid video id but video with restrictions
					// alternatives formats can't be fetched by youtube page.
					// i have to fallback to standard api url
					$apiVideo = $helper->getVideo($foundVideo);
					
					foreach ($apiVideo->mediaGroup->content as $content) {
						if ($content->type === "video/3gpp") {
							$returned = $content->url;
							X_Debug::w('Content restricted video, fallback to api url:'.$returned);
							break;
						}
					}

					if ( $returned === null ) {
						$returned = false;
					}
				}
				$toBeCached['url'] = $returned;
				$this->cachedLocation[$location] = $toBeCached;
				return $returned;
				
			}
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

		// i need to register source as first, because subtitles plugin use source
		// for create subfile
		
		X_Debug::i('Plugin triggered');
		
		$dLocation = $this->resolveLocation($location);
		
		if ( $location !== null ) {
			// TODO adapt to newer api when ready
			$vlc->registerArg('source', "\"$dLocation\"");
		} else {
			X_Debug::e("No source o_O");
			return;
		}

		
		// Now, it's time to check for sub parameter
		
		$request = $controller->getRequest();
		$sub = $request->getParam($this->getId().':sub', false);
		
		if ( $sub !== false  ) {
			
			$sub = X_Env::decode($sub);
			/* @var $helper X_VlcShares_Plugins_Helper_Youtube */
			$helper = $this->helpers('youtube');
			
			list(, , , $videoId) = explode('/', $location);
			
			$sub = $helper->getSubtitleNOAPI($videoId, $sub);
			$urlHelper = $controller->getHelper('url');
			$subFile = X_Env::completeUrl($urlHelper->url($sub['srt_url'],'default', true));
			
			$vlc->registerArg('subtitles', "--sub-file=\"{$subFile}\"");
			
		}
		
	}
	
	/**
	 * Add the link for -manage-megavideo-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_youtube_mlink'));
		$link->setTitle(X_Env::_('p_youtube_managetitle'))
			->setIcon('/images/youtube/logo.png')
			->setLink(array(
					'controller'	=>	'youtube',
					'action'		=>	'index',
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}
	
	
	public function getParentLocation($location = null) {
		//return false;
		@list($mode, $submode, $page, $videoId) = explode('/', $location);
		
		if ( $videoId != null ) {
			return implode('/', array($mode, $submode, $page));
		}
		
		if ( $page != null && $page > 0 ) {
			// need to be insered the back one page button
			//return implode('/', array($mode, $submode, $page - 1 ));
			// mayve we should revert to $mode, $submode only
		}
		
		switch ($mode) {
			case self::MODE_LIBRARY:
			case self::MODE_ACCOUNTS:
				return $submode ? $mode : null;
			
			case self::A_ACTIVITIES:
			case self::A_FAVORITES:
			case self::A_PLAYLISTS:
			case self::A_SUBSCRIPTIONS:
			case self::A_UPLOADED:
				if ( $submode ) {
					$exploded = explode(',', $submode);
					//X_Debug::i(print_r($exploded, true));
					if ( count($exploded) >= 2 ) {
						array_pop($exploded);
						return implode('/', array($mode,implode(',', $exploded)));
					} else {
						return implode('/', array(self::MODE_ACCOUNTS,implode(',', $exploded)));
					}
				} else {
					return null;
				}
			default:
				return false;
		}
		
	}

	function getBackupItems() {
		
		$return = array('videos' => array(), 'accounts' => array(), 'categories' => array());
		
		$videos = Application_Model_YoutubeVideosMapper::i()->fetchAll();
		foreach ($videos as $model) {
			/* @var $model Application_Model_YoutubeVideo */
			$return['videos']['video-'.$model->getId()] = array(
				'id'			=> $model->getId(), 
	            'idYoutube'   	=> $model->getIdYoutube(),
	            'description'	=> $model->getDescription(),
	            'idCategory'	=> $model->getIdCategory(),
	        	'label'			=> $model->getLabel(),
				'thumbnail'		=> $model->getThumbnail()
			);
		}

		$categories = Application_Model_YoutubeCategoriesMapper::i()->fetchAll();
		foreach ($categories as $model) {
			/* @var $model Application_Model_YoutubeCategory */
			$return['categories']['category-'.$model->getId()] = array(
				'id'			=> $model->getId(), 
	        	'label'			=> $model->getLabel(),
				'thumbnail'		=> $model->getThumbnail()
			);
		}
		
		$accounts = Application_Model_YoutubeAccountsMapper::i()->fetchAll();
		foreach ($accounts as $model) {
			/* @var $model Application_Model_YoutubeAccount */
			$return['accounts']['account-'.$model->getId()] = array(
				'id'			=> $model->getId(), 
	        	'label'			=> $model->getLabel(),
				'thumbnail'		=> $model->getThumbnail()
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
		
		// cleaning up all videos
		$models = Application_Model_YoutubeVideosMapper::i()->fetchAll();
		foreach ($models as $model) {
			Application_Model_YoutubeVideosMapper::i()->delete($model);
		}
		
		// cleaning up all categories
		$models = Application_Model_YoutubeCategoriesMapper::i()->fetchAll();
		foreach ($models as $model) {
			Application_Model_YoutubeCategoriesMapper::i()->delete($model);
		}
		
		// cleaning up all accounts
		$models = Application_Model_YoutubeAccountsMapper::i()->fetchAll();
		foreach ($models as $model) {
			Application_Model_YoutubeAccountsMapper::i()->delete($model);
		}

		$mapOldNewCategoryId = array();
		
		foreach (@$items['categories'] as $modelInfo) {
			$model = new Application_Model_YoutubeCategory();
			$model->setLabel(@$modelInfo['label'])
				->setThumbnail(@$modelInfo['thumbnail'] != '' ? @$modelInfo['thumbnail'] : null)
				;
			// i don't set id, or db adapter will try to update old data that i cleaned
			Application_Model_YoutubeCategoriesMapper::i()->save($model);
			$mapOldNewCategoryId[@$modelInfo['id']] = $model->getId();
		}
		
		foreach (@$items['videos'] as $modelInfo) {
			$model = new Application_Model_YoutubeVideo();
			$model->setIdYoutube(@$modelInfo['idYoutube']) 
				->setDescription(@$modelInfo['description'])
				->setIdCategory(@$mapOldNewCategoryId[$modelInfo['idCategory']])
				->setLabel(@$modelInfo['label'])
				->setThumbnail(@$modelInfo['thumbnail'] != '' ? @$modelInfo['thumbnail'] : null)
				;
			// i don't set id, or db adapter will try to update old data that i cleaned
			Application_Model_YoutubeVideosMapper::i()->save($model);
		}

		foreach (@$items['accounts'] as $modelInfo) {
			$model = new Application_Model_YoutubeAccount();
			$model->setLabel(@$modelInfo['label'])
				->setThumbnail(@$modelInfo['thumbnail'] != '' ? @$modelInfo['thumbnail'] : null)
				;
			// i don't set id, or db adapter will try to update old data that i cleaned
			Application_Model_YoutubeAccountsMapper::i()->save($model);
		}
		
		
		return implode('<br/>',array(
				X_Env::_('p_youtube_backupper_restoredcategories'). ": " .count($items['categories']),
				X_Env::_('p_youtube_backupper_restoredvideos'). ": " .count($items['videos']),
				X_Env::_('p_youtube_backupper_restoredaccounts'). ": " .count($items['accounts'])
			));
		
		
	}
	
	
	
	//========================
	// MENUS
	//========================
	
	
	protected function mainMenu(X_Page_ItemList_PItem $items) {
		
		// disabling cache plugin
		try {
			$cachePlugin = X_VlcShares_Plugins::broker()->getPlugins('cache');
			if ( method_exists($cachePlugin, 'setDoNotCache') ) {
				$cachePlugin->setDoNotCache();
			}
		} catch (Exception $e) {
			// cache plugin not registered, no problem
		}
		
		
		$item = new X_Page_Item_PItem('youtube-accounts', X_Env::_('p_youtube_br_mainmenu_accounts'));
		$item->setDescription(X_Env::_('p_youtube_br_mainmenu_accounts_desc'))
			->setIcon('/images/youtube/icons/accounts.png')
			->setLink(array(
				'l' => X_Env::encode(self::MODE_ACCOUNTS)
			), 'default', false)
			->setGenerator(__CLASS__);
		$items->append($item);
		
		$item = new X_Page_Item_PItem('youtube-library', X_Env::_('p_youtube_br_mainmenu_library'));
		$item->setDescription(X_Env::_('p_youtube_br_mainmenu_library_desc'))
			->setIcon('/images/youtube/icons/library.png')
			->setLink(array(
				'l' => X_Env::encode(self::MODE_LIBRARY)
			), 'default', false)
			->setGenerator(__CLASS__);
		$items->append($item);
		
	}
	
	
	protected function accountsMenu(X_Page_ItemList_PItem $items, $accountId = null) {

		// disabling cache plugin
		try {
			$cachePlugin = X_VlcShares_Plugins::broker()->getPlugins('cache');
			if ( method_exists($cachePlugin, 'setDoNotCache') ) {
				$cachePlugin->setDoNotCache();
			}
		} catch (Exception $e) {
			// cache plugin not registered, no problem
		}
		
		
		if ( $accountId != null ) {
			$account = new Application_Model_YoutubeAccount();
			Application_Model_YoutubeAccountsMapper::i()->find($accountId, $account);
			if ( $account->getId() != null ) {
				
				$item = new X_Page_Item_PItem('youtube-a-uploaded', X_Env::_('p_youtube_account_uploaded'));
				$item->setDescription(X_Env::_('p_youtube_account_uploaded_desc'))
					->setIcon('/images/youtube/icons/uploaded.png')
					->setCustom(__CLASS__.':location', self::A_UPLOADED.'/'.$account->getId())
					->setLink(array(
						'l' => X_Env::encode(self::A_UPLOADED.'/'.$account->getId())
					), 'default', false)
					->setGenerator(__CLASS__);
				$items->append($item);
				
				$item = new X_Page_Item_PItem('youtube-a-favorites', X_Env::_('p_youtube_account_favorites'));
				$item->setDescription(X_Env::_('p_youtube_account_favorites_desc'))
					->setIcon('/images/youtube/icons/favorites.png')
					->setCustom(__CLASS__.':location', self::A_FAVORITES.'/'.$account->getId())
					->setLink(array(
						'l' => X_Env::encode(self::A_FAVORITES.'/'.$account->getId())
					), 'default', false)
					->setGenerator(__CLASS__);
				$items->append($item);

				$item = new X_Page_Item_PItem('youtube-a-subscriptions', X_Env::_('p_youtube_account_subscriptions'));
				$item->setDescription(X_Env::_('p_youtube_account_subscriptions_desc'))
					->setIcon('/images/youtube/icons/subscriptions.png')
					->setCustom(__CLASS__.':location', self::A_SUBSCRIPTIONS.'/'.$account->getId())
					->setLink(array(
						'l' => X_Env::encode(self::A_SUBSCRIPTIONS.'/'.$account->getId())
					), 'default', false)
					->setGenerator(__CLASS__);
				$items->append($item);

				$item = new X_Page_Item_PItem('youtube-a-activities', X_Env::_('p_youtube_account_activities'));
				$item->setDescription(X_Env::_('p_youtube_account_activities_desc'))
					->setIcon('/images/youtube/icons/activities.png')
					->setCustom(__CLASS__.':location', self::A_ACTIVITIES.'/'.$account->getId())
					->setLink(array(
						'l' => X_Env::encode(self::A_ACTIVITIES.'/'.$account->getId())
					), 'default', false)
					->setGenerator(__CLASS__);
				$items->append($item);

				$item = new X_Page_Item_PItem('youtube-a-playlists', X_Env::_('p_youtube_account_playlists'));
				$item->setDescription(X_Env::_('p_youtube_account_playlists_desc'))
					->setIcon('/images/youtube/icons/playlists.png')
					->setCustom(__CLASS__.':location', self::A_PLAYLISTS.'/'.$account->getId())
					->setLink(array(
						'l' => X_Env::encode(self::A_PLAYLISTS.'/'.$account->getId())
					), 'default', false)
					->setGenerator(__CLASS__);
				$items->append($item);
				
			} else {
				$this->accountsMenu($items, null);
			}
		} else {
			$accounts = Application_Model_YoutubeAccountsMapper::i()->fetchAll();
			foreach ($accounts as $account) {
				/* @var $account Application_Model_YoutubeAccount */
				
				$item = new X_Page_Item_PItem('youtube-accounts-'.$account->getId(), $account->getLabel());
				$item//->setDescription(X_Env::_('p_youtube_account_friends_desc'))
					->setIcon('/images/youtube/icons/account.png')
					->setCustom(__CLASS__.':location', self::MODE_ACCOUNTS.'/'.$account->getId())
					->setLink(array(
						'l' => X_Env::encode(self::MODE_ACCOUNTS.'/'.$account->getId())
					), 'default', false)
					->setGenerator(__CLASS__);
				$items->append($item);
								
			}
		}
	}
	
	protected function uploadedMenu(X_Page_ItemList_PItem $items, $accountId, $page = 0 ) {
		
		if ( $accountId != null ) {
			$account = new Application_Model_YoutubeAccount();
			Application_Model_YoutubeAccountsMapper::i()->find($accountId, $account);
		
			
			/* @var $helper X_VlcShares_Plugins_Helper_Youtube */
			$helper = $this->helpers('youtube');
			$feed = $helper->getVideosByUser($account->getLabel(), $page);
			
			$this->_prepareVideo($items, $feed, self::A_UPLOADED."/$accountId/$page");
		
		} else {
			$this->accountsMenu($items);
		}
	}

	protected function favoritesMenu(X_Page_ItemList_PItem $items, $accountId, $page = 0 ) {
		
		if ( $accountId != null ) {
			$account = new Application_Model_YoutubeAccount();
			Application_Model_YoutubeAccountsMapper::i()->find($accountId, $account);
		
			
			/* @var $helper X_VlcShares_Plugins_Helper_Youtube */
			$helper = $this->helpers('youtube');
			$feed = $helper->getFavoritesByUser($account->getLabel(), $page);
			
			$this->_prepareVideo($items, $feed, self::A_FAVORITES."/$accountId/$page");
			
			if ( $feed->getNextLink() != null ) {
				$items->append($this->next(self::A_FAVORITES."/$accountId", $page + 1));
			}
		} else {
			$this->accountsMenu($items);
		}
	}

	protected function subscriptionsMenu(X_Page_ItemList_PItem $items, $mode = null, $page = 0 ) {
		
		// $mode has a format:
		// $accountId,$subscriptionname
		
		@list($accountId, $subscription) = explode(',', $mode, 2);
		
		if ( $accountId != null ) {
			$account = new Application_Model_YoutubeAccount();
			Application_Model_YoutubeAccountsMapper::i()->find($accountId, $account);

			/* @var $helper X_VlcShares_Plugins_Helper_Youtube */
			$helper = $this->helpers('youtube');
			
			// i already have a subscription, so load uploaded video by subscription
			if ( $subscription != null ) {
				/* @var $feed Zend_Gdata_YouTube_VideoFeed */
				$feed = $helper->getVideosByUser($subscription, $page);
				$this->_prepareVideo($items, $feed, self::A_SUBSCRIPTIONS."/$accountId,$subscription/$page");
				if ( $feed->getNextLink() != null ) {
					$items->append($this->next(self::A_SUBSCRIPTIONS."/$accountId,$subscription", $page + 1));
				}
			} else {
				// i have to read all subscriptions
				$subscriptions = $helper->getSubscriptionsByUser($account->getLabel(), $page);
				foreach ($subscriptions as $sub) {
					/* @var $sub Zend_Gdata_YouTube_SubscriptionEntry */
					$un = $sub->getUsername();
					
					$item = new X_Page_Item_PItem('youtube-acc-subscriptions-'.$un, $sub->getTitle());
					$item->setDescription($sub->getSummary())
						->setThumbnail($sub->getMediaThumbnail())
						->setIcon('/images/youtube/icons/subscription.png')
						->setCustom(__CLASS__.':location', self::A_SUBSCRIPTIONS."/$accountId,$un")
						->setLink(array(
							'l' => X_Env::encode(self::A_SUBSCRIPTIONS."/$accountId,$un")
						), 'default', false)
						->setGenerator(__CLASS__);
					$items->append($item);
				}
				if ( $subscriptions->getNextLink() != null ) {
					$items->append($this->next(self::A_SUBSCRIPTIONS."/$accountId", $page + 1));
				}
				
			}
		} else {
			$this->accountsMenu($items);
		}
		
	}

	protected function playlistsMenu(X_Page_ItemList_PItem $items, $mode = null, $page = 0 ) {
		
		// $mode has a format:
		// $accountId,$subscriptionname
		
		@list($accountId, $playlist) = explode(',', $mode, 2);
		
		if ( $accountId != null ) {
			$account = new Application_Model_YoutubeAccount();
			Application_Model_YoutubeAccountsMapper::i()->find($accountId, $account);

			/* @var $helper X_VlcShares_Plugins_Helper_Youtube */
			$helper = $this->helpers('youtube');
			
			// i already have a subscription, so load uploaded video by subscription
			if ( $playlist != null ) {
				/* @var $feed Zend_Gdata_YouTube_VideoFeed */
				$feed = $helper->getVideosByPlaylist($playlist, $page);
				$this->_prepareVideo($items, $feed, self::A_PLAYLISTS."/$accountId,$playlist/$page");
				if ( $feed->getNextLink() != null ) {
					$items->append($this->next(self::A_PLAYLISTS."/$accountId,$playlist", $page + 1));
				}
			} else {
				// i have to read all subscriptions
				$playlists = $helper->getPlaylistsByUser($account->getLabel(), $page);
				foreach ($playlists as $pl) {
					/* @var $pl Zend_Gdata_YouTube_PlaylistListEntry */
					$un = $pl->getPlaylistId();
					
					$item = new X_Page_Item_PItem('youtube-acc-subscriptions-'.$pl, $pl->getTitle());
					$item->setDescription($pl->getDescription())
						->setIcon('/images/youtube/icons/playlist.png')
						->setCustom(__CLASS__.':location', self::A_PLAYLISTS."/$accountId,$un")
						->setLink(array(
							'l' => X_Env::encode(self::A_PLAYLISTS."/$accountId,$un")
						), 'default', false)
						->setGenerator(__CLASS__);
					$items->append($item);
				}
				if ( $playlists->getNextLink() != null ) {
					$items->append($this->next(self::A_PLAYLISTS."/$accountId", $page + 1));
				}
				
			}
		} else {
			$this->accountsMenu($items);
		}
		
	}
	
	protected function activitiesMenu(X_Page_ItemList_PItem $items, $accountId = null, $page = 0 ) {
		
		if ( $accountId != null ) {
			$account = new Application_Model_YoutubeAccount();
			Application_Model_YoutubeAccountsMapper::i()->find($accountId, $account);
		
			
			/* @var $helper X_VlcShares_Plugins_Helper_Youtube */
			$helper = $this->helpers('youtube');
			$feed = $helper->getActivitiesByUser($account->getLabel(), $page);

			foreach ($feed as $activity) {
				/* @var $activity Zend_Gdata_YouTube_ActivityEntry */
				
				$vid = $activity->getVideoId();
				
				if ( $vid == null ) continue;
				
				/* @var $video Zend_Gdata_YouTube_VideoEntry */
				$video = $helper->getVideo($vid);
				
				$thumb = $video->getVideoThumbnails();
				$thumb = $thumb[0]['url'];
				
				$item = new X_Page_Item_PItem('youtube-acc-activity-'.$vid, $activity->getTitle(). ": ".$video->getTitle());
				$item->setDescription($video->getVideoDescription())
					->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setThumbnail($thumb)
					->setIcon('/images/youtube/icons/activity.png')
					->setCustom(__CLASS__.':location', self::A_ACTIVITIES."/$accountId/0/$vid")
					->setLink(array(
						'action' => 'mode',
						'l' => X_Env::encode(self::A_ACTIVITIES."/$accountId/0/$vid")
					), 'default', false)
					->setGenerator(__CLASS__);
				$items->append($item);
				
			}
			/*
			if ( $feed->getNextLink() != null ) {
				$items->append($this->next(self::A_FAVORITES."/$accountId", $page + 1));
			}
			*/
		} else {
			$this->accountsMenu($items);
		}
		
		
	}

	protected function back($mode, $submode, $page = 0, $id = null) {
		
		$location = array($mode, $submode);
		if ( $page > 1 ) {
			$page--;
			$location[] = $page;
			if ( $id ) {
				$location[] = $id;
			}
		}
		$location = implode('/', $location);
		
		$item = new X_Page_Item_PItem('youtube-back', X_Env::_('p_youtube_backbutton'));
		$item->setIcon('/images/youtube/icons/back.png')
			->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setCustom(__CLASS__.':location', $location)
			->setLink(array(
				'l' => X_Env::encode("$location")
			), 'default', false)
			->setGenerator(__CLASS__);
		return $item;
	}
	
	
	protected function next($locationPrefix, $nextPage) {
		
		$item = new X_Page_Item_PItem('youtube-next', X_Env::_('p_youtube_nextbutton'));
		$item->setIcon('/images/youtube/icons/next.png')
			->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setCustom(__CLASS__.':location', "$locationPrefix/$nextPage")
			->setLink(array(
				'l' => X_Env::encode("$locationPrefix/$nextPage")
			), 'default', false)
			->setGenerator(__CLASS__);
		return $item;
	}
	
	protected function libraryMenu(X_Page_ItemList_PItem $items, $submode = false) {
		X_Debug::i("Submode requested: ". print_r($submode, true));
		
		// disabling cache plugin
		try {
			$cachePlugin = X_VlcShares_Plugins::broker()->getPlugins('cache');
			if ( method_exists($cachePlugin, 'setDoNotCache') ) {
				$cachePlugin->setDoNotCache();
			}
		} catch (Exception $e) {
			// cache plugin not registered, no problem
		}
		
		
		if ($submode === false || $submode == null ) {
			// load all categories in the library
			$categories = Application_Model_YoutubeCategoriesMapper::i()->fetchAll();
			foreach ( $categories as $category ) {
				/* @var $category Application_Model_YoutubeCategory */
				$item = new X_Page_Item_PItem('youtube-category-'.$category->getId(), $category->getLabel() );
				$item//->setDescription(X_Env::_('p_youtube_br_mainmenu_submenu_error'))
					->setThumbnail($category->getThumbnail())
					->setIcon('/images/youtube/icons/category.png')
					->setCustom(__CLASS__.':location', self::MODE_LIBRARY."/".$category->getId())
					->setLink(array(
						'l' => X_Env::encode(self::MODE_LIBRARY."/".$category->getId())
					), 'default', false)
					->setGenerator(__CLASS__);
				$items->append($item);
			}
		} else {
			// load all video inside a category categories
			/* @var $category Application_Model_YoutubeCategory */
			$category = new Application_Model_YoutubeCategory();
			Application_Model_YoutubeCategoriesMapper::i()->find($submode, $category);
			X_Debug::i('Category found: '.print_r($category, true));
			if ( $category->getId() != null && $category->getId() == $submode ) {
				$videos = Application_Model_YoutubeVideosMapper::i()->fetchByCategory($category->getId());
				foreach ( $videos as $video) {
					/* @var $video Application_Model_YoutubeVideo */
					$item = new X_Page_Item_PItem('youtube-library-'.$video->getId(), $video->getLabel() );
					$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
						->setIcon('/images/youtube/icons/video.png')
						->setDescription($video->getDescription())
						->setThumbnail($video->getThumbnail())
						->setCustom(__CLASS__.':location', self::MODE_LIBRARY."/".$category->getId()."/0/".$video->getId())
						->setLink(array(
							'action' => 'mode',
							'l' => X_Env::encode(self::MODE_LIBRARY."/".$category->getId()."/0/".$video->getId())
						), 'default', false)
						->setGenerator(__CLASS__);
					$items->append($item);
				}
			}
		}
	}


	private function _prepareVideo(X_Page_ItemList_PItem $items, Zend_Gdata_Media_Feed $feed, $locationPrefix) {

		foreach ( $feed as $yvideo ) {
			/* @var $yvideo Zend_Gdata_YouTube_VideoEntry */
			if ( $yvideo->getVideoDuration() == 0 ) continue; // no duration = no video
			$item = new X_Page_Item_PItem('youtube-video-'.$yvideo->getVideoId(), $yvideo->getVideoTitle() . ' [' . X_Env::formatTime($yvideo->getVideoDuration()) .']' );
			$thumb = $yvideo->getVideoThumbnails();
			@$thumb = $thumb[0]['url'];
			$item->setDescription($yvideo->getVideoDescription())
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setIcon('/images/youtube/icons/video.png')
				->setThumbnail($thumb)
				->setCustom(__CLASS__.':location', "$locationPrefix/{$yvideo->getVideoId()}")
				->setLink(array(
					'action' => 'mode',
					'l' => X_Env::encode("$locationPrefix/{$yvideo->getVideoId()}")
				), 'default', false)
				->setGenerator(__CLASS__);
			$items->append($item);
		} 
				
	}
}