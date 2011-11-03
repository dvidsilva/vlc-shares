<?php

require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'Zend/Dom/Query.php';


/**
 * Add Jigoku site as a video source
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Jigoku extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const VERSION = '0.1.4';
	const VERSION_CLEAN = '0.1.4';
	
	public function __construct() {
		$this->setPriority('gen_beforeInit')
			->setPriority('getCollectionsItems')
			->setPriority('preRegisterVlcArgs')
			->setPriority('getShareItems')
			->setPriority('preGetModeItems')
			->setPriority('getIndexManageLinks')
			;
	}
	
	/**
	 * Inizialize translator for this plugin
	 * @param Zend_Controller_Action $controller
	 */
	function gen_beforeInit(Zend_Controller_Action $controller) {
		$this->helpers()->language()->addTranslation(__CLASS__);
	}
	
	/**
	 * Add the main link for jigoku
	 * @param Zend_Controller_Action $controller
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");
		
		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_jigoku_collectionindex'));
		$link->setIcon('/images/jigoku/logo.jpg')
			->setDescription(X_Env::_('p_jigoku_collectionindex_desc'))
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
		
		//$baseUrl = $this->config('base.url', 'http://www.jigoku.net/mediacenter/index.php?page=ajax_show_folder&id=');
		
		$items = new X_Page_ItemList_PItem();
		
		X_Debug::i("Requested location: $location");
		
		$split = $location != '' ? @explode('/', $location, 4) : array();
		@list($letter, $thread, $video) = $split;
		
		X_Debug::i("Exploded location: ".var_export($split, true));
		
		switch ( count($split) ) {
			// i should not be here, so i fallback to video case
			case 3:
			// show the list of video in the page
			case 2:
				$this->_fetchVideos($items, $letter, $thread);
				break;
			// fetch the list of anime in group
			case 1:
				$this->_fetchThreads($items, $letter);
				break;
			// fetch the list of groups
			default:
				$this->_fetchClassification($items);
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
			$link = new X_Page_Item_PItem('core-directwatch', X_Env::_('p_jigoku_watchdirectly'));
			$link->setIcon('/images/icons/play.png')
				->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
				->setLink($url);
			return new X_Page_ItemList_PItem(array($link));
		} else {
			// if there is no link, i have to remove start-vlc button
			// and replace it with a Warning button
			
			X_Debug::i('Setting priority to filterModeItems');
			$this->setPriority('filterModeItems', 99);
			
			$link = new X_Page_Item_PItem('megavideo-warning', X_Env::_('p_jigoku_invalidlink'));
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
	
	
	private $cachedLocation = array();
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::resolveLocation
	 * @param string $location
	 * @return string real address of a resource
	 */
	function resolveLocation($location = null) {
		if ( $location == '' || $location == null ) return false;
		
		if ( array_key_exists($location, $this->cachedLocation) ) {
			return $this->cachedLocation[$location];
		}
		
		X_Debug::i("Requested location: $location");
		
		@list($letter, $thread, $href) = explode('/', $location, 3);

		X_Debug::i("Letter: $letter, Thread: $thread, Ep: $href");
		
		if ( $href == null || $thread == null ) {
			$this->cachedLocation[$location] = false;
			return false;	
		}
		
		@list($epId, $epName) = explode(':', $href, 2);
		
		
		// i have to fetch the streaming page :(
		
		$baseUrl = $this->config('base.url', 'http://www.jigoku.it/anime-streaming/');
		$baseUrl .= "$thread/$epId/$epName";
		$htmlString = $this->_loadPage($baseUrl, true);
		$dom = new Zend_Dom_Query($htmlString);
		
		$results = $dom->queryXpath('//object[@id="oplayer"]//param[@name="movie"]/attribute::value');
		
		X_Debug::i("Videos found: ".$results->count());
		
		$return = false;
		
		if ( $results->count() ) {
			
			$current = $results->current();
			$linkUrl = $current->nodeValue;
			
			X_Debug::i("Link url: {{$linkUrl}}");
			
			if ( strpos($linkUrl, 'megavideo') !== false ) {
			
				@list(, $megavideoID) = explode('/v/', $linkUrl, 2);
				
				X_Debug::i("Megavideo ID: $megavideoID");
				
				try {
					$return = $this->helpers()->hoster()->findHoster($linkUrl)->getPlayable($megavideoID, true);
				} catch (Exception $e) {
					X_Debug::e($e->getMessage());
				}
				
			} elseif ( $linkUrl == "/js/mediaplayer/player.swf" ) {
				// direct link, nice
				
				X_Debug::i("Direct link mode");
				
				$navCurrent = $current->parentNode->nextSibling;
				
				while ( $navCurrent != null ) {
					
					/* @var $navCurrent DOMElement */
					//  no text nodes or comments
					if ( $navCurrent instanceof DOMElement ) {
						if ( ((string) $navCurrent->nodeName) == "param" ) {
							if ( $navCurrent->hasAttribute('name') && $navCurrent->getAttribute("name") == "flashvars" ) {
								$flashvars = $navCurrent->getAttribute("value");
								$parsed = array();
								parse_str($flashvars, $parsed);
								X_Debug::i("Parsed string: ".var_export($parsed, true));
								
								$return = $parsed['file'];
								
								break;
							}
						}
					}
						
					$navCurrent = $navCurrent->nextSibling;
				}
				
			}
		}
		
		$this->cachedLocation[$location] = $return;
		return $return;
	}
	
	/**
	 * Support for parent location
	 * @see X_VlcShares_Plugins_ResolverInterface::getParentLocation
	 * @param $location
	 */
	function getParentLocation($location = null) {
		if ( $location == '' || $location == null ) return false;
		
		//X_Debug::i($location);
		
		$exploded = explode('/', $location);
		
		//X_Debug::i(var_export($exploded, true));
		
		array_pop($exploded);
		
		//X_Debug::i(var_export($exploded, true));
		
		if ( count($exploded) >= 1 ) {
			return implode('/', $exploded);
		} else {
			return null;
		}			
		
		
		/*
		if ( $href != null ) {
			return $path;
		} else {
			$lStack = explode(':',$path);
			if ( count($lStack) > 1 ) {
				array_pop($lStack);
				return implode(':', $lStack);
			} else {
				return null;
			}
		}
		*/
	}
	
	
	/**
	 * Add the link for -manage-megavideo-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_jigoku_mlink'));
		$link->setTitle(X_Env::_('p_jigoku_managetitle'))
			->setIcon('/images/jigoku/logo.jpg')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'jigoku'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}
	
	private function _fetchClassification(X_Page_ItemList_PItem $items) {
		
		$lets = 'ultimi-episodi,0-9,a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z';
		$lets = explode(',', $lets);
		
		foreach ( $lets as $l ) {
			$item = new X_Page_Item_PItem($this->getId()."-$l", strtoupper($l));
			if ( $l == "ultimi-episodi" ) {
				// translate ultimi-episodi... special case
				$item->setLabel(X_Env::_('p_jigoku_lastupdates'));
			}
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$l")
				->setLink(array(
					'l'	=>	X_Env::encode("$l")
				), 'default', false);
				
			$items->append($item);
		}
	}
	
	
	private function _fetchLastUpdates(X_Page_ItemList_PItem $items, $letter) {
		
		$indexUrl = $this->config('base.url', 'http://www.jigoku.it/anime-streaming/');
		$indexUrl .= "$letter/";
		$htmlString = $this->_loadPage($indexUrl);
		$dom = new Zend_Dom_Query($htmlString);
		
		$results = $dom->queryXpath('//div[@class="elenco_lista"]//a');
		
		X_Debug::i("Threads found: ".$results->count());
		
		for ( $i = 0; $i < $results->count(); $i++, $results->next()) {
		
			$current = $results->current();
			 
			$label = $current->textContent;
		
			$href = $current->getAttribute('href');
			// href has format: /anime-streaming/$NEEDEDVALUE/
			// so i trim / from the bounds and then i get the $NEEDEDVALUE
			@list(,$href) = explode('/', trim($href, '/'));
			
			// WARNING: base64 encoding of "d.php?" expose / char <.< 
			
			$item = new X_Page_Item_PItem($this->getId()."-$label", $label);
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$letter/$href")
				->setLink(array(
					'l'	=>	X_Env::encode("$letter/$href")
				), 'default', false);
				
			if ( APPLICATION_ENV == 'development' ) {
				$item->setDescription("$letter/$href");
			}
				
			$items->append($item);
			
			
		}
		
	}
	
	private function _fetchThreads(X_Page_ItemList_PItem $items, $letter) {
		
		X_Debug::i("Fetching threads for $letter");

		if ( $letter == 'ultimi-episodi' ) {
			return $this->_fetchLastUpdates($items, $letter);
		}
		
		
		$indexUrl = $this->config('base.url', 'http://www.jigoku.it/anime-streaming/');
		$htmlString = $this->_loadPage($indexUrl);
		$dom = new Zend_Dom_Query($htmlString);
		
		$tLetter = $letter;
		if ( $tLetter == '0-9' ) {
			$tLetter = "s$tLetter";
		}
		
		// fetch all threads inside the table
		$results = $dom->queryXpath('//table[@class="streaming_elenco"]//td[@id="' . $tLetter . '"][@class="lettera"][text()!=""]/ancestor::table[@class="streaming_elenco"]//tr[position() > 1]//td[@class="serie"]/a');
		
		X_Debug::i("Threads found: ".$results->count());
		
		for ( $i = 0; $i < $results->count(); $i++, $results->next()) {
		
			$current = $results->current();
			 
			$label = $current->textContent;
			

			$href = $current->getAttribute('href');
			// href has format: /anime-streaming/$NEEDEDVALUE/
			// so i trim / from the bounds and then i get the $NEEDEDVALUE
			@list(,$href) = explode('/', trim($href, '/'));
			
			// WARNING: base64 encoding of "d.php?" expose / char <.< 
			
			$item = new X_Page_Item_PItem($this->getId()."-$label", $label);
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$letter/$href")
				->setLink(array(
					'l'	=>	X_Env::encode("$letter/$href")
				), 'default', false);
				
			if ( APPLICATION_ENV == 'development' ) {
				$item->setDescription("$letter/$href");
			}
				
			$items->append($item);
			
		}
		
	}	
	
	
	private function _fetchVideos(X_Page_ItemList_PItem $items, $letter, $thread) {
		
		X_Debug::i("Fetching videos for $letter/$thread");
		
		$baseUrl = $this->config('base.url', 'http://www.jigoku.it/anime-streaming/');
		$baseUrl .= "$thread";
		$htmlString = $this->_loadPage($baseUrl, true);
		$dom = new Zend_Dom_Query($htmlString);
		
		// xpath index stars from 1
		$results = $dom->queryXpath('//div[@class="elenco"]//td[@class="serie"]/a');
		
		X_Debug::i("Links found: ".$results->count());
		
		//$found = false;
		
		for ( $i = 0; $i < $results->count(); $i++, $results->next()) {
		
			$current = $results->current(); 
			
			
			$label = trim(trim($current->textContent), chr(0xC2).chr(0xA0));
			if ( $label == '') {
				$label = X_Env::_('p_jigoku_nonamevideo');
			}
			$href = $current->getAttribute('href');
			// href format: /anime-streaming/b-gata-h-kei/8306/episodio-2/
			@list(, , $epId,$epName) = explode('/', trim($href, '/'));
			// works even without the $epName
			$href="$epId:$epName";
			
			//$found = true;
			
			X_Debug::i("Valid link found: $href");
			
			$item = new X_Page_Item_PItem($this->getId()."-$label", $label);
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setCustom(__CLASS__.':location', "$letter/$thread/$href")
				->setLink(array(
					'action'	=> 'mode',
					'l'	=>	X_Env::encode("$letter/$thread/$href")
				), 'default', false);
				
			if ( APPLICATION_ENV == 'development' ) {
				$item->setDescription("$letter/$thread/$href");
			}
				
			$items->append($item);
			
		}
		/*
		if (!$found) {
			$item = new X_Page_Item_PItem($this->getId().'-ops', X_Env::_('p_animedb_opsnovideo'));
			$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(X_Env::completeUrl(
					//$urlHelper->url()
				));
			$items->append($item);
		}
		*/
	}
	
	
	private function _loadPage($uri) {

		X_Debug::i("Loading page $uri");
		
		$http = new Zend_Http_Client($uri, array(
			'maxredirects'	=> $this->config('request.maxredirects', 10),
			'timeout'		=> $this->config('request.timeout', 10),
			'keepalive' => true
		));
		
		$http->setHeaders(array(
			$this->config('hide.useragent', false) ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1',
			//'Content-Type: application/x-www-form-urlencoded'
		));
		
		$response = $http->request();
		$htmlString = $response->getBody();
		
		return $htmlString;
	}
	
}
