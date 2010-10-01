<?php

require_once 'X/VlcShares/Plugins/Abstract.php';


/**
 * Add Allsp.com site as a video source
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_SouthPark extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {

	private $seasons = array(
		1 => "http://allsp.com/e.php?season=1",
		2 => "http://allsp.com/e.php?season=2",
		3 => "http://allsp.com/e.php?season=3",
		4 => "http://allsp.com/e.php?season=4",
		5 => "http://allsp.com/e.php?season=5",
		6 => "http://allsp.com/e.php?season=6",
		7 => "http://allsp.com/e.php?season=7",
		8 => "http://allsp.com/e.php?season=8",
		9 => "http://allsp.com/e.php?season=9",
		10 => "http://allsp.com/e.php?season=10",
		11 => "http://allsp.com/e.php?season=11",
		12 => "http://allsp.com/e.php?season=12",
		13 => "http://allsp.com/e.php?season=13",
		14 => "http://allsp.com/e.php?season=14",
	);
	
	
	public function __construct() {
		$this->setPriority('getCollectionsItems')
			->setPriority('preRegisterVlcArgs')
			->setPriority('getShareItems')
			->setPriority('preGetModeItems')
			->setPriority('getIndexManageLinks');
	}
	
	/**
	 * Add the main link for southpark library
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
				'label' => X_Env::_('p_southpark_collectionindex'), 
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
		
		// try to disable SortItems plugin, so link are listed as in html page
		X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_SortItems');
		
		
		$urlHelper = $controller->getHelper('url');
		
		$items = array();
		
		if ( $location != '' && array_key_exists((int) $location, $this->seasons) ) {
			
			// episodes list
			$url = $this->seasons[(int) $location];
			
			$html = $this->_loadPage($url);
			$dom = new Zend_Dom_Query($html);
			
			$results = $dom->queryXpath('//div[@id="randomVideos"]//div[@class="randomTab"]//a[@class="previewDescriptionTitle"]');
			
			for ( $i = 0; $i < $results->count(); $i++, $results->next()) {
				
				$node = $results->current();
				$href = $node->getAttribute('href');
				$label = $node->nodeValue;
				
				$id = explode('id=', $href, 2);
				$id = @$id[1];
				
				$items[] = array(
					'label'		=>	"$label",
					'link'		=>	X_Env::completeUrl(
						$urlHelper->url(
							array(
								'action' => 'mode',
								'l'	=>	base64_encode($id)
							), 'default', false
						)
					),
					__CLASS__.':location'	=>	$id
				);
				
			}
				
			
		} else {
			
			foreach ($this->seasons as $key => $seasons) {
				$items[] = array(
					'label'		=>	X_Env::_('p_southpark_season_n').": $key",
					'link'		=>	X_Env::completeUrl(
						$urlHelper->url(
							array(
								'l'	=>	base64_encode($key)
							), 'default', false
						)
					),
					__CLASS__.':location'	=>	$key
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
				'label'		=>	X_Env::_('p_southpark_watchdirectly'),
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
		
		$pageVideo = "http://allsp.com/xml.php?id=$location";
		
		$htmlString = $this->_loadPage($pageVideo);
		
		$dom = new Zend_Dom_Query($htmlString);
		
		$results = $dom->queryXpath('//location');
		
		if ( $results->valid() ) {

			$value = $results->current()->nodeValue;
			if ( $value != '' ) {
				$this->cachedLocation[$location] = $value;
				return $value;
			}
		}
		$this->cachedLocation[$location] = false;
		return false;
		
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
				'title'		=>	X_Env::_('p_southpark_managetitle'),
				'label'		=>	X_Env::_('p_southpark_mlink'),
				'link'		=>	$urlHelper->url(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'southpark'
				)),
				'icon'		=>	'/images/southpark/logo.png',
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
