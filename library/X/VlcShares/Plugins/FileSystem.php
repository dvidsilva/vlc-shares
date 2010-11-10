<?php

require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/VlcShares/Plugins/BackuppableInterface.php';

/**
 * VlcShares 0.4.2+ plugin:
 * provide filesystem based collection.
 * With this plugin, you can add directories
 * to vlc-shares's collection
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_FileSystem extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverDisplayableInterface, X_VlcShares_Plugins_BackuppableInterface {
	
	public function __construct() {
		$this->setPriority('getCollectionsItems')
			->setPriority('preRegisterVlcArgs')
			->setPriority('getShareItems')
			->setPriority('getIndexManageLinks')
			->setPriority('getIndexStatistics')
			->setPriority('getIndexActionLinks');
	}
	
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");
		
		// usando le opzioni, determino quali link inserire
		// all'interno della pagina delle collections
		
		$urlHelper = $controller->getHelper('url');
		/* @var $urlHelper Zend_Controller_Action_Helper_Url */
		
		//$serverUrl = $controller->getFrontController()->getBaseUrl();
		$request = $controller->getRequest();
		/* @var $request Zend_Controller_Request_Http */
		//$request->get
		
		return array(
			array(
				'label' => X_Env::_('p_filesystem_collectionindex'), 
				'link'	=> X_Env::completeUrl(
					$urlHelper->url(
						array(
							'controller' => 'browse',
							'action' => 'share',
							'p' => $this->getId(),
						), 'default', true
					)
				),
				'icon'	=> '/images/filesystem/logo.png',
				'desc'	=> X_Env::_('p_filesystem_collectionindex_desc'),
				'itemType' => 'folder'
			)
		);
	}
	
	public function getShareItems($provider, $location, Zend_Controller_Action $controller) {
		// this plugin add items only if it is the provider
		if ( $provider != $this->getId() ) return;
		
		X_Debug::i("Plugin triggered");
		
		$urlHelper = $controller->getHelper('url');
		
		$items = array();
		
		if ( $location != '' ) {
			list($shareId, $path) = explode(':', $location, 2);
			
			$share = new Application_Model_FilesystemShare();
			Application_Model_FilesystemSharesMapper::i()->find($shareId, $share);
			
			// TODO prevent ../
			
			$browsePath = realpath($share->getPath().$path);
			if ( file_exists($browsePath)) {
				$dir = new DirectoryIterator($browsePath);
				foreach ($dir as $entry) {
					if ( $entry->isDot() )
						continue;
		
					if ( $entry->isDir() ) {
						$items[] = array(
							'label'		=>	"{$entry->getFilename()}/",
							'link'		=>	X_Env::completeUrl(
								$urlHelper->url(
									array(
										'l'	=>	base64_encode("{$share->getId()}:{$path}{$entry->getFilename()}/")
									), 'default', false
								)
							),
							__CLASS__.':location'	=>	"{$share->getId()}:{$path}{$entry->getFilename()}/",
							'icon'		=>	'/images/icons/folder_32.png',
							'itemType'	=>	'folder'
						);
						
						
					} else if ($entry->isFile() ) {
						$items[] = array(
							'label'		=>	"{$entry->getFilename()}",
							'link'		=>	X_Env::completeUrl(
								$urlHelper->url(
									array(
										'action' => 'mode',
										'l'	=>	base64_encode("{$share->getId()}:{$path}{$entry->getFilename()}")
									), 'default', false
								)
							),
							__CLASS__.':location'	=>	"{$share->getId()}:{$path}{$entry->getFilename()}",
							'icon'		=>	'/images/icons/file_32.png',
							'itemType'	=>	'file'
						);
						
					} else {
						// scarta i symlink
						continue;
					}
						
				}
			}
			
		} else {
			// if location is not specified,
			// show collections
			
			$shares = Application_Model_FilesystemSharesMapper::i()->fetchAll();
			foreach ( $shares as $share ) {
				/* @var $share Application_Model_FilesystemShare */
				$items[] = array(
					'label'		=>	$share->getLabel(),
					'link'		=>	X_Env::completeUrl(
						$urlHelper->url(
							array(
								'l'	=>	base64_encode("{$share->getId()}:/")
							), 'default', false
						)
					),
					__CLASS__.':location'	=>	"{$share->getId()}:/",
					'icon'		=>	'/images/icons/folder_32.png',
					'desc'		=>	"{$share->getPath()}",
					'itemType'	=>	'folder'
				);
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
		
		if ( $location !== null && file_exists($location) ) {
			// TODO adapt to newer api when ready
			$vlc->registerArg('source', "\"$location\"");			
		} else {
			X_Debug::e("No source o_O");
		}
	
	}
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::resolveLocation
	 * @param string $location
	 * @return string real address of a resource
	 */
	function resolveLocation($location = null) {

		// prevent no-location-given error
		if ( $location === null || $location === '') return false;
		
		@list($shareId, $path) = explode(':', $location, 2);
		
		$share = new Application_Model_FilesystemShare();
		Application_Model_FilesystemSharesMapper::i()->find($shareId, $share);
		
		// TODO prevent ../
		
		return realpath($share->getPath().$path);
	}
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::getParentLocation
	 * @param $location
	 */
	function getParentLocation($location = null) {
		if ($location == null || $location == '') return false;
		
		@list($shareId, $path) = explode(':', $location, 2);
		if (rtrim($path,'\\/') == '' ) return null;
		
		return $shareId.':'.rtrim(dirname($path),'\\/').'/';
		
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
		
		$urlHelper = $controller->getHelper('url');
		
		return array(
			array(
				'label'		=>	X_Env::_('p_filesystem_actionadddirectory'),
				'link'		=>	$urlHelper->url(array(
					'controller'	=>	'filesystem',
					'action'		=>	'index',
					'a'				=>	'add'
				)),
				'icon'		=>	'/images/plus.png'
			)
		);
	}
	
	/**
	 * Add the link for -manage-megavideo-
	 * @param Zend_Controller_Action $this
	 * @return array The format of the array should be:
	 * 		array(
	 * 			array(
	 * 				'title' => ITEM TITLE,
	 * 				'label' => ITEM LABEL,
	 * 				'link'	=> HREF,
	 * 				'highlight'	=> true|false,
	 * 				'icon'	=> ICON_HREF,
	 * 				'subinfos' => array(INFO, INFO, INFO)
	 * 			), ...
	 * 		)
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$urlHelper = $controller->getHelper('url');
		
		return array(
			array(
				'title'		=>	X_Env::_('p_filesystem_managetitle'),
				'label'		=>	X_Env::_('p_filesystem_mlink'),
				'link'		=>	$urlHelper->url(array(
					'controller'	=>	'filesystem',
					'action'		=>	'index'
				)),
				'icon'		=>	'/images/filesystem/logo.png',
				'subinfos'	=> array()
			),
		);
	
	}
	
	/**
	 * Retrieve statistic from plugins
	 * @param Zend_Controller_Action $this
	 * @return array The format of the array should be:
	 * 		array(
	 * 			array(
	 * 				'title' => ITEM TITLE,
	 * 				'label' => ITEM LABEL,
	 * 				'stats' => array(INFO, INFO, INFO),
	 * 				'provider' => array('controller', 'index', array()) // if provider is setted, stats key is ignored 
	 * 			), ...
	 * 		)
	 */
	public function getIndexStatistics(Zend_Controller_Action $controller) {
		
		$collections = count(Application_Model_FilesystemSharesMapper::i()->fetchAll()); // FIXME create count functions
		
		return array(
			array(
				'title'	=> X_Env::_('p_filesystem_statstitle'),
				'label'	=> X_Env::_('p_filesystem_statstitle'),
				'stats'	=>	array(
					X_Env::_('p_filesystem_statcollections').": $collections",
				)
			)
		);
		
	}
	
	/**
	 * Backup shared directories
	 * This is not a trigger of plugin API. It's called by Backupper plugin
	 */
	function getBackupItems() {
		
		$return = array();
		$models = Application_Model_FilesystemSharesMapper::i()->fetchAll();
		
		foreach ($models as $model) {
			/* @var $model Application_Model_FilesystemShare */
			$return['shares']['share-'.$model->getId()] = array(
				'id'			=> $model->getId(), 
	            'path'   		=> $model->getPath(),
	            'image' 		=> $model->getImage(),
	        	'label' 		=> $model->getLabel(),
			);
		}
		
		return $return;
	}
	
	/**
	 * Restore backupped shared directories
	 * This is not a trigger of plugin API. It's called by Backupper plugin
	 */
	function restoreItems($items) {
		
		$shares = Application_Model_FilesystemSharesMapper::i()->fetchAll();
		// cleaning up all shares
		foreach ($shares as $share) {
			Application_Model_FilesystemSharesMapper::i()->delete($share);
		}
	
		foreach (@$items['shares'] as $shareInfo) {
			$share = new Application_Model_FilesystemShare();
			$share->setPath(@$shareInfo['path'])
				->setImage(@$shareInfo['image'])
				->setLabel(@$shareInfo['label']);
			// i don't set id, or db adapter will try to update old data that i cleaned
			Application_Model_FilesystemSharesMapper::i()->save($share);
		}
		
		return X_Env::_('p_filesystem_backupper_restoreditems'). ": " .count($items['shares']);
		
		//return parent::restoreItems($items);
	}
	
		
}
