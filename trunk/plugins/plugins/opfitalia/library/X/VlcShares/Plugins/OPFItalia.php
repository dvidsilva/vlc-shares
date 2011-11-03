<?php

require_once 'Zend/Http/CookieJar.php';
require_once 'Zend/Http/Cookie.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'Zend/Dom/Query.php';


/**
 * Add OPFItalia site as a video source
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_OPFItalia extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
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
			->setPriority('prepareConfigElement')
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
	 * Add the main link for megavideo library
	 * @param Zend_Controller_Action $controller
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");
		
		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_opfitalia_collectionindex'));
		$link->setIcon('/images/opfitalia/logo.png')
			->setDescription(X_Env::_('p_opfitalia_collectionindex_desc'))
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
		
		$baseUrl = $this->config('base.url', 'http://www.opfitalia.net/mediacenter/index.php?page=ajax_show_folder&id=');
		
		$items = new X_Page_ItemList_PItem();
		
		try {
			if ( $location != '' ) {
				@list($path, $type, $link) = explode('/', $location);
				$lStack = explode(':', $path);
				$last = count($lStack) > 0 ? $lStack[count($lStack)-1] : 0;
				$decoded = $this->_loadPage($baseUrl.$last);
				$this->_fillPlaylist($items, $decoded, $lStack);
			} else {
				// show the index
				$indexCategory = $this->config('index.category', 0);
				$decoded = $this->_loadPage($baseUrl.$indexCategory);
				$this->_fillPlaylist($items, $decoded, array());
			}
		} catch (Exception $e ) {
			// auth problems
			$item = new X_Page_Item_PItem('opf-autherror', X_Env::_('p_opfitalia_br_error').": {$e->getMessage()}");
			$item->setIcon('/images/msg_error.png')
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setDescription($e->getMessage())
				->setLink(array(
					'controller'	=> 'index',
					'action'		=> 'collections'
				), 'default', true);
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
			$link = new X_Page_Item_PItem('core-directwatch', X_Env::_('p_opfitalia_watchdirectly'));
			$link->setIcon('/images/icons/play.png')
				->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
				->setLink($url);
			return new X_Page_ItemList_PItem(array($link));
		} else {
			// if there is no link, i have to remove start-vlc button
			// and replace it with a Warning button
			
			X_Debug::i('Setting priority to filterModeItems');
			$this->setPriority('filterModeItems', 99);
			
			$link = new X_Page_Item_PItem('megavideo-warning', X_Env::_('p_opfitalia_invalidlink'));
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
		
		X_Debug::i("Requested location: $location");
		
		@list($path, $type, $href) = explode('/', $location, 3);

		X_Debug::i("Path: $path, Type: $type, Href: $href");
		
		if ( $href == null || $type == null ) return false;
		
		switch ($type) {
			
			case 'file':
				return $href;
			
			case 'megavideo':
				try {
					/* @var $megavideo X_VlcShares_Plugins_Helper_Megavideo */
					$megavideo = $this->helpers('megavideo');
					// if server isn't specified, there is no video
					if ( $megavideo->setLocation($href)->getServer() ) {
						return $megavideo->getUrl();
					}
				} catch (Exception $e) {
					X_Debug::e($e->getMessage());
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
		if ( $location == '' || $location == null ) return false;
		
		@list($path, $type, $href) = explode('/', $location, 3);
		
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
	}
	
	
	/**
	 * Add the link for -manage-megavideo-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_opfitalia_mlink'));
		$link->setTitle(X_Env::_('p_opfitalia_managetitle'))
			->setIcon('/images/opfitalia/logo.png')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'opfitalia'
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
			$m = new X_Page_Item_Message($this->getId(), X_Env::_('p_opfitalia_dashboardwarning'));
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
		$test = new X_Page_Item_Test($this->getId().'-writeaccess', '[OPFItalia] Checking for write access to /data/opfitalia/ folder');
		if ( is_writable(APPLICATION_PATH . '/../data/opfitalia/') ) {
			$test->setType(X_Page_Item_Message::TYPE_INFO);
			$test->setReason('Write access granted');
		} else {
			$test->setType(X_Page_Item_Message::TYPE_WARNING);
			$test->setReason("CookieJar file can't be stored, items fetch will be really slow");
		}
		$tests->append($test);
		
		$test = new X_Page_Item_Test($this->getId().'-credentials', '[OPFItalia] Checking for authentication credentials');
		if ( $this->config('auth.username', '') != '' && $this->config('auth.password', '') != '' ) {
			$test->setType(X_Page_Item_Message::TYPE_INFO);
			$test->setReason('Credentials configurated');
		} else {
			$test->setType(X_Page_Item_Message::TYPE_WARNING);
			$test->setReason("Credentials not configurated. Contents could be not viewed");
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
			case 'plugins_opfitalia_auth_password':
				$password = $form->createElement('password', 'plugins_opfitalia_auth_password', array(
					'label' => $element->getLabel(),
					'description' => $element->getDescription(),
					'renderPassword' => true,
				));
				$form->plugins_opfitalia_auth_password = $password;
				break;
		}
		
		// remove cookie.jar if somethings has value
		if ( !$form->isErrors() && !is_null($element->getValue()) && file_exists(APPLICATION_PATH . '/../data/opfitalia/cookie.jar') ) {
			if ( @!unlink(APPLICATION_PATH . '/../data/opfitalia/cookie.jar') ) {
				X_Debug::e("Error removing cookie.jar");
			}
		}
	}	
	
	
	
	
	private function _fillPlaylist(X_Page_ItemList_PItem $items, $decoded, $lStack) {
		X_Debug::i("Decoded: ".print_r($decoded, true));
		// The format of $decoded (only notable parts):
		// {
		//		ok: true|false
		//		msg: $msg ; Setted only if ok = false
		//		folderId: $thisFolder
		//		folders: [
		//				{
		//					id: $folderID
		//					name: $folderName
		//				}, ...
		//			]
		//		objects: [
		//				{
		//					id: $objectID
		//					name: $objectName
		//					link: http://...?...&video=urlencoded(URL)&...
		//				},
		//				{
		//					id: $objectID
		//					name: $objectName
		//					link: http://...?...&host=megavideo&video=urlencoded(MEGAVIDEOURL)&...
		//				},
		//			]
		//		...
		// }
		foreach ($decoded->folders as $folder) {
			$_stack = $lStack;
			$_stack[] = $folder->id;
			$item = new X_Page_Item_PItem("opfitalia-folder-{$folder->id}", urldecode($folder->name) );
			$item->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setGenerator(__CLASS__)
				->setCustom(__CLASS__.':location', implode(':', $_stack))
				->setLink(array(
					'l' => X_Env::encode(implode(':', $_stack))
				),'default', false);
			$items->append($item);
		}
		
		foreach ($decoded->objects as $object) {
			
			$item = new X_Page_Item_PItem("opfitalia-video-{$object->id}", urldecode($object->name) );
			$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setGenerator(__CLASS__);
				
			// time to decode the link arg
			
			// file: index.php?page=show_streaming&video=http:\/\/www.dbforever.net\/strm\/onepiece\/one_piece_459.mp4&width=704&height=430
			// megavideo: index.php?page=show_streaming&host=megavideo&video=http:\/\/www.megavideo.com\/v\/GIV2R76V038221ac298ceb332a9cad75288c318b&width=640&height=480
				
			$link = explode('&', $object->link);
			
			$type = 'file';
			$href = '';
			
			foreach ($link as $sublink) {
				list($arg, $value) = @explode('=', $sublink, 2);
				if ( $arg == 'host' ) {
					if ( $value == 'megavideo' ) {
						$type = 'megavideo';
					}
				}
				if ( $arg == 'video' ) {
					$href = str_replace('\\/', '/', urldecode($value));
				}
			}
			
			if ( $type == 'megavideo' ) {
				// i gave to split the /v/ param only
				$splitted = explode('/v/', $href, 2);
				if ( count($splitted) == 2 ) {
					$href = $splitted[1];
				} else {
					// try to decode it
					// as ?v=
					preg_match('#\?v=(.+?)$#', $href, $id);
					if ( count($id) >= 2  ) {
						$href = $id[1];
					} else {
						// if even this fail
						// i have to skip thi entry
						continue;
					}
				}
				$item->setLabel($item->getLabel()." [Megavideo]");
				
			}
			
			$item->setCustom(__CLASS__.':location', implode(':', $lStack)."/$type/$href")
				->setLink(array(
					'action'	=> 'mode',
					'l'			=> X_Env::encode(implode(':', $lStack)."/$type/$href")
				),'default', false);
			$items->append($item);
		}
		
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
			'X-Requested-With: XMLHttpRequest',
			'Referer: http://www.opfitalia.net/mediacenter/index.php?page=show_streaming',
			'Content-Type: application/x-www-form-urlencoded'
		));
		
		$jarFile = APPLICATION_PATH . '/../data/opfitalia/cookie.jar';
		
		//$ns = new Zend_Session_Namespace(__CLASS__);
		
		if ( $this->jar == null ) {
			// Session disabled, i'm not sure wiimc can handle sessions
			/*if ( false && isset($ns->jar) && $ns->jar instanceof Zend_Http_CookieJar ) {
				$this->jar = $ns->jar;
				X_Debug::i('Loading stored authentication in Session');
			} else*/if ( file_exists($jarFile) ) {
				$this->jar = new Zend_Http_CookieJar();
				$cookies = unserialize(file_get_contents($jarFile));
				foreach ($cookies as $c) {
					$_c = new Zend_Http_Cookie($c['name'], $c['value'], $c['domain'], $c['exp'], $c['path']);
					$this->jar->addCookie($_c);
				}
				X_Debug::i('Loading stored authentication in File');
			} else {
				X_Debug::i('No cookie file');
			}
		}
		
		$http->setCookieJar($this->jar);
		//time to make the request
		
		$response = $http->request();
		$jsonString = $response->getBody();
		
		try {
			$decoded = Zend_Json::decode($jsonString, Zend_Json::TYPE_OBJECT);
			return $decoded;
		} catch (Exception $e) {
			// if the request doesn't return JSON code,
			// maybe user isn't authenticated
			
			X_Debug::i('User not authenticated');
			
			if ( $this->config('auth.username', '') != '' && $this->config('auth.password', '') != '' ) {
				X_Debug::i("Autentication needed");
				// do new login
				$http->setCookieJar(true);
				$pageLogin = $this->config('login.url', 'http://www.opfitalia.net/mediacenter/index.php?page=login');
				$http->setUri($pageLogin);
				// TODO remove this
				$http->setParameterPost ( array (
					'username' => (string) $this->config('auth.username', ''),
					'password' => (string) hash('sha256',$this->config('auth.password', ''), false),
					'redirectUrl' => '',
					'rememberMe' => '1'
				 ) );
					
				// TODO remove this
				if ( APPLICATION_ENV == 'development' ) {
					$response = $http->request(Zend_Http_Client::POST);
					if ( !$this->_isAuthenticated($response->getBody(), 'correttamente'  )) {
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
				
				$http->setUri($uri);
				$http->resetParameters(false);
				$response = $http->request(Zend_Http_Client::GET);
				$jsonString = $response->getBody();
	
				try {
					$decoded = Zend_Json::decode($jsonString, Zend_Json::TYPE_OBJECT);
				} catch (Exception $e) {
					// epic fail
					// Useless authentication
					//X_Debug::i('Epic fail page: '.print_r($jsonString, true));
					throw new Exception('Authetication failed');
				}
			} else {
				throw new Exception('Username/Password not found');
			}
		}
		return $decoded;
	}
	
	/**
	 * Search the html string for pattern that
	 * give info about authentication
	 * @param string $htmlString an opf page
	 */
	private function _isAuthenticated($htmlString, $pattern = 'correttamente') {
		
		//$pattern = '<li class="welcomelink">';
		return ( strpos($htmlString, $pattern) !== false );
		
	}
	
	
}
