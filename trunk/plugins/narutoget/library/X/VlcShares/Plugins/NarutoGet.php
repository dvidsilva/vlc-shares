<?php

require_once 'X/VlcShares/Plugins/Abstract.php';


/**
 * Add AnimeLand.it site as a videos source
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_NarutoGet extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const VERSION = '0.2';
	const VERSION_CLEAN = '0.2';
	
	const INDEX_NARUTO = 'naruto';
	const INDEX_SHIPPUDEN = 'shippuden'; 
	
	public function __construct() {
		$this->setPriority('getCollectionsItems')
			->setPriority('preRegisterVlcArgs')
			->setPriority('getShareItems')
			->setPriority('preGetModeItems')
			->setPriority('gen_beforeInit')
			->setPriority('getIndexManageLinks');
	}
	
	/**
	 * Inizialize translator for this plugin
	 * @param Zend_Controller_Action $controller
	 */
	function gen_beforeInit(Zend_Controller_Action $controller) {
		$this->helpers()->language()->addTranslation(__CLASS__);
	}
	
	
	/**
	 * Add the main link for megavideo library
	 * @param Zend_Controller_Action $controller
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");
		
		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_narutoget_collectionindex'));
		$link->setIcon('/images/narutoget/logo.png')
			->setDescription(X_Env::_('p_narutoget_collectionindex_desc'))
			->setType(X_Page_Item_PItem::TYPE_CONTAINER)
			->setLink(
				array(
					'controller' => 'browse',
					'action' => 'share',
					'p' => $this->getId(),
					//'l' => X_Env::encode(self::INDEX_SHIPPUDEN)
				), 'default', true
			);
		return new X_Page_ItemList_PItem(array($link));
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
		
		$items = new X_Page_ItemList_PItem();
		/*
		if ( $location != '' ) {

			
			/*
			if ( $location == self::INDEX_SHIPPUDEN ) {
				*/
		
				//try to disable SortItems plugin, so link are listed as in html page
				X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_SortItems');
		
				$pageIndex = $this->config('index.shippuden.url', 'http://www.narutoget.com/naruto-shippuden-episodes/');
				
				$htmlString = $this->_loadPage($pageIndex);
				
				$dom = new Zend_Dom_Query($htmlString);
				
				$results = $dom->queryXpath('//a[@class="movie"]');
				
				for ( $i = 0; $i < $results->count(); $i++, $results->next()) {
					
					$node = $results->current();
					$href = $node->getAttribute('href');
					$label = $node->nodeValue;
					
					$item = new X_Page_Item_PItem($this->getId().'-'.$label, $label);
					$item->setIcon('/images/icons/file_32.png')
						->setType(X_Page_Item_PItem::TYPE_ELEMENT)
						->setCustom(__CLASS__.':location', $href)
						->setLink(array(
							'action' => 'mode',
							'l'	=>	X_Env::encode($href)
						), 'default', false);
					$items->append($item);
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
										'l'	=>	X_Env::encode($href)
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
										'l'	=>	X_Env::encode("$type:$href")
									), 'default', false
								)
							),
							__CLASS__.':location'	=>	"$type:$href"
						);
						
					}
				}
			}
		} else {
			$items[] = array(
				'label'		=>	X_Env::_('p_narutoget_index_naruto'),
				'link'		=>	X_Env::completeUrl(
					$urlHelper->url(
						array(
							'action' => 'share',
							'l'	=>	X_Env::encode(self::INDEX_NARUTO)
						), 'default', false
					)
				),
				__CLASS__.':location'	=>	self::INDEX_NARUTO
			);
			$items[] = array(
				'label'		=>	X_Env::_('p_narutoget_index_shippuden'),
				'link'		=>	X_Env::completeUrl(
					$urlHelper->url(
						array(
							'action' => 'share',
							'l'	=>	X_Env::encode(self::INDEX_SHIPPUDEN)
						), 'default', false
					)
				),
				__CLASS__.':location'	=>	self::INDEX_SHIPPUDEN
			);
		}
		*/
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
			$link = new X_Page_Item_PItem('core-directwatch', X_Env::_('p_narutoget_watchdirectly'));
			$link->setIcon('/images/icons/play.png')
				->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
				->setLink($url);
			return new X_Page_ItemList_PItem(array($link));
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
		if ( $location == null ) return false;
		
		$htmlString = $this->_loadPage($location);
		
		$dom = new Zend_Dom_Query($htmlString);
		
		$results = $dom->queryXpath('//embed/attribute::src');
		
		if ( $results->valid() ) {
			
			$attr = $results->current()->nodeValue;
			
			X_Debug::i("Location source type1: $attr");
			
			$attrs = explode("&", $attr);
			foreach ($attrs as $attr) {
				@list($type, $value) = explode('=', $attr, 2);
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
			
			X_Debug::i("Location source type2: $attr");
			
			$attrs = explode("&", $attr);
			foreach ($attrs as $attr) {
				@list($type, $value) = explode('=', $attr, 2);
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
	 * @see X_VlcShares_Plugins_ResolverInterface::getParentLocation
	 * @param $location
	 */
	function getParentLocation($location = null) {
		if ($location == null || $location == '') {
			return false;
		} else {
			return null;
		}
	}
	
	
	/**
	 * Add the link for -manage-megavideo-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_narutoget_mlink'));
		$link->setTitle(X_Env::_('p_narutoget_managetitle'))
			->setIcon('/images/narutoget/logo.png')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'narutoget'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
	}
	
	private function _loadPage($uri) {

		$http = new Zend_Http_Client($uri, array(
			'maxredirects'	=> $this->config('request.maxredirects', 0),
			'timeout'		=> $this->config('request.timeout', 10)
		));
		
		$response = $http->request();
		$htmlString = $response->getBody();
		return $htmlString;
	}
	
}
