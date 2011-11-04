<?php

/**
 * Add some live channels from spanish radio
 * @author Miguel Angel Pescador Santirso
 *
 */
class X_VlcShares_Plugins_Spainradio extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const VERSION = '0.2';
	const VERSION_CLEAN = '0.2';

	const C_40 = 'http://194.169.201.177:8085/live3.mp3';
	const C_C100 = 'http://cadena100.cope.stream.flumotion.com/cope/cadena100.asf.asx';
	const C_CadenaDial = 'http://194.169.201.177:8085/liveDial.mp3';
	const C_M80 = 'http://194.169.201.177:8085/liveM80.mp3';
	const C_EuropaFM = 'http://stream.europafmgipuzkoa.com/" ';
	const C_RNE1 = 'http://radio1.rtve.stream.flumotion.com/rtve/radio1.mp3.m3u';
	const C_RNE3 = 'http://radio1.rtve.stream.flumotion.com/rtve/radio3.mp3.m3u';
	const C_RNE4 = 'http://radio1.rtve.stream.flumotion.com/rtve/radio4.mp3.m3u';
	const C_RNE5 = 'http://radio1.rtve.stream.flumotion.com/rtve/radio5.mp3.m3u';
	const C_RNEClasica = 'http://radioclasica.rtve.stream.flumotion.com/rtve/radioclasica.mp3.m3u';
	const C_RNEExterior = 'http://radioexterior.rtve.stream.flumotion.com/rtve/radioexterior.mp3.m3u';

	protected $channels = array(
		'Los 40 Principales' => '40',
		'Cadena 100' => 'C100',
		'Cadena Dial' => 'CadenaDial',
		'M80 Radio' => 'M80',
		'Europa FM' => 'EuropaFM',
		'RNE Radio 1' => 'RNE1',
		'RNE Radio 3' => 'RNE3',
		'RNE Radio 4' => 'RNE4',
		'RNE Radio 5' => 'RNE5',
		'RNE Clasica' => 'RNEClasica',
		'RNE Exterior' => 'RNEExterior',
	);
	
	
	/**
	 * @var Zend_Http_CookieJar
	 */
	private $jar = null;
	
	
	public function __construct() {
		$this->setPriority('gen_beforeInit')
			->setPriority('getCollectionsItems')
			->setPriority('preRegisterVlcArgs')
			->setPriority('getShareItems')
			//->setPriority('preGetModeItems')
			->setPriority('getIndexManageLinks')
			//->setPriority('getIndexMessages')
			//->setPriority('getTestItems')
			//->setPriority('prepareConfigElement')
			;
	}
	
	/**
	 * Inizialize translator for this plugin
	 * @param Zend_Controller_Action $controller
	 */
	function gen_beforeInit(Zend_Controller_Action $controller) {
		$this->helpers()->language()->addTranslation(__CLASS__);
		// redirect support in wiimc exists only from 1.1.0
		if ( $this->helpers()->devices()->isWiimc() && !X_VlcShares_Plugins::helpers()->devices()->isWiimcBeforeVersion('1.0.9') && $this->config('direct.enabled', true) ) {
			$this->setPriority('preGetModeItems');
		}
		
	}
	
	/**
	 * Add the link for -manage-spainradio-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_spainradio_mlink'));
		$link->setTitle(X_Env::_('p_spainradio_managetitle'))
			->setIcon('/images/spainradio/logo.jpg')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'spainradio'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}
	
	/**
	 * Add the main link for spainradio
	 * @param Zend_Controller_Action $controller
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");
		
		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_spainradio_collectionindex'));
		$link->setIcon('/images/spainradio/logo.jpg')
			->setDescription(X_Env::_('p_spainradio_collectionindex_desc'))
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
		
		if ( $provider != $this->getId() ) return;
		
		X_Debug::i('Plugin triggered');

		X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_SortItems');
		
		if ( X_VlcShares_Plugins::broker()->isRegistered('cache') ) {
			$cachePlugin = X_VlcShares_Plugins::broker()->getPlugins('cache');
			if ( method_exists($cachePlugin, 'setDoNotCache') ) {
				$cachePlugin->setDoNotCache();
			}
		}
		
		$items = new X_Page_ItemList_PItem();

		foreach ($this->channels as $name => $url) {
			
			$item = new X_Page_Item_PItem($this->getId()."-$url", $name);
			$item->setIcon('/images/icons/file_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', $url)
				->setLink(array(
					'l'	=>	X_Env::encode($url),
					'action' => 'mode',
				), 'default', false)
				->setThumbnail('/vlc-shares/public/images/spainradio/'.$url.'.png');//new a thumbnail.
				
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
		
		$location = $this->Location($location);
		
		if ( $location !== null ) {
			// TODO adapt to newer api when ready
			$vlc->registerArg('source', "\"$location\"");			
		} else {
			X_Debug::e("No source o_O");
		}
	
	}
	
	/**
	 *	Add button -watch spainradio stream directly-
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
			$link = new X_Page_Item_PItem('core-directwatch', X_Env::_('p_spainradio_watchdirectly'));
			$link->setIcon('/images/icons/play.png')
				->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
				->setLink($url);
			return new X_Page_ItemList_PItem(array($link));
		} else {
			// if there is no link, i have to remove start-vlc button
			// and replace it with a Warning button
			
			X_Debug::i('Setting priority to filterModeItems');
			$this->setPriority('filterModeItems', 99);
			
			$link = new X_Page_Item_PItem('megavideo-warning', X_Env::_('p_spainradio_invalidlink'));
			$link->setIcon('/images/msg_error.png')
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(array (
					'controller' => 'browse',
					'action' => 'share',
					'p'	=> $this->getId(),
					'l' => X_Env::encode($this->getParentLocation($location)),
				), 'default', true);
			return new X_Page_ItemList_PItem(array($link));

		}
	}
	
	/**
	 * Remove vlc-play button if location is invalid
	 * @param X_Page_Item_PItem $item,
	 * @param string $provider
	 * @param Zend_Controller_Action $controller
	 */
	public function filterModeItems(X_Page_Item_PItem $item, $provider,Zend_Controller_Action $controller) {
		if ( $item->getKey() == 'core-play') {
			X_Debug::i('plugin triggered');
			X_Debug::w('core-play flagged as invalid because the link is invalid');
			return false;
		}
	}
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::resolveLocation
	 * @param string $location
	 * @return string real address of a resource
	 */
	function resolveLocation($location = null) {
		if ( $location == '' || $location == null ) return false;
					
		// if it isn't in the channels array it's an invalid location
		if ( !array_search($location, $this->channels) ) return null; 
		
		// X_Env::routeLink should be deprecated, but now is the best option
		$linkUrl = X_Env::routeLink('spainradio','proxy', array(
			'v' => X_Env::encode($location),
			//'r' => X_Env::encode($baseUrl) // baseUrl is the page containing the link
		));
		
		$return = $linkUrl;
				
 		return $return;
	}
	
	/**
	 * Support for parent location
	 * @see X_VlcShares_Plugins_ResolverInterface::getParentLocation
	 * @param $location
	 */
	function getParentLocation($location = null) {
		if ( $location == '' || $location == null ) return false;
		
		return null;
		
	}
	
}
