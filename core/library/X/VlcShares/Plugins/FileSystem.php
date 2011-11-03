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
	
	/**
	 * Return the -shared-folders- link
	 * for the collection index
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");
		
		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_filesystem_collectionindex'));
		$link->setIcon('/images/filesystem/logo.png')
			->setDescription(X_Env::_('p_filesystem_collectionindex_desc'))
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
	 * Return a list of page items for the current $location
	 * if the $provider is this
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
						$item = new X_Page_Item_PItem($this->getId().'-'.$entry->getFilename().'/', "{$entry->getFilename()}/");
						$item->setIcon('/images/icons/folder_32.png')
							->setType(X_Page_Item_PItem::TYPE_CONTAINER)
							->setCustom(__CLASS__.':location', "{$share->getId()}:{$path}{$entry->getFilename()}/")
							->setLink(array(
								'l'	=>	X_Env::encode("{$share->getId()}:{$path}{$entry->getFilename()}/")
							), 'default', false);
						$items->append($item);
					} else if ($entry->isFile() ) {
						$item = new X_Page_Item_PItem($this->getId().'-'.$entry->getFilename(), "{$entry->getFilename()}");
						$item->setIcon('/images/icons/file_32.png')
							->setType(X_Page_Item_PItem::TYPE_ELEMENT)
							->setCustom(__CLASS__.':location', "{$share->getId()}:{$path}{$entry->getFilename()}")
							->setLink(array(
								'action' => 'mode',
								'l'	=>	X_Env::encode("{$share->getId()}:{$path}{$entry->getFilename()}")
							), 'default', false);
						$items->append($item);
						
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
				$item = new X_Page_Item_PItem($this->getId().'-'.$share->getLabel(), $share->getLabel());
				$item->setIcon('/images/icons/folder_32.png')
					->setDescription(APPLICATION_ENV == 'development' ? $share->getPath() : null)
					->setType(X_Page_Item_PItem::TYPE_CONTAINER)
					->setCustom(__CLASS__.':location', "{$share->getId()}:/")
					->setLink(array(
						'l'	=>	X_Env::encode("{$share->getId()}:/")
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
	 * Add the link Add new shared folder link to actionLinks
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ActionLink
	 */
	public function getIndexActionLinks(Zend_Controller_Action $controller) {
		
		$urlHelper = $controller->getHelper('url');
		
		$link = new X_Page_Item_ActionLink($this->getId(), X_Env::_('p_filesystem_actionadddirectory'));
		$link->setIcon('/images/plus.png')
			->setLink(array(
					'controller'	=>	'filesystem',
					'action'		=>	'add'
				), 'default', true);
		return new X_Page_ItemList_ActionLink(array($link));
		
	}
	
	/**
	 * Add the link for -manage-sharef-folders-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {
		
		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_filesystem_mlink'));
		$link->setTitle(X_Env::_('p_filesystem_managetitle'))
			->setIcon('/images/filesystem/logo.png')
			->setLink(array(
					'controller'	=>	'filesystem',
					'action'		=>	'index',
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
	
	}
	
	/**
	 * Show the number of shared folders in the dashboard
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Statistic
	 */
	public function getIndexStatistics(Zend_Controller_Action $controller) {
		
		$collections = count(Application_Model_FilesystemSharesMapper::i()->fetchAll()); // FIXME create count functions
		
		$stat = new X_Page_Item_Statistic($this->getId(), X_Env::_('p_filesystem_statstitle'));
		$stat->setTitle(X_Env::_('p_filesystem_statstitle'))
			->appendStat(X_Env::_('p_filesystem_statcollections').": $collections");

		return new X_Page_ItemList_Statistic(array($stat));
		
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
