<?php

require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'Megavideo.php'; // megavideo wrapper


/**
 * Megavideo plugin
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Megavideo extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	public function __construct() {
		$this->setPriority('getCollectionsItems')
			->setPriority('preRegisterVlcArgs')
			->setPriority('getShareItems')
			->setPriority('preGetModeItems');
	}
	
	/**
	 * Add the main link for megavideo library
	 * @param Zend_Controller_Action $controller
	 */
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
				'label' => X_Env::_('p_megavideo_collectionindex'), 
				'link'	=> X_Env::completeUrl(
					$urlHelper->url(
						array(
							'controller' => 'browse',
							'action' => 'share',
							'p' => $this->getId(),
						), 'default', true
					)
				)
			)
		);
	}
	
	/**
	 * Get category/video list
	 * @param unknown_type $provider
	 * @param unknown_type $location
	 * @param Zend_Controller_Action $controller
	 */
	public function getShareItems($provider, $location, Zend_Controller_Action $controller) {
		// this plugin add items only if it is the provider
		if ( $provider != $this->getId() ) return;
		
		X_Debug::i("Plugin triggered");
		
		$urlHelper = $controller->getHelper('url');
		
		$items = array();
		
		if ( $location != '' ) {
			
			//list($shareType, $linkId) = explode(':', $location, 2);
			// $location is the categoryName

			$videos = Application_Model_MegavideoMapper::i()->fetchByCategory($location);
			
			foreach ($videos as $video) {
				/* @var $video Application_Model_Megavideo */
				$items[] = array(
					'label'		=>	"{$video->getLabel()}",
					'link'		=>	X_Env::completeUrl(
						$urlHelper->url(
							array(
								'action' => 'mode',
								'l'	=>	base64_encode($video->getId())
							), 'default', false
						)
					),
					__CLASS__.':location'	=>	$video->getId()
				);
			}
			
		} else {
			// if location is not specified,
			// show collections
			
			$categories = Application_Model_MegavideoMapper::i()->fetchCategories();
			foreach ( $categories as $share ) {
				/* @var $share Application_Model_FilesystemShare */
				$items[] = array(
					'label'		=>	"{$share['category']} ({$share['links']})",
					'link'		=>	X_Env::completeUrl(
						$urlHelper->url(
							array(
								'l'	=>	base64_encode($share['category'])
							), 'default', false
						)
					),
					__CLASS__.':location'	=>	$share['category']
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
		
		$video = new Application_Model_Megavideo();
		Application_Model_MegavideoMapper::i()->find($location, $video);
		
		if ( $video->getId() != null ) {
			$megavideo = new Megavideo($video->getIdVideo());
	    	return array(array(
				'label'		=>	X_Env::_('p_megavideo_watchdirectly'),
				'link'		=>	$megavideo->get('URL'),
	    		'type'		=>	X_Plx_Item::TYPE_VIDEO	
			));
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
		if ( (int) $location < 0 ) return false; // megavideo_model id > 0
		
		$video = new Application_Model_Megavideo();
		Application_Model_MegavideoMapper::i()->find((int) $location, $video);
		
		// TODO prevent ../
		if ( $video->getId() == null ) return false;
		
		$megavideo = new Megavideo($video->getIdVideo());
		
		return $megavideo->get('URL');
	}
	
}
