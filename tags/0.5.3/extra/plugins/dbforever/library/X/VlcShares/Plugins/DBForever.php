<?php

require_once 'X/VlcShares/Plugins/Abstract.php';


/**
 * Add DBForever.org site as a video source
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_DBForever extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const VERSION = '0.2';
	const VERSION_CLEAN = '0.2';
	
	const INDEX_NARUTO = 'strm_naruto';
	const INDEX_ONEPIECE = 'strm_onepiece';
	const INDEX_BLEACH = 'strm_bleach';
	
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

		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_dbforever_collectionindex'));
		$link->setIcon('/images/dbforever/logo.png')
			->setDescription(X_Env::_('p_dbforever_collectionindex_desc'))
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
		
		$urlHelper = $controller->getHelper('url');
		
		$items = new X_Page_ItemList_PItem();
		
		if ( $location != '' && ( $location == self::INDEX_NARUTO || $location == self::INDEX_ONEPIECE || $location == self::INDEX_BLEACH   ) ) {
			
			
			$pageIndex = $this->config('index.url', 'http://www.dbforever.net/home.php')."?page=$location";
			
			$htmlString = $this->_loadPage($pageIndex);
			
			$dom = new Zend_Dom_Query($htmlString);
			
			$results = $dom->queryXpath('//div[@align="left"]/a');
			
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
			
		} else {

			$item = new X_Page_Item_PItem($this->getId().'-'.self::INDEX_NARUTO, X_Env::_('p_dbforever_naruto_ep'));
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', self::INDEX_NARUTO)
				->setThumbnail('http://www.dbforever.net/img/banner/naruto_banner_grande.jpg')
				->setLink(array(
					'l'	=>	X_Env::encode(self::INDEX_NARUTO)
				), 'default', false);
			$items->append($item);

			$item = new X_Page_Item_PItem($this->getId().'-'.self::INDEX_ONEPIECE, X_Env::_('p_dbforever_onepiece_ep'));
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', self::INDEX_ONEPIECE)
				->setThumbnail('http://www.dbforever.net/img/banner/onepiece_banner_grande.jpg')
				->setLink(array(
					'l'	=>	X_Env::encode(self::INDEX_ONEPIECE)
				), 'default', false);
			$items->append($item);
			
			$item = new X_Page_Item_PItem($this->getId().'-'.self::INDEX_BLEACH, X_Env::_('p_dbforever_bleach_ep'));
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', self::INDEX_BLEACH)
				->setThumbnail('http://www.dbforever.net/img/banner/bleach_banner_grande.jpg')
				->setLink(array(
					'l'	=>	X_Env::encode(self::INDEX_BLEACH)
				), 'default', false);
			$items->append($item);
			
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
			$link = new X_Page_Item_PItem('core-directwatch', X_Env::_('p_dbforever_watchdirectly'));
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
			'maxredirects'	=> $this->config('request.maxredirects', 0),
			'timeout'		=> $this->config('request.timeout', 10)
		));
		
		$response = $http->request();
		$htmlString = $response->getBody();
		return $htmlString;
	}
	
}
