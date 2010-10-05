<?php

require_once 'X/VlcShares/Plugins/Abstract.php';


/**
 * Add OPFItalia site as a video source
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_OPFItalia extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
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
				'label' => X_Env::_('p_opfitalia_collectionindex'), 
				'link'	=> X_Env::completeUrl(
					$urlHelper->url(
						array(
							'controller' => 'browse',
							'action' => 'share',
							'p' => $this->getId(),
						), 'default', true
					)
				),
				'icon'	=> '/images/opfitalia/logo.png',
				'desc'	=> X_Env::_('p_opfitalia_collectionindex_desc')
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
		
		//try to disable SortItems plugin, so link are listed as in html page
		X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_SortItems');
		
		if ( $location != '' ) {
		
			$pageIndex = $this->config('index.url', 'http://www.opfitalia.net/mediacenter/streaming.php');
			$htmlString = $this->_loadPage($pageIndex);
			$dom = new Zend_Dom_Query($htmlString);
			
			// xpath index start from 1, not 0
			$results = $dom->queryXpath('//div[@class="saga"]['.((int)$location+1).']/following-sibling::div[1]//div[@class="video"]//td[@style="white-space: nowrap"]/a');
			
			for ( $i = 0; $i < $results->count(); $i++, $results->next()) {

				$node = $results->current();
				$href = $node->getAttribute('href');
				$label = $node->nodeValue;
				
				// i discard the video from megavideo
				if ( strpos($href, 'megavideo') !== false ) continue;
				
				list(,$href) = explode('?', $href, 2);
				
				$href = explode('&', $href);
				foreach ( $href as $param ) {
					list($key, $value) = explode('=', $param, 2);
					if ( $key == 'video') {
						$href = $value;
						break;
					}
				}
				
				@$epName = $node->parentNode->nextSibling->nextSibling->firstChild->nodeValue;
				
				$items[] = array(
					'label'		=>	"$label: $epName",
					'link'		=>	X_Env::completeUrl(
						$urlHelper->url(
							array(
								'action' => 'mode',
								'l'	=>	base64_encode($href)
							), 'default', false
						)
					),
					__CLASS__.':location'	=>	$href,
					'icon'	=>	'/images/icons/file_32.png'
				);
			}
			
			if ( count($items) == 0 ) {
				$items[] = array(
					'label'		=>	X_Env::_('p_opfitalia_opsnovideo'),
					'link'		=>	X_Env::completeUrl(
						$urlHelper->url()
					)
				);
			}
			
		} else {

			
			$pageIndex = $this->config('index.url', 'http://www.opfitalia.net/mediacenter/streaming.php');
			$htmlString = $this->_loadPage($pageIndex);
			$dom = new Zend_Dom_Query($htmlString);
			
			$results = $dom->queryXpath('//div[@class="saga"]/a');
			
			for ( $i = 0; $i < $results->count(); $i++, $results->next()) {
			
				$label = $results->current()->nodeValue;
				
				$items[] = array(
					'label'		=>	X_Env::_('p_opfitalia_saga') . ": $label",
					'link'		=>	X_Env::completeUrl(
						$urlHelper->url(
							array(
								'l'	=>	base64_encode($i)
							), 'default', false
						)
					),
					__CLASS__.':location'	=>	$i,
					'icon'	=>	'/images/icons/folder_32.png'
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
		
		$url = $this->resolveLocation($location);
		
		if ( $url ) {
	    	return array(array(
				'label'		=>	X_Env::_('p_opfitalia_watchdirectly'),
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

		return $location;
		
	}
	
	/**
	 * No support for parent location
	 * @see X_VlcShares_Plugins_ResolverInterface::getParentLocation
	 * @param $location
	 */
	function getParentLocation($location = null) {
		if ($location == null || $location == '') return false;
		
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
				'title'		=>	X_Env::_('p_opfitalia_managetitle'),
				'label'		=>	X_Env::_('p_opfitalia_mlink'),
				'link'		=>	$urlHelper->url(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'opfitalia'
				)),
				'icon'		=>	'/images/opfitalia/logo.png',
				'subinfos'	=> array()
			),
		);
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
