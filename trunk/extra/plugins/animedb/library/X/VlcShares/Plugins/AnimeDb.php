<?php

require_once 'Zend/Http/CookieJar.php';
require_once 'Zend/Http/Cookie.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'Zend/Dom/Query.php';


/**
 * Add AnimeDB.tv site as a video source
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_AnimeDb extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const VERSION = '0.2.1';
	const VERSION_CLEAN = '0.2.1';
	
	/**
	 * @var Zend_Http_CookieJar
	 */
	private $jar = null;
	
	public function __construct() {
		$this->setPriority('gen_beforeInit')
			->setPriority('getCollectionsItems')
			->setPriority('preRegisterVlcArgs')
			->setPriority('getShareItems')
			->setPriority('preGetModeItems')
			->setPriority('getIndexManageLinks')
			->setPriority('getIndexMessages')
			->setPriority('getTestItems')
			->setPriority('prepareConfigElement');
	}
	
	/**
	 * Inizialize translator for this plugin
	 * @param Zend_Controller_Action $controller
	 */
	function gen_beforeInit(Zend_Controller_Action $controller) {
		$this->helpers()->language()->addTranslation(__CLASS__);
	}
	
	/**
	 * Add the main link for animedb library
	 * @param Zend_Controller_Action $controller
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");
		
		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_animedb_collectionindex'));
		$link->setIcon('/images/animedb/logo.png')
			->setDescription(X_Env::_('p_animedb_collectionindex_desc'))
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
		
		//try to disable SortItems plugin, so link are listed as in html page
		X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_SortItems');
		
		X_Debug::i("Requested location: $location");
		
		$split = $location != '' ? @explode('/', $location, 4) : array();
		
		X_Debug::i("Exploded location: ".var_export($split, true));
		
		switch ( count($split) ) {
			// i should not be here, so i fallback to video case
			case 4:
			// show the list of video in the page
			// here authentication is required if it's possibile
			case 3:
				$this->_fetchVideos($items, $split[0], $split[1], $split[2]);
				break;
			// show the list of thread in category by letter
			case 2:
				$this->_fetchThreads($items, $split[0], $split[1]);
				break;
			// show the list of A-B-C.... if an area is selected
			case 1:
				$this->_fetchClassification($items, $split[0]);
				break;
			default:
				$this->_fetchLists($items);
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
			$link = new X_Page_Item_PItem('core-directwatch', X_Env::_('p_animedb_watchdirectly'));
			$link->setIcon('/images/icons/play.png')
				->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
				->setLink($url);
			return new X_Page_ItemList_PItem(array($link));
		} else {
			// if there is no link, i have to remove start-vlc button
			// and replace it with a Warning button
			
			X_Debug::i('Setting priority to filterModeItems');
			$this->setPriority('filterModeItems', 99);
			
			$link = new X_Page_Item_PItem('megavideo-warning', X_Env::_('p_animedb_invalidmegavideo'));
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
		X_Debug::i('plugin triggered');
		if ( $item->getKey() == 'core-play') {
			X_Debug::w('core-play flagged as invalid because megavideo link is invalid');
			return false;
		}
	}
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::resolveLocation
	 * @param string $location
	 * @return string real address of a resource
	 */
	function resolveLocation($location = null) {
		if ( $location == null ) return false;
		$split = $location != '' ? @explode('/', $location, 4) : array();
		if ( count($split) == 4 ) {
			// $split[3] have a resource hoster:id			
			$href = $split[3];

			// new hoster api
			
			@list($hoster, $videoId) = explode(':', $href, 2);
			
			try {
				return $this->helpers()->hoster()->getHoster($hoster)->getPlayable($videoId);
			} catch (Exception $e) {
				X_Debug::e("Invalid hoster {{$hoster}}");
			}
			
		}
		
		return false;
	}
	
	/**
	 * No support for parent location
	 * @see X_VlcShares_Plugins_ResolverInterface::getParentLocation
	 * @param $location
	 */
	function getParentLocation($location = null) {
		if ($location == null || $location == '') return false;
		$split = @explode('/', $location, 4);
		$return = false;
		if ( count($split) == 1 ) {
			return false;
		} else {
			array_pop($split);
			return implode('/', $split);
		}
	}
	
	
	/**
	 * Add the link for -manage-megavideo-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_animedb_mlink'));
		$link->setTitle(X_Env::_('p_animedb_managetitle'))
			->setIcon('/images/animedb/logo.png')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'animedb'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}
	
	/**
	 * Show a warning message if username and password aren't specified yet
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Message
	 */
	public function getIndexMessages(Zend_Controller_Action $controller) {
		
		X_Debug::i('Plugin triggered');
		
		if ( $this->config('auth.username', '') == '' || $this->config('auth.password', '') == '' ) {
			$m = new X_Page_Item_Message($this->getId(), X_Env::_('p_animedb_dashboardwarning'));
			$m->setType(X_Page_Item_Message::TYPE_WARNING);
			return new X_Page_ItemList_Message(array($m));
		}
		
	}
	
	/**
	 * Animedb tests:
	 * 	- check for username & password
	 *  - check for data/animedb/ path writable
	 * @param Zend_Config $options
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_Message
	 */
	public function getTestItems(Zend_Config $options,Zend_Controller_Action $controller) {
		
		$tests = new X_Page_ItemList_Test(); 
		$test = new X_Page_Item_Test($this->getId().'-writeaccess', '[AnimeDb] Checking for write access to /data/animedb/ folder');
		if ( is_writable(APPLICATION_PATH . '/../data/animedb/') ) {
			$test->setType(X_Page_Item_Message::TYPE_INFO);
			$test->setReason('Write access granted');
		} else {
			$test->setType(X_Page_Item_Message::TYPE_WARNING);
			$test->setReason("CookieJar file can't be stored, items fetch will be really slow");
		}
		$tests->append($test);
		
		$test = new X_Page_Item_Test($this->getId().'-credentials', '[AnimeDb] Checking for authentication credentials');
		if ( $this->config('auth.username', '') != '' && $this->config('auth.password', '') != '' ) {
			$test->setType(X_Page_Item_Message::TYPE_INFO);
			$test->setReason('Credentials configurated');
		} else {
			$test->setType(X_Page_Item_Message::TYPE_WARNING);
			$test->setReason("Credentials not configurated. Some contents could be not viewed");
		}
		$tests->append($test);
		
		return $tests;
	}
	
	
	/**
	 * Remove cookie.jar if configs change and convert form password to password element
	 * @param string $section
	 * @param string $namespace
	 * @param unknown_type $key
	 * @param Zend_Form_Element $element
	 * @param Zend_Form $form
	 * @param Zend_Controller_Action $controller
	 */
	public function prepareConfigElement($section, $namespace, $key, Zend_Form_Element $element, Zend_Form  $form, Zend_Controller_Action $controller) {
		// nothing to do if this isn't the right section
		if ( $namespace != $this->getId() ) return;
		
		switch ($key) {
			// i have to convert it to a password element
			case 'plugins_animedb_auth_password':
				$password = $form->createElement('password', 'plugins_animedb_auth_password', array(
					'label' => $element->getLabel(),
					'description' => $element->getDescription(),
					'renderPassword' => true,
				));
				$form->plugins_animedb_auth_password = $password;
				break;
		}
		
		// remove cookie.jar if somethings has value
		if ( !$form->isErrors() && !is_null($element->getValue()) && file_exists(APPLICATION_PATH . '/../data/animedb/cookie.jar') ) {
			if ( @!unlink(APPLICATION_PATH . '/../data/animedb/cookie.jar') ) {
				X_Debug::e("Error removing cookie.jar");
			}
		}
	}	
	
		
	
	
	/**
	 * Fetch the list page and add items to the list
	 * @param X_Page_ItemList_PItem $items
	 */
	private function _fetchLists(X_Page_ItemList_PItem $items) {
		
		X_Debug::i("Fetching lists");
		
		$pageIndex = $this->config('index.url', 'http://animedb.tv/forum/liste.php');
		$htmlString = $this->_loadPage($pageIndex);
		$dom = new Zend_Dom_Query($htmlString);
		
		// xpath index stars from 1
		$results = $dom->queryXpath('//div[@class="forumbits"][1]//div[@class="forumrow"]//h2[@class="forumtitle"]//a');
		
		X_Debug::i("Lists found: ".$results->count());
		
		for ( $i = 0; $i < $results->count(); $i++, $results->next()) {
		
			$current = $results->current(); 
			$label = trim(trim($current->nodeValue), chr(0xC2).chr(0xA0));
			$href = $current->getAttribute('href');
			
			$item = new X_Page_Item_PItem($this->getId()."-$label", $label);
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', $href)
				->setLink(array(
					'l'	=>	X_Env::encode($href)
				), 'default', false);
				
			if ( APPLICATION_ENV == 'development' ) {
				$item->setDescription($href);
			}
				
			$items->append($item);
			
		}
		
	}
	
	private function _fetchClassification(X_Page_ItemList_PItem $items, $category) {
		
		$lets = 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z,09,EXTRA';
		$lets = explode(',', $lets);
		
		foreach ( $lets as $l ) {
			$item = new X_Page_Item_PItem($this->getId()."-$l", $l);
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$category/$l")
				->setLink(array(
					'l'	=>	X_Env::encode("$category/$l")
				), 'default', false);
				
			if ( APPLICATION_ENV == 'development' ) {
				$item->setDescription("$category/$l");
			}
				
			$items->append($item);
		}
	}
	
	private function _fetchThreads(X_Page_ItemList_PItem $items, $category, $letter) {
		
		X_Debug::i("Fetching threads for $category/$letter");
		
		$baseUrl = $this->config('base.url', 'http://animedb.tv/forum/');
		$baseUrl .= "$category&let=$letter";
		$htmlString = $this->_loadPage($baseUrl);
		$dom = new Zend_Dom_Query($htmlString);
		
		// xpath index stars from 1
		$results = $dom->queryXpath('//div[@id="threadlist"]//ol//h3[@class="threadtitle"]//a[@class="title"]');
		
		X_Debug::i("Threads found: ".$results->count());
		
		for ( $i = 0; $i < $results->count(); $i++, $results->next()) {
		
			$current = $results->current(); 
			$label = trim(trim($current->nodeValue), chr(0xC2).chr(0xA0));
			$href = $current->getAttribute('href');
			
			// WARNING: base64 encoding of "d.php?" expose / char <.< 
			
			$item = new X_Page_Item_PItem($this->getId()."-$label", $label);
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$category/$letter/$href")
				->setLink(array(
					'l'	=>	X_Env::encode("$category/$letter/$href")
				), 'default', false);
				
			if ( APPLICATION_ENV == 'development' ) {
				$item->setDescription("$category/$letter/$href");
			}
				
			$items->append($item);
			
		}
		
	}
	
	
	private function _fetchVideos(X_Page_ItemList_PItem $items, $category, $letter, $thread) {
		
		X_Debug::i("Fetching videos for $category/$letter/$thread");
		
		$baseUrl = $this->config('base.url', 'http://animedb.tv/forum/');
		$baseUrl .= "$thread";
		$htmlString = $this->_loadPage($baseUrl, true);
		

		if ( $this->config('scraper.alternative.enabled', false) ) {
			
			X_Debug::w("Using alternative scaper");
			
			// From a patch submitted by Valerio Moretti
			$this->_parseAlternativeScaper($htmlString, $items, $category, $letter, $thread);
			// all items should be in the list. It's useless to continue
			return;
		}
		
		
		
		$dom = new Zend_Dom_Query($htmlString);
		
		// xpath index stars from 1
		$results = $dom->queryXpath('//ol[@id="posts"]/li[1]//div[@class="content"]//a');
		
		X_Debug::i("Links found: ".$results->count());
		
		$found = false;
		
		for ( $i = 0; $i < $results->count(); $i++, $results->next()) {
		
			$current = $results->current(); 
			
			$label = trim(trim($current->nodeValue), chr(0xC2).chr(0xA0));
			if ( $label == '') {
				$label = X_Env::_('p_animedb_nonamevideo');
			}
			$href = $current->getAttribute('href');
			
			try {
				$hoster = $this->helpers()->hoster()->findHoster($href);
			} catch ( Exception $e) {
				// no hoster = no valid href
				continue;
			}
			
			$label .= " [".ucfirst($hoster->getId())."]";
			$href = "{$hoster->getId()}:{$hoster->getResourceId($href)}";
			
			$found = true;
			
			X_Debug::i("Valid link found: $href");
			
			$item = new X_Page_Item_PItem($this->getId()."-$label", $label);
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setCustom(__CLASS__.':location', "$category/$letter/$thread/$href")
				->setLink(array(
					'action'	=> 'mode',
					'l'	=>	X_Env::encode("$category/$letter/$thread/$href")
				), 'default', false);
				
			if ( APPLICATION_ENV == 'development' ) {
				$item->setDescription("$category/$letter/$thread/$href");
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
	
	/**
	 * Find links using an alternative way
	 * @author Valerio Moretti
	 */
	private function _parseAlternativeScaper($htmlString, X_Page_ItemList_PItem $items, $category, $letter, $thread) {
		
		//mi suddivido il post in righe
		$htmlRows = explode('<br />', $htmlString);
		
		/*
		//qua volevo selezionarmi solo il codice del post per rendere il tutto piu' veloce
		//pero' sbaglio qualcosa e non mi funziona
		
		$dom = new Zend_Dom_Query($htmlString);
		$content = $dom->queryXpath('//ol[@id="posts"]/li[1]//div[@class="content"]');
		$htmlPost = $content->current();
		
		$htmlRows = explode('<br />', $htmlPost);
		*/

		//analizzo riga per riga
		foreach($htmlRows as $row)
		{
			
			//la mia ignoranza del Xpath e' immensa e questo e' l'unico modo che ho trovato per farlo funzionare
			$row = '<root>'.$row.'</root>';
			
			$dom = new Zend_Dom_Query($row);
			
			// xpath index stars from 1
			
			
			$results = $dom->queryXpath('//a');
			
			X_Debug::i("Links found: ".$results->count());
			
			$found = false;
			$title = ''; //il testo del primo link della riga
			
			for ( $i = 0; $i < $results->count(); $i++, $results->next()) {
			
				$current = $results->current(); 
				$label = trim(trim($current->nodeValue), chr(0xC2).chr(0xA0));

				if ( $label == '') {
					$label = X_Env::_('p_animedb_nonamevideo');
				}
				if ($title == '') {
					$title = $label;
				}
				$href = $current->getAttribute('href');
				
				try {
					$hoster = $this->helpers()->hoster()->findHoster($href);
				} catch ( Exception $e) {
					// no hoster = no valid href
					continue;
				}
				
				$href = "{$hoster->getId()}:{$hoster->getResourceId($href)}";
									
				$label = $label == $title ? $label : $title.' | '.$label;

				$label .= " [".ucfirst($hoster->getId())."]";				
				
				$found = true;
				
				X_Debug::i("Valid link found: $href");
				
				$item = new X_Page_Item_PItem($this->getId()."-$label", $label);
				$item->setIcon('/images/icons/folder_32.png')
					->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setCustom(__CLASS__.':location', "$category/$letter/$thread/$href")
					->setLink(array(
						'action'	=> 'mode',
						'l'	=>	X_Env::encode("$category/$letter/$thread/$href")
					), 'default', false);
					
				if ( APPLICATION_ENV == 'development' ) {
					$item->setDescription("$category/$letter/$thread/$href");
				}
					
				$items->append($item);
				
			}
		}		
		
	}
	
	private function _loadPage($uri, $forceAuth = false) {

		X_Debug::i("Loading page $uri");
		
		$http = new Zend_Http_Client($uri, array(
			'maxredirects'	=> $this->config('request.maxredirects', 10),
			'timeout'		=> $this->config('request.timeout', 10),
			'keepalive' => true
		));
		
		$http->setHeaders(array(
			$this->config('hide.useragent', false) ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1'
		    //'Accept-encoding' => 'deflate',
		));
		
		$jarFile = APPLICATION_PATH . '/../data/animedb/cookie.jar';
		$ns = new Zend_Session_Namespace(__CLASS__);
		
		if ( $this->jar == null ) {
			if ( false && isset($ns->jar) && $ns->jar instanceof Zend_Http_CookieJar ) {
				$this->jar = $ns->jar;
				X_Debug::i('Loading stored authentication in Session');
			} elseif ( file_exists($jarFile) ) {
				$this->jar = new Zend_Http_CookieJar();
				$cookies = unserialize(file_get_contents($jarFile));
				foreach ($cookies as $c) {
					$_c = new Zend_Http_Cookie($c['name'], $c['value'], $c['domain'], $c['exp'], $c['path']);
					$this->jar->addCookie($_c);
				}
				X_Debug::i('Loading stored authentication in File');
			}
		}
		
		$http->setCookieJar($this->jar);
		//time to make the request
		
		$response = $http->request();
		$htmlString = $response->getBody();
		//X_Debug::i($htmlString);
		
		// before return the page, I have to check if i'm authenticated
		// TODO REMOVE AUTH
		if ( $forceAuth && $this->config('auth.username', '') != '' && $this->config('auth.password', '') != '' && !$this->_isAuthenticated($htmlString) ) {
			
			X_Debug::i("Autentication needed");
			
			$token = $this->_getSecurityToken($htmlString);
			//$sValue = $this->_getSValue($htmlString);
			
			// do new login
			$http->setCookieJar(true);
			$pageLogin = $this->config('login.url', 'http://animedb.tv/forum/login.php?do=login');
			$http->setUri($pageLogin);
			$http->setParameterPost ( array (
				// TODO REMOVE AUTH
				'vb_login_username' => (string) $this->config('auth.username', ''),
				'vb_login_password' => (string) $this->config('auth.password', ''),
				'vb_login_password_hint' => 'Password',
				'vb_login_md5password' => '',
				'vb_login_md5password_utf' => '',
				'securitytoken'	=> $token,
				'do' => 'login',
				'cookieuser' => 1,
				's'	=> '',
				'x' => 13,
				'y' => 30
			 ) );
				
			// TODO remove this
			if ( APPLICATION_ENV == 'development' ) {
				$response = $http->request(Zend_Http_Client::POST);
				if ( !$this->_isAuthenticated($response->getBody(), '<p class="blockrow restore">Grazie per esserti collegato,'  )) {
					X_Debug::w('Wrong credentials or authentication procedure doesn\'t work');
				} else {
					X_Debug::w('Client authenticated. Full access granted');
				}
				//X_Debug::i($response->getBody());
			} else {
				$http->request(Zend_Http_Client::POST);
			}
			
			$this->jar = $http->getCookieJar();
			// store the cookiejar
			
			$cks = $this->jar->getAllCookies(Zend_Http_CookieJar::COOKIE_OBJECT);
			foreach ($cks as $i => $c) {
				/* @var $c Zend_Http_Cookie */
				$cks[$i] = array(
					'domain' => $c->getDomain(),
					'exp' => $c->getExpiryTime(),
					'name' => $c->getName(),
					'path' => $c->getPath(),
					'value' => $c->getValue()
				);
			} 
			if ( @file_put_contents($jarFile, serialize($cks), LOCK_EX) === false ) {
				X_Debug::e('Error while writing jar file. Check permissions. Everything will work, but much more slower');
			}
			
			//$ns->jar = $this->jar;
			
			// time to do a new old request
			//$http->resetParameters();
			$http->setUri($uri);
			$response = $http->request(Zend_Http_Client::GET);
			$htmlString = $response->getBody();

			//X_Debug::i($htmlString);
			
		}
		
		
		return $htmlString;
	}
	
	/**
	 * Search the html string for pattern that
	 * give info about authentication
	 * @param string $htmlString an animedb page
	 */
	private function _isAuthenticated($htmlString, $pattern = '<li class="welcomelink">') {
		
		//$pattern = '<li class="welcomelink">';
		return ( strpos($htmlString, $pattern) !== false );
		
	}
	
	private function _getSecurityToken($htmlString) {
		
		$pattern = 'var SECURITYTOKEN = "';
		$start = strpos($htmlString, $pattern);
		
		if ( $start === false ) {
			return '';
		}
		$start += strlen($pattern);
		$end = strpos($htmlString, '"', $start);
		$token = substr($htmlString, $start, $end - $start);
		X_Debug::i('Security token: '.$token);
		
		return $token;
		
	}

	private function _getSValue($htmlString) {
		
		$pattern = '<input type="hidden" name="s" value="';
		$start = strpos($htmlString, $pattern);
		
		if ( $start === false ) {
			return '';
		}
		$start += strlen($pattern);
		$end = strpos($htmlString, '"', $start);
		$token = substr($htmlString, $start, $end - $start);
		X_Debug::i('S value: '.$token);
		
		return $token;
		
	}
	
	
}
