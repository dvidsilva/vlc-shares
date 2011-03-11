<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'Zend/Config.php';
require_once 'Zend/Controller/Request/Abstract.php';

class X_VlcShares_Plugins_FsThumbs extends X_VlcShares_Plugins_Abstract {

	public function __construct() {
		
		$this->setPriority('gen_beforeInit')
			->setPriority('filterShareItems')
			->setPriority('getIndexStatistics')
			->setPriority('getIndexManageLinks');
		
		
	}	


	/**
	 * Inizialize translator for this plugin
	 * @param Zend_Controller_Action $controller
	 */
	function gen_beforeInit(Zend_Controller_Action $controller) {
		$this->helpers()->language()->addTranslation(__CLASS__);
		
		if ( !$this->helpers()->ffmpeg()->isEnabled() ) {
			$this->setPriority('getIndexMessages');
		}
		
	}
	
	/**
	 * Show an error if ffmpeg isn't enabled
	 */
	function getIndexMessages(Zend_Controller_Action $controller) {
		X_Debug::i('Plugin triggered');
		
		$m = new X_Page_Item_Message($this->getId(), X_Env::_('p_fsthumbs_dashboardffmpegerror'));
		$m->setType(X_Page_Item_Message::TYPE_ERROR);
		return new X_Page_ItemList_Message(array($m));
		
	}
	
	/**
	 * Retrieve thumbnails statistics
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Statistic
	 */
	public function getIndexStatistics(Zend_Controller_Action $controller) {
		
		
		$entries = Application_Model_FsThumbsMapper::i()->getCount();
		
		$stat = new X_Page_Item_Statistic($this->getId(), X_Env::_('p_fsthumbs_statstitle'));
		$stat->setTitle(X_Env::_('p_fsthumbs_statstitle'))
			->appendStat(X_Env::_('p_fsthumbs_storedentries', Application_Model_FsThumbsMapper::i()->getCount(), $this->config('max.cached', 200)))
			->appendStat(X_Env::_('p_fsthumbs_storedentries_dim', self::formatSize(Application_Model_FsThumbsMapper::i()->getTotalSize())))
			;
			
		if ( $entries ) { 

			$urlHelper = $controller->getHelper('url');
			
			$clearOverHref = $urlHelper->url(array(
				'controller' => 'fsthumbs',
				'action'	=> 'delete',
				'type'	=> 'over'
			), 'default', true);
			
			$clearAllHref = $urlHelper->url(array(
				'controller' => 'fsthumbs',
				'action'	=> 'delete',
				'type'	=> 'all'
			), 'default', true);
			
			$clearOrphanHref = $urlHelper->url(array(
				'controller' => 'fsthumbs',
				'action'	=> 'delete',
				'type'	=> 'orphan'
			), 'default', true);
			
			$clearOverLink = '<a href="'.$clearOverHref.'">'.X_Env::_('p_fsthumbs_actions_removeover').'</a>';
			$clearOrphanLink = '<a href="'.$clearOrphanHref.'">'.X_Env::_('p_fsthumbs_actions_removeorphan').'</a>';
			$clearAllLink = '<a href="'.$clearAllHref.'">'.X_Env::_('p_fsthumbs_actions_removeall').'</a>';
			
			
			$stat->appendStat($clearOverLink)
				->appendStat($clearOrphanLink)
				->appendStat($clearAllLink);
		}

		return new X_Page_ItemList_Statistic(array($stat));	
	}
	
	/**
	 * Check the item in the collection should be filtered out
	 * If return is false, the item will be discarded at 100%
	 * If return is true, isn't sure that the item will be added
	 * 'cause another plugin can prevent this
	 * 
	 * Plugins who check per-item acl or blacklist should hook here
	 * 
	 * @param X_Page_Item_PItem $item
	 * @param string $provider
	 * @param Zend_Controller_Action $controller
	 * @return boolean true if item is ok, false if item should be discarded
	 */
	public function filterShareItems(X_Page_Item_PItem $item, $provider, Zend_Controller_Action $controller) {
	
		$providerClass = X_VlcShares_Plugins::broker()->getPluginClass($provider);
		$providerObj = X_VlcShares_Plugins::broker()->getPlugins($provider);
		
		
		if ( is_a($providerObj, 'X_VlcShares_Plugins_FileSystem') ) {
			
			if ( $item->getType() == X_Page_Item_PItem::TYPE_ELEMENT ) {
				
				$itemLocation = $item->getCustom('X_VlcShares_Plugins_FileSystem:location');

				/* @var $urlHelper Zend_Controller_Action_Helper_Url */
				$urlHelper = $controller->getHelper('url');
				
				$path = $providerObj->resolveLocation($itemLocation);
				
				$thumb = new Application_Model_FsThumb();
				Application_Model_FsThumbsMapper::i()->fetchByPath($path, $thumb);
				
				if ( $thumb->isNew() ) {
					$thumbUrl = X_Env::completeUrl($urlHelper->direct('thumb', 'fsthumbs', 'default', array('l' => X_Env::encode($itemLocation), 't' => 'dummy.jpg')));
				} else {
					$thumbUrl = X_Env::completeUrl(Zend_Controller_Front::getInstance()->getBaseUrl().$thumb->getUrl());
				}
				
				//$item->setDescription($itemLocation);
				
				$item->setThumbnail($thumbUrl);
				
			}
		}
		
		return true;
	}
	
	
	
	
	/**
	 * Add the link for -manage-thumbnails-
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

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_fsthumbs_mlink'));
		$link->setTitle(X_Env::_('p_fsthumbs_managetitle'))
			->setIcon('/images/fsthumbs/logo.png')
			->setLink(array(
					'controller'	=>	'fsthumbs',
					'action'		=>	'index',
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
	
	}
	
	
	/**
	 * Show size in printable format
	 * @return string
	 */
	static public function formatSize($size) {
		$sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
		if ($size == 0) { return('n/a'); } else {
		return (round($size/pow(1024, ($i = floor(log($size, 1024)))), $i > 1 ? 2 : 0) . $sizes[$i]); }
	}
	
}
