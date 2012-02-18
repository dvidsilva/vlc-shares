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
	 * Add the button BackToStream in controls page
	 *
	 * @param X_Streamer_Engine $engine
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array
	 */
	public function preGetControlItems(X_Streamer_Engine $engine, Zend_Controller_Action $controller) {
		
		$urlHelper = $controller->getHelper('url');
		
		$return = new X_Page_ItemList_PItem();
		
		if ( $this->config('show.title', true)) {

			$onAirName = X_Env::_("p_streaminfo_unknown_source");
			
			if ( $engine instanceof X_Streamer_Engine_Vlc ) {
				$vlc = $engine->getVlcWrapper();
				$onAirName = $vlc->getCurrentName();
			} else {
				// try to find the name from the location (if any)
				$providerId = $controller->getRequest()->getParam('p', false);
				$location = $controller->getRequest()->getParam('l', false);
				if ( $providerId && $location ) {
					$providerObj = X_VlcShares_Plugins::broker()->getPlugins($providerId);
					$location = X_Env::decode($location);
					if ( $providerObj instanceof X_VlcShares_Plugins_ResolverInterface ) {
						$onAirName = $providerObj->resolveLocation($location);
					}
				}
			}
				
			// show the title of the file
			$item = new X_Page_Item_PItem('streaminfo-onair', X_Env::_('p_streaminfo_onair'). ": {$onAirName}");
			$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(X_Env::completeUrl($urlHelper->url()));
			$return->append($item);
			
		}
		
		if ( $engine instanceof X_Streamer_Engine_Vlc ) {
			$vlc = $engine->getVlcWrapper();
			
			if ( $this->config('show.time', false)) {
				$currentTime = X_Env::formatTime($vlc->getCurrentTime());
				$totalTime = X_Env::formatTime($vlc->getTotalTime());
	
				$item = new X_Page_Item_PItem('streaminfo-time', "{$currentTime}/{$totalTime}");
				$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setLink(X_Env::completeUrl($urlHelper->url()));
				$return->append($item);
			}
			
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
