<?php

require_once 'X/VlcShares/Plugins/Abstract.php';


/**
 * Add AnimeLand.it site as a videos source
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_NarutoGet extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const INDEX_NARUTO = 'naruto';
	const INDEX_SHIPPUDEN = 'shippuden'; 
	
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
				'label' => X_Env::_('p_narutoget_collectionindex'), 
				'link'	=> X_Env::completeUrl(
					$urlHelper->url(
						array(
							'controller' => 'browse',
							'action' => 'share',
							'p' => $this->getId(),
							'l' => base64_encode(self::INDEX_SHIPPUDEN)
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

			//try to disable SortItems plugin, so link are listed as in html page
			X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_SortItems');
			
			/*
			if ( $location == self::INDEX_SHIPPUDEN ) {
				*/
				$pageIndex = $this->config('index.shippuden.url', 'http://www.narutoget.com/naruto-shippuden-episodes/');
				
				$htmlString = $this->_loadPage($pageIndex);
				
				$dom = new Zend_Dom_Query($htmlString);
				
				$results = $dom->queryXpath('//a[@class="movie"]');
				
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
						__CLASS__.':location'	=>	$href
					);
					
				}
				/*
			}  else {
				
				list($type, $cat) = @explode(':', $location, 2);
				
				if ( $cat != null ) {
					
					$htmlString = $this->_loadPage($cat);
					
					$dom = new Zend_Dom_Query($htmlString);
					
					$results = $dom->queryXpath('//a[@class="movie"]');
					
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
							__CLASS__.':location'	=>	$href
						);
						
					}
						
					
					
				} else {
					$pageIndex = $this->config('index.naruto.url', 'http://www.narutoget.com/page/10-naruto-episodes-subbed/');
					
					$htmlString = $this->_loadPage($pageIndex);
					
					$dom = new Zend_Dom_Query($htmlString);
					
					$results = $dom->queryXpath('//div[@id="side-a"]//div[@align="left"]//a');
					
					for ( $i = 0; $i < $results->count(); $i++, $results->next()) {
						
						$node = $results->current();
						$href = $node->getAttribute('href');
						$label = $node->nodeValue;
						
						$items[] = array(
							'label'		=>	"$label",
							'link'		=>	X_Env::completeUrl(
								$urlHelper->url(
									array(
										'action' => 'share',
										'l'	=>	base64_encode("$type:$href")
									), 'default', false
								)
							),
							__CLASS__.':location'	=>	"$type:$href"
						);
						
					}
				}
			}
			*/
		} else {
			/*
			$items[] = array(
				'label'		=>	X_Env::_('p_narutoget_index_naruto'),
				'link'		=>	X_Env::completeUrl(
					$urlHelper->url(
						array(
							'action' => 'share',
							'l'	=>	base64_encode(self::INDEX_NARUTO)
						), 'default', false
					)
				),
				__CLASS__.':location'	=>	self::INDEX_NARUTO
			);
			*/		
			$items[] = array(
				'label'		=>	X_Env::_('p_narutoget_index_shippuden'),
				'link'		=>	X_Env::completeUrl(
					$urlHelper->url(
						array(
							'action' => 'share',
							'l'	=>	base64_encode(self::INDEX_SHIPPUDEN)
						), 'default', false
					)
				),
				__CLASS__.':location'	=>	self::INDEX_SHIPPUDEN
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
				'label'		=>	X_Env::_('p_narutoget_watchdirectly'),
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
		
		$htmlString = $this->_loadPage($location);
		
		$dom = new Zend_Dom_Query($htmlString);
		
		$results = $dom->queryXpath('//embed/attribute::src');
		
		if ( $results->valid() ) {

			$attr = $results->current()->nodeValue;
			$attrs = explode("&", $attr);
			foreach ($attrs as $attr) {
				@list($type, $value) = explode('=', $attr);
				if ( $type == 'video_src' ) {
					$this->cachedLocation[$location] = $value;
					return $value;
				}
			}
		}
		
		// fallback for type 2 of video:
		$results = $dom->queryXpath('//embed/attribute::flashvars');
		
		if ( $results->valid() ) {

			$attr = $results->current()->nodeValue;
			$attrs = explode("&", $attr);
			foreach ($attrs as $attr) {
				@list($type, $value) = explode('=', $attr);
				if ( $type == 'file' ) {
					$this->cachedLocation[$location] = $value;
					return $value;
				}
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
				'title'		=>	X_Env::_('p_narutoget_managetitle'),
				'label'		=>	X_Env::_('p_narutoget_mlink'),
				'link'		=>	$urlHelper->url(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'narutoget'
				)),
				'icon'		=>	'/images/narutoget/logo.png',
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
