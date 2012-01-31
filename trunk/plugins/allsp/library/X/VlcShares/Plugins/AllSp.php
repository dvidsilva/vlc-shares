<?php

require_once 'X/VlcShares/Plugins/Abstract.php';


/**
 * Add Allsp.com site as a video source
 * @author ximarx
 * @version 0.2
 */
class X_VlcShares_Plugins_AllSp extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const VERSION = '0.2';
	const VERSION_CLEAN = '0.2';

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
		15 => "http://allsp.com/e.php?season=15",
	);
	
	
	public function __construct() {
		$this->setPriority('gen_beforeInit')
			->setPriority('getCollectionsItems')
			->setPriority('preRegisterVlcArgs')
			->setPriority('getShareItems')
			->setPriority('preGetModeItems')
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
	 * Add the main link for allsp library
	 * @param Zend_Controller_Action $controller
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");

		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_allsp_collectionindex'));
		$link->setIcon('/images/allsp/logo.png')
			->setDescription(X_Env::_('p_allsp_collectionindex_desc'))
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
		
		$items = new X_Page_ItemList_PItem();
		
		if ( $location != '' && array_key_exists((int) $location, $this->seasons) ) {
			
			// episodes list
			$url = $this->seasons[(int) $location];
			
			$html = $this->_loadPage($url);
			$dom = new Zend_Dom_Query($html);
			
			$results = $dom->queryXpath('//div[@id="randomVideos"]//div[@class="randomTab"]//a[@class="previewDescriptionTitle"]');
			
			$resultsImages = $dom->queryXpath('//div[@id="randomVideos"]//div[@class="randomTab"]//img[1]/attribute::src');
			
			for ( $i = 0; $i < $results->count(); $i++, $results->next()) {
				
				$node = $results->current();
				$href = $node->getAttribute('href');
				$label = $node->nodeValue;
				
				$id = explode('id=', $href, 2);
				$id = @$id[1];

				$thumb = null;
				try {
					if ( $resultsImages->valid() ) {
						$thumb = $resultsImages->current()->nodeValue;
						$resultsImages->next();
					}
				} catch ( Exception $e) {
					$thumb = null;
				}
				
				$item = new X_Page_Item_PItem($this->getId().'-'.$label, $label);
				$item->setIcon('/images/icons/file_32.png')
					->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setCustom(__CLASS__.':location', $id)
					->setLink(array(
						'action' => 'mode',
						'l'	=>	X_Env::encode($id)
					), 'default', false);
				if ( $thumb !== null ) {
					$item->setThumbnail($thumb);
				}
				$items->append($item);
			}
				
			
		} else {
			
			foreach ($this->seasons as $key => $seasons) {
				
				$item = new X_Page_Item_PItem($this->getId().'-'.$key, X_Env::_('p_allsp_season_n').": $key");
				$item->setIcon('/images/icons/folder_32.png')
					->setType(X_Page_Item_PItem::TYPE_CONTAINER)
					->setCustom(__CLASS__.':location', $key)
					->setLink(array(
						'action' => 'share',
						'l'	=>	X_Env::encode($key)
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
			$link = new X_Page_Item_PItem('core-directwatch', X_Env::_('p_allsp_watchdirectly'));
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
	 * No support for parent location
	 * @see X_VlcShares_Plugins_ResolverInterface::getParentLocation
	 * @param $location
	 */
	function getParentLocation($location = null) {
		if ( $location == null || $location == '') return false;
	}
	
	
	/**
	 * Add the link for -manage-allsp-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_allsp_mlink'));
		$link->setTitle(X_Env::_('p_allsp_managetitle'))
			->setIcon('/images/allsp/logo.png')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'allsp'
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
