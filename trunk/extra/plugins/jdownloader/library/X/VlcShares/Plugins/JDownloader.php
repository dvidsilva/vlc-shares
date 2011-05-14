<?php

/**
 * JDownloader integration plugin
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_JDownloader extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const VERSION_CLEAN = '0.2.1';
	const VERSION = '0.2.1';
	
	public function __construct() {
		$this
			->setPriority('gen_beforeInit')
			->setPriority('getCollectionsItems')
			->setPriority('preGetShareItems')
			->setPriority('getShareItems')
			->setPriority('getSelectionItems')
			->setPriority('getIndexManageLinks')
			;
				
	}
	
	/**
	 * Registers a jdownloader helper inside the helper broker
	 */
	public function gen_beforeInit(Zend_Controller_Action $controller) {
		
		$this->helpers()->language()->addTranslation(__CLASS__);
		
		$helper_conf = new Zend_Config(array(
			'ip' => $this->config('remoteapi.ip', 'localhost'),
			'port' => $this->config('remoteapi.port', '10025'),
			'timeout' => $this->config('request.timeout', '1'),
			'nightly' => $this->config('version.isnightly', false)
		)); 
		
		$this->helpers()->registerHelper('jdownloader', new X_VlcShares_Plugins_Helper_JDownloader($helper_conf));
		
		if ( $this->config('download.enabled', true) ) {
			// Add a DOWNLOAD this video LINK
			$this
				->setPriority('getModeItems')
				;
		}
		
		if ( $this->config('statistics.enabled', true) ) {
			$this
				->setPriority('getIndexStatistics');
		}
	}
	
	/**
	 * Add the link for -manage-jdownloader-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_jdownloader_mlink'));
		$link->setTitle(X_Env::_('p_jdownloader_managetitle'))
			->setIcon('/images/jdownloader/logo.png')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'jdownloader'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}
	
	
	/**
	 * Add Jdownloader to collection index
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");

		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_jdownloader_collectionindex'));
		$link->setIcon('/images/jdownloader/logo.png')
			->setDescription(X_Env::_('p_jdownloader_collectionindex_desc'))
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
	 * Show JDownloader status
	 * @param unknown_type $provider
	 * @param unknown_type $location
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function preGetShareItems($provider, $location, Zend_Controller_Action $controller) {
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
		
		// unregister sort plugin, it's useless now
		X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_SortItems');
		
		/* @var $jdHelper X_VlcShares_Plugins_Helper_JDownloader */
		$jdHelper = $this->helpers('jdownloader');
		
		/* @var $url Zend_View_Helper_Url */
		$urlHelper = $controller->getHelper('url');
		
		$list = new X_Page_ItemList_PItem();
		
		try {
			
			$link = new X_Page_Item_PItem($this->getId().'-status', X_Env::_('p_jdownloader_share_status', 
				$jdHelper->sendRawCommand(X_VlcShares_Plugins_Helper_JDownloader::CMD_GET_DOWNLOADSTATUS),
				$jdHelper->sendRawCommand(X_VlcShares_Plugins_Helper_JDownloader::CMD_GET_SPEED)
			));
			$link->setIcon('/images/jdownloader/logo.png')
				//->setDescription( $fileDesc )
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(X_Env::completeUrl($urlHelper->url()));
					
			$list->append($link);
	
			$link = new X_Page_Item_PItem($this->getId().'-start', X_Env::_('p_jdownloader_share_status_start'));
			$link->setIcon('/images/icons/play.png')
				//->setDescription( $fileDesc )
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(array(
						'action'				=> 'selection',
						'pid'					=> $this->getId(),
						"{$this->getId()}:action" => 'start',
					), 'default', false);
					
			$list->append($link);
	
			$link = new X_Page_Item_PItem($this->getId().'-pause', X_Env::_('p_jdownloader_share_status_pause'));
			$link->setIcon('/images/icons/pause.png')
				//->setDescription( $fileDesc )
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(array(
						'action'				=> 'selection',
						'pid'					=> $this->getId(),
						"{$this->getId()}:action" => 'pause',
				), 'default', false);
								
			$list->append($link);
	
			$link = new X_Page_Item_PItem($this->getId().'-stop', X_Env::_('p_jdownloader_share_status_stop'));
			$link->setIcon('/images/icons/stop.png')
				//->setDescription( $fileDesc )
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(array(
						'action'				=> 'selection',
						'pid'					=> $this->getId(),
						"{$this->getId()}:action" => 'stop',
					), 'default', false);
								
			$list->append($link);
	
			if ( $this->helpers()->devices()->isWiimc() ) {
				$link = new X_Page_Item_PItem($this->getId().'-packageseparator', X_Env::_('p_jdownloader_share__packageseparator'));
				$link//->setIcon('/images/jdownloader/logo.png')
					//->setDescription( $fileDesc )
					->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setLink(X_Env::completeUrl($urlHelper->url()));
						
				$list->append($link);
			}
			
		} catch (Exception $e ) {
			
			// connection error, i have to remote getShareItem registration
			
			$this->setPriority('getShareItems', -1);
			
			$link = new X_Page_Item_PItem($this->getId().'-error', X_Env::_('p_jdownloader_selection_error_network'));
			$link->setDescription($e->getMessage());
				$link->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setLink(array(
						'action'				=> 'share',
						'pid'					=> null,
						"{$this->getId()}:action" => null,
				), 'default', false);
			$list->append($link);
		}
		return $list;
		
	}
	
	/**
	 * Get download queue status
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
		
		// unregister sort plugin, it's useless now
		X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_SortItems');
		
		/* @var $jdHelper X_VlcShares_Plugins_Helper_JDownloader */
		$jdHelper = $this->helpers('jdownloader');
		
		/* @var $url Zend_View_Helper_Url */
		$urlHelper = $controller->getHelper('url');
	
		$packages = $jdHelper->getDownloads();
		
		X_Debug::i("Packages found: " .count($packages));
		
		$list = new X_Page_ItemList_PItem();
		
		foreach ($packages as $package) {
			/* @var $package Application_Model_JDownloaderPackage */
			
			if ( $this->helpers()->devices()->isWiimc() ) {
				$packageLabel = X_Env::_('p_jdownloader_share_packageentry_nohtml', $package->getName(), $package->getSize(), $package->getFilesCount(), $package->getETA(), $package->getPercent(), X_Env::_('p_jdownloader_downloadstate_'.($package->isDownloading() ? '1' : '0') ) );
				$packageDesc = X_Env::_('p_jdownloader_share_packageentry_desc_nohtml', $package->getSize(), $package->getFilesCount(), $package->getETA(), $package->getPercent(), X_Env::_('p_jdownloader_downloadstate_'.($package->isDownloading() ? '1' : '0') ) );
			} else {
				$packageLabel = X_Env::_('p_jdownloader_share_packageentry_html', $package->getName() );
				$packageDesc = X_Env::_('p_jdownloader_share_packageentry_desc_html', $package->getSize(), $package->getFilesCount(), $package->getETA(), $package->getPercent(), X_Env::_('p_jdownloader_downloadstate_'.($package->isDownloading() ? '1' : '0') ) );
			}
			
			$link = new X_Page_Item_PItem($this->getId().'-package', $packageLabel);
			$link->setIcon('/images/icons/folder.png')
				->setDescription($packageDesc)
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(X_Env::completeUrl($urlHelper->url()));
					
			$list->append($link);
			
			X_Debug::i("{$package->getFilesCount()} files in package {$package->getName()}");
			
			foreach ($package->getFiles() as $file ) {
				
				/* @var $file Application_Model_JDownloaderFile */
				
				if ( $this->helpers()->devices()->isWiimc() ) {
					$fileLabel = X_Env::_('p_jdownloader_share_fileentry_nohtml', $file->getName(), $file->getHoster(), $file->getPercent(), X_Env::_('p_jdownloader_downloadstate_'.($file->isDownloading() ? '1' : '0') ) );
					$fileDesc = X_Env::_('p_jdownloader_share_fileentry_desc_nohtml', $file->getHoster(), $file->getPercent(), X_Env::_('p_jdownloader_downloadstate_'.($file->isDownloading() ? '1' : '0') ) );
				} else {
					$fileLabel = X_Env::_('p_jdownloader_share_fileentry_html', $file->getName() );
					$fileDesc = X_Env::_('p_jdownloader_share_fileentry_desc_html', $file->getHoster(), $file->getPercent(), X_Env::_('p_jdownloader_downloadstate_'.($file->isDownloading() ? '1' : '0') ) );
				}
				
				
				$link = new X_Page_Item_PItem($this->getId().'-file', $fileLabel);
				$link//->setIcon('/images/icons/file.png')
					->setDescription( $fileDesc )
					->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setLink(X_Env::completeUrl($urlHelper->url()));
						
				$list->append($link);
				
			}
		}
		
		return $list;
		
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

		// if provider is FileSystem, JDownload isn't needed for sure
		if ( X_VlcShares_Plugins::broker()->getPluginClass($provider) == 'X_VlcShares_Plugins_FileSystem' ) {
			return;
		}
		
		try {
		
			/* @var $megavideoHelper X_VlcShares_Plugins_Helper_Megavideo */
			$megavideoHelper = $this->helpers('megavideo');
			// check megavideo helper to be sure that location has been setted for check
			
			// if location is not setted, helper throwns an exception.
			// i don't care for the returned value
			$megavideoHelper->getServer();
			
			X_Debug::i('Plugin triggered. Location could be provided by Megavideo');
			
			$urlHelper = $controller->getHelper('url');
	
			$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_jdownloader_downloadlink'));
			$link->setIcon('/images/jdownloader/logo.png')
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(array(
						'action'	=>	'selection',
						'pid'		=>	$this->getId()
					), 'default', false);
	
			return new X_Page_ItemList_PItem(array($link));
				
		} catch (Exception $e) {
			
			// this use the newer hoster api
			try {
				
				switch ($provider) {
					// special cases
					
					case 'onlinelibrary':
						// hoster/id is stored inside db
						$exploded = explode('/', $location);
						$exploded = array_pop($exploded);
						$video = new Application_Model_Video();
						Application_Model_VideosMapper::i()->find($exploded, $video);
						$hoster = $video->getHoster();
						$videoId = $video->getIdVideo();
						break;
						
						
					default:
						// we try to get information by location decoding
						// many plugins use a /-divided location with last param in the format of HOSTER:VIDEOID
						$exploded = explode('/', $location);
						$exploded = array_pop($exploded);
						$exploded = explode(':', $exploded, 2);
						
						@list($hoster, $videoId) = $exploded;
						break;
						
				}
				
			
				
				// lets search a valid hoster
				$this->helpers()->hoster()->getHoster($hoster);
				
				$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_jdownloader_downloadlink'));
				$link->setIcon('/images/jdownloader/logo.png')
					->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setLink(array(
							'action'	=>	'selection',
							'pid'		=>	$this->getId()
						), 'default', false);
		
				return new X_Page_ItemList_PItem(array($link));
				
			} catch (Exception $e) {
				X_Debug::i("Location is not provided by a valid plugin/hoster. Try direct download");
				
				// last chance: allow to start download from the resolveLocation
				
				$pObj = X_VlcShares_Plugins::broker()->getPlugins($provider);
				if ( $pObj instanceof X_VlcShares_Plugins_ResolverInterface ) {
					$url = $pObj->resolveLocation($location);
					if ( X_Env::startWith($url, "http://") || X_Env::startWith($url, "https://") ) {
						$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_jdownloader_downloadlink_location', $url));
						$link->setIcon('/images/jdownloader/logo.png')
							->setType(X_Page_Item_PItem::TYPE_ELEMENT)
							->setLink(array(
									'action'	=>	'selection',
									'pid'		=>	$this->getId()
								), 'default', false);
				
						return new X_Page_ItemList_PItem(array($link));
					} else {
						X_Debug::i("Location isn't http or https");
					}
				}
				
			}
		}
	}
	
	
	/**
	 * Add megavideo id to jdownloader download queue
	 * @param string $provider
	 * @param string $location
	 * @param string $pid
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function getSelectionItems($provider, $location, $pid, Zend_Controller_Action $controller) {
		// we want to expose items only if pid is this plugin
		if ( $this->getId() != $pid	) {
			return;
		}
		
		X_Debug::i('Plugin triggered');		

		$action = $controller->getRequest()->getParam("{$this->getId()}:action", '');
		
		
		/* @var $jdownloader X_VlcShares_Plugins_Helper_JDownloader */
		$jdownloader = $this->helpers('jdownloader');

		// IF action = 'start, stop or pause'
		// execute the action and return confirm, then exit
		if ( $action != '' ) {
		
			try {
				if ( $action == "start" ) {
					$jdownloader->sendRawCommand(X_VlcShares_Plugins_Helper_JDownloader::CMD_ACTION_START);
					$link = new X_Page_Item_PItem($this->getId().'-started', X_Env::_('p_jdownloader_selection_started'));
					$link->setType(X_Page_Item_PItem::TYPE_ELEMENT)
						->setLink(array(
							'action'				=> 'share',
							'pid'					=> null,
							"{$this->getId()}:action" => null,
						), 'default', false);
					return new X_Page_ItemList_PItem(array($link));
				}
		
				if ( $action == "pause" ) {
					$jdownloader->sendRawCommand(X_VlcShares_Plugins_Helper_JDownloader::CMD_ACTION_PAUSE);
					$link = new X_Page_Item_PItem($this->getId().'-started', X_Env::_('p_jdownloader_selection_paused'));
					$link->setType(X_Page_Item_PItem::TYPE_ELEMENT)
						->setLink(array(
							'action'				=> 'share',
							'pid'					=> null,
							"{$this->getId()}:action" => null,
						), 'default', false);
					return new X_Page_ItemList_PItem(array($link));
				}
				
				if ( $action == "stop" ) {
					$jdownloader->sendRawCommand(X_VlcShares_Plugins_Helper_JDownloader::CMD_ACTION_STOP);
					$link = new X_Page_Item_PItem($this->getId().'-started', X_Env::_('p_jdownloader_selection_stopped'));
					$link->setType(X_Page_Item_PItem::TYPE_ELEMENT)
						->setLink(array(
							'action'				=> 'share',
							'pid'					=> null,
							"{$this->getId()}:action" => null,
						), 'default', false);
					return new X_Page_ItemList_PItem(array($link));
				}
			} catch (Exception $e) {
				// connection problems or timeout
				$link = new X_Page_Item_PItem($this->getId().'-error', X_Env::_('p_jdownloader_selection_error_network'));
				$link->setDescription($e->getMessage());
					$link->setType(X_Page_Item_PItem::TYPE_ELEMENT)
						->setLink(array(
							'action'				=> 'share',
							'pid'					=> null,
							"{$this->getId()}:action" => null,
					), 'default', false);
				return new X_Page_ItemList_PItem(array($link));
			}
			
		} else {
		
			// else only confirm the addict 
			
			/* @var $megavideo X_VlcShares_Plugins_Helper_Megavideo */
			$megavideo = $this->helpers('megavideo');
			
			try {
				$megavideo->getServer();
			} catch (Exception $e) {
				// not loaded location yet
				X_Debug::i("Force location loading");
				$providerObj = X_VlcShares_Plugins::broker()->getPlugins($provider);
				if ( $providerObj instanceof X_VlcShares_Plugins_ResolverInterface ) {
					$providerObj->resolveLocation($location);
				}
			}
			
			$url = null;
			
			try {
				$url = "http://www.megavideo.com/?v=".$megavideo->getId();
			} catch (Exception $e) {
				
				// TODO please, rewrite this piece of code
				// it's really a shame
				
				// let's try to get the url from the hoster
				try {

					switch ($provider) {
						// special cases
						
						case 'onlinelibrary':
							// hoster/id is stored inside db
							$exploded = explode('/', $location);
							$exploded = array_pop($exploded);
							$video = new Application_Model_Video();
							Application_Model_VideosMapper::i()->find($exploded, $video);
							$hoster = $video->getHoster();
							$videoId = $video->getIdVideo();
							break;
							
							
						default:
							// we try to get information by location decoding
							// many plugins use a /-divided location with last param in the format of HOSTER:VIDEOID
							$exploded = explode('/', $location);
							$exploded = array_pop($exploded);
							$exploded = explode(':', $exploded, 2);
							
							@list($hoster, $videoId) = $exploded;
							break;
							
					}
					
					// lets search a valid hoster
					$url = $this->helpers()->hoster()->getHoster($hoster)->getHosterUrl($videoId);
					
				} catch (Exception $e) {
					// simply: provider isn't compatible
					// trying direct download
					$pObj = X_VlcShares_Plugins::broker()->getPlugins($provider);
					if ( $pObj instanceof X_VlcShares_Plugins_ResolverInterface ) {
						$url = $pObj->resolveLocation($location);
						if ( !X_Env::startWith($url, "http://") && !X_Env::startWith($url, "https://") ) {
							// not valid, revert changes
							$url = null;
						}
					}
						
				}
				
			}
			
			try {
				
				if ( $url === null ) {
					throw new Exception();
				}
				
				X_Debug::i("Appending {{$url}} to the queue");
				
				$jdownloader->addLink($url);
				$link = new X_Page_Item_PItem($this->getId().'-added', X_Env::_('p_jdownloader_selection_added'));
				
			} catch (Zend_Http_Client_Adapter_Exception $e) {
				// connection problems or timeout
				$link = new X_Page_Item_PItem($this->getId().'-error', X_Env::_('p_jdownloader_selection_error_network'));
				$link->setDescription($e->getMessage());
				
			} catch (Exception $e) {
				// wrapper/other error
				X_Debug::e("Trying to add a new download to JDowloader, but location isn't a megavideo url or there is an error");
				$link = new X_Page_Item_PItem($this->getId().'-error', X_Env::_('p_jdownloader_selection_error_invalid'));
				$link->setDescription($e->getMessage());
			}
			
			$link->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(array(
					'action'				=> 'mode',
					$this->getId()			=> null, // unset this plugin selection
					'pid'					=> null
				), 'default', false);
			return new X_Page_ItemList_PItem(array($link));
			
		}
	}
	
	/**
	 * Retrieve jdownloader statistics
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Statistic
	 */
	public function getIndexStatistics(Zend_Controller_Action $controller) {
		
		
		//$entries = Application_Model_CacheMapper::i()->getCount();
		
		/* @var $jdHelper X_VlcShares_Plugins_Helper_JDownloader */
		$jdHelper = $this->helpers('jdownloader');
		
		/* @var $url Zend_View_Helper_Url */
		$urlHelper = $controller->getHelper('url');

		try {
			$downloadStatus = $jdHelper->sendRawCommand(X_VlcShares_Plugins_Helper_JDownloader::CMD_GET_DOWNLOADSTATUS) ;
			$connstatus = true;
		} catch (Exception $e) {
			$connstatus = false;
		}
		
		
		$stat = new X_Page_Item_Statistic($this->getId(), X_Env::_('p_jdownloader_statstitle'));
		$stat->setTitle(X_Env::_('p_jdownloader_statstitle'))
			->appendStat(X_Env::_('p_jdownloader_stat_connectionstatus', X_Env::_('p_jdownloader_stat_connection_'.($connstatus ? '1' : '0'))));
			
		if ( $connstatus ) { 

			$currentSpeed = $jdHelper->sendRawCommand(X_VlcShares_Plugins_Helper_JDownloader::CMD_GET_SPEED);
			$itemsTotal = $jdHelper->sendRawCommand(X_VlcShares_Plugins_Helper_JDownloader::CMD_GET_DOWNLOADS_ALL_COUNT);
			$itemsDone = $jdHelper->sendRawCommand(X_VlcShares_Plugins_Helper_JDownloader::CMD_GET_DOWNLOADS_FINISHED_COUNT);
			
			$stat->appendStat(X_Env::_('p_jdownloader_stat_downloadstatus', $downloadStatus))
				->appendStat(X_Env::_('p_jdownloader_stat_currentspeed', $currentSpeed))
				->appendStat(X_Env::_('p_jdownloader_stat_itemsdone', $itemsDone, $itemsTotal))
				;
		} else {
			$stat->appendStat(X_Env::_('p_jdownloader_stat_connectionerrorhelp'));
		}

		return new X_Page_ItemList_Statistic(array($stat));
	}
	
	
	function resolveLocation($location = null) {
		return false;
	}
	
	function getParentLocation($location = null) {
		return false;
	}
	
}