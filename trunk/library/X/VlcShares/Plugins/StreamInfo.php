<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/Vlc.php';

/**
 * Add infos to controls page
 * 
 * Configs:
 * 
 * - show Title label
 * 		show.title = true
 * 
 * - show Current position
 * 		show.time = false
 * 
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_StreamInfo extends X_VlcShares_Plugins_Abstract {

	public function __construct() {
		$this->setPriority('preGetControlItems', 99)
		->setPriority('getIndexManageLinks');
	}	
	
	/**
	 * Display current title and/or current position
	 * @param Zend_Controller_Action $controller
	 */
	public function preGetControlItems(Zend_Controller_Action $controller) {
		$urlHelper = $controller->getHelper('url');
		
		$vlc = X_Vlc::getLastInstance();
		
		$return = array();
		
		if ( $this->config('show.title', true)) {
			// show the title of the file
			$return[] =	array(
				'label'	=>	X_Env::_('p_streaminfo_onair'). ": {$vlc->getCurrentName()}",
				'link'	=>	X_Env::completeUrl($urlHelper->url()),
			);
		}
		
		if ( $this->config('show.time', false)) {
			$currentTime = X_Env::formatTime($vlc->getCurrentTime());
			$totalTime = X_Env::formatTime($vlc->getTotalTime());
			
			// show current position
			$return[] =	array(
				'label'	=>	"{$currentTime}/{$totalTime}",
				'link'	=>	X_Env::completeUrl($urlHelper->url()),
			);
		}
		
		return $return;
	}
	
	/**
	 * Add the link for -manage-streaminfo-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_streaminfo_mlink'));
		$link->setTitle(X_Env::_('p_streaminfo_managetitle'))
			->setIcon('/images/manage/configs.png')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'streaminfo'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
	
	}
	
}
