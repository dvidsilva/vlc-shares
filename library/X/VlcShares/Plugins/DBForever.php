<?php

require_once 'X/VlcShares/Plugins/Abstract.php';


/**
 * Add DBForever.org site as a video source
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_DBForever extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const INDEX_NARUTO = 'strm_naruto';
	const INDEX_ONEPIECE = 'strm_onepiece';
	const INDEX_BLEACH = 'strm_bleach';
	
	public function __construct() {
		$this->setPriority('getCollectionsItems')
			->setPriority('preRegisterVlcArgs')
			->setPriority('getShareItems')
			->setPriority('preGetModeItems')
			->setPriority('getIndexManageLinks');
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
				'label' => X_Env::_('p_dbforever_collectionindex'), 
				'link'	=> X_Env::completeUrl(
					$urlHelper->url(
						array(
							'controller' => 'browse',
							'action' => 'share',
							'p' => $this->getId(),
						), 'default', true
					)
				),
				'icon'	=> '/images/dbforever/logo.png',
				'desc'	=> X_Env::_('p_dbforever_collectionindex_desc'),
				'itemType'		=>	'folder',
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
		
		if ( $location != '' && ( $location == self::INDEX_NARUTO || $location == self::INDEX_ONEPIECE || $location == self::INDEX_BLEACH   ) ) {
			
			
			$pageIndex = $this->config('index.url', 'http://www.dbforever.net/home.php')."?page=$location";
			
			$htmlString = $this->_loadPage($pageIndex);
			
			$dom = new Zend_Dom_Query($htmlString);
			
			$results = $dom->queryXpath('//div[@align="left"]/a');
			
			for ( $i = 0; $i < $results->count(); $i++, $results->next()) {
				
				$node = $results->current();
				$href = $node->getAttribute('href');
				$label = $node->nodeValue;
				
				$items[] = array(
					'label'		=>	"$label",
					'link'		=>	X_Env::completeUrl(
						$urlHelper->url(
							array(
								'action' => 'mode',
								'l'	=>	base64_encode($href)
							), 'default', false
						)
					),
					__CLASS__.':location'	=>	$href,
					'icon'		=> '/images/icons/file_32.png',
					'itemType'		=>	'file'
				);
				
			}
			
			
			
		} else {
			
			$items[] = array(
				'label'		=>	X_Env::_('p_dbforever_naruto_ep'),
				'link'		=>	X_Env::completeUrl(
					$urlHelper->url(
						array(
							'l'	=>	base64_encode(self::INDEX_NARUTO)
						), 'default', false
					)
				),
				__CLASS__.':location'	=>	self::INDEX_NARUTO,
				'icon'		=> '/images/icons/folder_32.png',
				'thumb'		=> 'http://www.dbforever.net/img/banner/naruto_banner_grande.jpg',
				'itemType'		=>	'folder'
			);

			$items[] = array(
				'label'		=>	X_Env::_('p_dbforever_onepiece_ep'),
				'link'		=>	X_Env::completeUrl(
					$urlHelper->url(
						array(
							'l'	=>	base64_encode(self::INDEX_ONEPIECE)
						), 'default', false
					)
				),
				__CLASS__.':location'	=>	self::INDEX_ONEPIECE,
				'icon'		=> '/images/icons/folder_32.png',
				'thumb'		=> 'http://www.dbforever.net/img/banner/onepiece_banner_grande.jpg',
				'itemType'		=>	'folder'
			);
			
			$items[] = array(
				'label'		=>	X_Env::_('p_dbforever_bleach_ep'),
				'link'		=>	X_Env::completeUrl(
					$urlHelper->url(
						array(
							'l'	=>	base64_encode(self::INDEX_BLEACH)
						), 'default', false
					)
				),
				__CLASS__.':location'	=>	self::INDEX_BLEACH,
				'icon'		=> '/images/icons/folder_32.png',
				'thumb'		=> 'http://www.dbforever.net/img/banner/bleach_banner_grande.jpg',
				'itemType'		=>	'folder'
			);
			
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
		
		if ( $url ) {
	    	return array(array(
				'label'		=>	X_Env::_('p_dbforever_watchdirectly'),
				'link'		=>	$url,
	    		'type'		=>	X_Plx_Item::TYPE_VIDEO	
			));
		}
		
		
		
	}
	
	private $cachedLocation = array();
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::resolveLocation
	 * @param string $location
	 * @return string real address of a resource
	 */
	function resolveLocation($location = null) {

		if ( array_key_exists($location, $this->cachedLocation) ) {
			return $this->cachedLocation[$location];
		}
		
		// prevent no-location-given error
		if ( $location === null ) return false;
		
		$pageVideo = $this->config('index.url', 'http://www.dbforever.net/home.php')."$location";
		
		$htmlString = $this->_loadPage($pageVideo);
		
		$dom = new Zend_Dom_Query($htmlString);
		
		$results = $dom->queryXpath('//embed/attribute::flashvars');
		
		if ( $results->valid() ) {

			$attr = $results->current()->nodeValue;
			$attrs = explode("&", $attr);
			foreach ($attrs as $attr) {
				list($type, $value) = explode('=', $attr);
				if ( $type == 'file' ) {
					// fix for relative links inside bleach category
					if ( !X_Env::startWith($value, 'http://') ) {
						$value = "http://www.dbforever.net$value";
					}
					$this->cachedLocation[$location] = $value;
					return $value;
				}
			}
		}
		$this->cachedLocation[$location] = false;
		return false;
		
	}
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::getParentLocation
	 * @param $location
	 */
	function getParentLocation($location = null) {
		if ($location == null || $location == '') return false;
		
		if ( $location == self::INDEX_BLEACH || $location == self::INDEX_ONEPIECE || $location == self::INDEX_NARUTO ) {
			return null; // no parent for category index. Fallback to normal index
		} else {
			if ( X_Env::startWith($location, '?page=') ) {
				// ok, we are inside a category
				$location = substr($location, strlen('?page='));
				if ( X_Env::startWith($location, self::INDEX_BLEACH)) {
					return self::INDEX_BLEACH;
				} elseif ( X_Env::startWith($location, self::INDEX_BLEACH)) {
					return self::INDEX_NARUTO;
				} elseif ( X_Env::startWith($location, self::INDEX_ONEPIECE) || X_Env::startWith($location, 'strm_one_piece') ) {
					// i need to use double condition because in the page i have an inconsitence
					return self::INDEX_ONEPIECE;
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}
	
	/**
	 * Add the link for -manage-dbforever-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_dbforever_mlink'));
		$link->setTitle(X_Env::_('p_dbforever_managetitle'))
			->setIcon('/images/dbforever/logo.png')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'dbforever'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}
	
	private function _loadPage($uri) {

		$http = new Zend_Http_Client($uri, array(
			'maxredirects'	=> $this->config('request.maxredirects'),
			'timeout'		=> $this->config('request.timeout')
		));
		
		$response = $http->request();
		$htmlString = $response->getBody();
		return $htmlString;
	}
	
}
