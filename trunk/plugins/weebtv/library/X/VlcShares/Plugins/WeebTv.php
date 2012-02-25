<?php 

class X_VlcShares_Plugins_WeebTv extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const VERSION = '0.1beta';
	const VERSION_CLEAN = '0.1';
	
	const URL_CHANNELS = 'http://weeb.tv/channels%s';
	const URL_CHANNEL = 'http://weeb.tv/channel/%s';
	const URL_PARAMS = 'http://weeb.tv/setPlayer';
	
	const LOCATION = '/^(?P<channel>[^/]+?)$/';
	
	//{{{NODE DEFINITION
	private $nodes = array(
		'exact:' => array(
				'function'	=> 'menuChannels',
				'params'	=> array()
			),
	);
	//}}}
	
	protected $cachedLocation = array();
	
	function __construct() {

		$this->setPriority('getCollectionsItems');
		$this->setPriority('getShareItems');
		$this->setPriority('preGetStreamItems');
		$this->setPriority('preGetControlItems');
		$this->setPriority('getIndexManageLinks');
		$this->setPriority('gen_beforeInit');
		$this->setPriority('prepareConfigElement');
		
		// check for __bootstrap loading
		try {
			X_VlcShares_Plugins::helpers()->streamer()->register(new X_Streamer_Engine_RtmpDumpWeebTv());
		} catch (Exception $e) {
			$this->setPriority('gen_afterPluginsInitialized');			
		}
		
	}

	/**
	 * Inizialize translator for this plugin
	 * @param Zend_Controller_Action $controller
	 */
	function gen_beforeInit(Zend_Controller_Action $controller) {
		$this->helpers()->language()->addTranslation(__CLASS__);
	}
	
	/**
	 * Register the RtmpDumpWeebTv streamer engine
	 * @param X_VlcShares_Plugins_Broker $broker
	 */
	public function gen_afterPluginsInitialized(X_VlcShares_Plugins_Broker $broker) {
		$this->helpers()->streamer()->register(new X_Streamer_Engine_RtmpDumpWeebTv());
	}
	
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::getLocation()
	 */
	function resolveLocation($location = null) {

		if ( $location == '' || $location == null ) return false;
		
		if ( array_key_exists($location, $this->cachedLocation) ) {
			return $this->cachedLocation[$location];
		}
		
		X_Debug::i("Requested location: $location");

		
		if ( $location == null ) {
			// location isn't a valid video id, so we return fals
			// and insert the query result in the cache
			$this->cachedLocation[$location] = false;
			return false;	
		}
		
		/*
		// resolve the link:
		try {
			$return = $this->getLinkHosterUrl($location);
		} catch ( Exception $e) {
			$return = false;
			X_Debug::e("Error found: {$e->getMessage()}");
		}
		*/

		// resolve a fake rtmpdump-weebtv address to wake up rmtpgw-weebtv
		
		$return = X_RtmpDumpWeebTv::getFakeUri($location);
		
		
		$this->cachedLocation[$location] = $return;
		return $return;
	
	}
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::getParentLocation()
	 */
	function getParentLocation($location = null) {
		if ( $location == '' || $location == null ) return false;
		
		$exploded = explode('/', $location);
		
		array_pop($exploded);
		
		if ( count($exploded) >= 1 ) {
			return implode('/', $exploded);
		} else {
			return null;
		}			
	}
	
	
	/**
	 * Add the go to stream link (only if engine is rtmpdump-weebtv)
	 *
	 * @param X_Streamer_Engine $engine selected streamer engine
	 * @param string $uri
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to stream
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return X_Page_ItemList_PItem
	 */
	public function preGetStreamItems(X_Streamer_Engine $engine, $uri, $provider, $location, Zend_Controller_Action $controller) {
	
		// ignore the call if streamer is not rtmpdump
		if ( !($engine instanceof X_Streamer_Engine_RtmpDumpWeebTv ) ) return;
	
		X_Debug::i('Plugin triggered');
	
		$return = new X_Page_ItemList_PItem();
	
	
		$outputLink = "http://{%SERVER_NAME%}:{$this->getStreamingPort()}/";
		$outputLink = str_replace(
				array(
						'{%SERVER_IP%}',
						'{%SERVER_NAME%}'
				),array(
						$_SERVER['SERVER_ADDR'],
						strstr($_SERVER['HTTP_HOST'], ':') ? strstr($_SERVER['HTTP_HOST'], ':') : $_SERVER['HTTP_HOST']
				), $outputLink
		);
	
		
		// try to decode the $location
		try {
			$outputLink .= $this->getLinkParams($location);
		} catch (Exception $e) {
			X_Debug::e("Unable to decode rtmp params");
		}
		
		$item = new X_Page_Item_PItem($this->getId(), X_Env::_('p_outputs_gotostream'));
		$item->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
		->setIcon('/images/icons/play.png')
		->setLink($outputLink);
		$return->append($item);
	
		return $return;
	
	}
	
	/**
	 * Add the button BackToStream in controls page
	 *
	 * @param X_Streamer_Engine $engine
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array
	 */
	public function preGetControlItems(X_Streamer_Engine $engine, Zend_Controller_Action $controller) {
	
		// ignore if the streamer is not vlc
		if ( !($engine instanceof X_Streamer_Engine_RtmpDumpWeebTv ) ) return;
	
		$outputLink = "http://{%SERVER_NAME%}:{$this->getStreamingPort()}/";
		$outputLink = str_replace(
				array(
						'{%SERVER_IP%}',
						'{%SERVER_NAME%}'
				),array(
						$_SERVER['SERVER_ADDR'],
						strstr($_SERVER['HTTP_HOST'], ':') ? strstr($_SERVER['HTTP_HOST'], ':') : $_SERVER['HTTP_HOST']
				), $outputLink
		);
		
		
		// try to get the location and provider
		$provider = $controller->getRequest()->getParam('p', false);
		$location = $controller->getRequest()->getParam('l', false);
		
		if ( $provider == $this->getId() && $location ) {
			$location = X_Env::decode($location);
			// try to decode the $location
			try {
				$outputLink .= $this->getLinkParams($location);
			} catch (Exception $e) {
				X_Debug::e("Unable to decode rtmp params");
			}
		}
		
	
		$item = new X_Page_Item_PItem($this->getId(), X_Env::_('p_profiles_backstream'));
		$item->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
		->setIcon('/images/icons/play.png')
		->setLink($outputLink);
		return new X_Page_ItemList_PItem(array($item));
	
	}	
	
	public function getStreamingPort() {
		return $this->config("streaming.port", '8081');
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
			case 'plugins_weebtv_auth_password':
				$password = $form->createElement('password', 'plugins_weebtv_auth_password', array(
				'label' => $element->getLabel(),
				'description' => $element->getDescription(),
				'renderPassword' => true,
				));
				$form->plugins_weebtv_auth_password = $password;
				break;
		}
	
	}
	
		
	
	/**
	 * Add the TvLinks link inside collection index
	 * @param Zend_Controller_Action $controller
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");
		
		return X_VlcShares_Plugins_Utils::getCollectionsEntryList($this->getId());
		
	}
	

	/**
	 * Fetch resources from filmstream site
	 * @param string $provider the plugin key of the one who should handle the request
	 * @param string $location the current $location
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return X_Page_ItemList_PItem
	 */
	public function getShareItems($provider, $location, Zend_Controller_Action $controller) {
		// this plugin fetch resources only if it's the provider
		if ( $provider != $this->getId() ) return;
		// add an info inside the debug log so we can trace this call 
		X_Debug::i('Plugin triggered');
		// disable automatic sorting, items will be already sorted in the target site
		X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_SortItems');
		// let's create the itemlist
		$items = new X_Page_ItemList_PItem();
		// show the requested location in the debug log
		// $location has been already decoded
		X_Debug::i("Requested node: $location");

		X_VlcShares_Plugins_Utils::menuProxy($items, $location, $this->nodes, $this );
		
		return $items;
	}
	
	/**
	 *	Add button -watch stream directly-
	 * 
	 * @param string $provider
	 * @param string $location
	 * @param Zend_Controller_Action $controller
	 */
	public function preGetModeItems($provider, $location, Zend_Controller_Action $controller) {

		if ( $provider != $this->getId()) return;
		X_Debug::i("Plugin triggered");
		return X_VlcShares_Plugins_Utils::getWatchDirectlyOrFilter($this->getId(), $this, $location);
	}
	
	
	/**
	 * Add the link for -manage-streamingonline-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {
		return X_VlcShares_Plugins_Utils::getIndexManageEntryList($this->getId());
	}
	
	/**
	 * Show an error message if one of the plugin dependencies is missing
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Message
	 */
	public function getIndexMessages(Zend_Controller_Action $controller) {
		$messages = new X_Page_ItemList_Message();
		
		if ( class_exists("X_VlcShares_Plugins_Utils", true) ) {
			if ( !method_exists('X_VlcShares_Plugins_Utils', 'menuProxy')  ) {
				// old version of PageParserLib
				$message = new X_Page_Item_Message($this->getId(),"PageParserLib plugin version is old. Please, update it");
				$message->setType(X_Page_Item_Message::TYPE_FATAL);
				$messages->append($message);
			}
		} else {
			$message = new X_Page_Item_Message($this->getId(),"PageParser API is required from RaiClick. Please, install PageParserLib plugin");
			$message->setType(X_Page_Item_Message::TYPE_FATAL);
			$messages->append($message);
		}
		return $messages;
	}
	
	public function menuChannels(X_Page_ItemList_PItem $items ) {
		
		$auth = '';
		if ( $this->config('auth.enabled', false) ) {
			$auth = "&username=".rawurlencode($this->config('auth.username', ''))."&userpassword=".rawurlencode($this->config('auth.password', ''))."&option=andback";
		}
		
		$page = X_PageParser_Page::getPage(
				sprintf(self::URL_CHANNELS, $auth),
				//new X_PageParser_Parser_TvLinks(X_PageParser_Parser_TvLinks::MODE_TITLES, $type, $filter)
				new X_PageParser_Parser_Preg('%<p style="font-size:12px;.+>(?P<label>.*?)</a></p>(.*\n){5}.*<a href="http://weeb.tv/channel/(?P<href>.*?)" title="(.*?)"><img src="(?P<thumbnail>.*)" alt=".*?" height="100" width="100" /></a>%', X_PageParser_Parser_Preg::PREG_MATCH_ALL, PREG_SET_ORDER)
		);
		$this->preparePageLoader($page);
		$parsed = $page->getParsed();
	
		foreach ( $parsed as $match ) {
			$thumbnail = $match['thumbnail'];
			$label = $match['label'];
			$href = $match['href'];
				
			$item = new X_Page_Item_PItem($this->getId()."-{$label}", $label );
			$item->setIcon('/images/icons/file_32.png')
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setCustom(__CLASS__.':location', "$href")
				->setDescription(APPLICATION_ENV == 'development' ? "$href" : null)
				->setThumbnail($thumbnail)
				->setLink(array(
						'action' => 'mode',
						'l'	=>	X_Env::encode("$href")
				), 'default', false);
				
			$items->append($item);
				
				
		}
	
	}


	
	public function getLinkParams($linkId) {
		
		$channelParams = false;
		try {
			/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
			$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
			$channelParams = unserialize($cacheHelper->retrieveItem("weebtv::$linkId"));
			X_Debug::i("Valid cache entry found: ".print_r($channelParams, true));
		} catch (Exception $e) {
			// no cache plugin or no entry in cache, it's the same
			X_Debug::i("Cache disabled or no valid entry found");
		}

		if ( !$channelParams ) {
			// get the cid from the page
			
			X_Debug::i("Fetching channel params for: {{$linkId}}");
			
			$page = X_PageParser_Page::getPage(
					sprintf(self::URL_CHANNEL, $linkId),
					new X_PageParser_Parser_Preg(
							'%<param name="movie" value="(?P<swf>.+?)" />.*?<param name="flashvars" value="&cid=(?P<cid>.+?)" />%s',
							X_PageParser_Parser_Preg::PREG_MATCH)
			);
			$this->preparePageLoader($page);
			$_channelParams = $page->getParsed();
			$channelParams = array();
			// clean params from useless keys
			foreach ($_channelParams as $key => $value) {
				if ( $key == 'cid' || $key == 'swf' ) {
					$channelParams[$key] = $value;
				}
			}
			
			X_Debug::i("Params: ".print_r($channelParams, true));
			
			unset($page);
		}
		
		$cid = @$channelParams['cid'];
		$swfUrl = @$channelParams['swf'];
		
		if ( !$cid ) {
			X_Debug::e("Cid not found for channel {{$linkId}}");
			throw new Exception("Cid not found for channel {{$linkId}}");
		}

		// store in cache if possible
		try {
			/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
			$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
		
			$cacheHelper->storeItem("weebtv::$linkId", serialize($channelParams), 60); // store for the next 15 min
		
			X_Debug::i("Value stored in cache for 60 min: {key = weebtv::$linkId}");
		
		} catch (Exception $e) {
			// no cache plugin, next time i have to repeat the request
		}
		
		
		// first get the playlist url from the page

		$authEnabled = $this->config('auth.enabled', false);
		$username = rawurlencode($this->config('auth.username', ''));
		$password = rawurlencode($this->config('auth.password', ''));
		$authString = '';
		
		if ( $authEnabled ) {
			$authString = "&username={$username}&userpassword={$password}";
		}

		// without encode
		$username = $this->config('auth.username', '');
		$password = $this->config('auth.password', '');
		
		$http = new Zend_Http_Client(self::URL_PARAMS.$authString, array(
				'headers' => array(
						'User-Agent' => 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11',
				)
		));
		
		$http->setParameterPost('firstConnect', '1');
		$http->setParameterPost('watchTime', '0');
		$http->setParameterPost('cid', $cid);
		$http->setParameterPost('ip', 'NaN');
		
		if ( $authEnabled ) {
			$http->setParameterPost('username', $username);
			$http->setParameterPost('password', $password);
		}
		
		$str = $http->request('POST')->getBody();
		
		$params = array();
		parse_str($str, $params);
		
		$rtmpParams = array();
		
		$check = array(
				'ticket' => 73,
				'rtmp' => 10,
				'time' => 16,
				'playPath' => 11
		);
		
		foreach ($check as $label => $key) {
			if ( isset($params[$key]) ) {
				$rtmpParams[$label] = $params[$key];
			}
		}

		X_Debug::i("Fetched stream params for channel {{$linkId}}: ".print_r($rtmpParams, true));		
		
		$ticket = $rtmpParams['ticket'];
		if ( $authEnabled ) {
			$ticket .= ";$username;$password";
		}
		
		$hosterLocation = X_RtmpDumpWeebTv::buildHttpRequest(array(
				'rtmp' => $rtmpParams['rtmp'].'/'.$rtmpParams['playPath'],
				'swfUrl' => rawurldecode($swfUrl),
				'weeb' => $ticket,
				'live' => '1',
			));
		
		X_Debug::i("Hoster location resolved: $hosterLocation");
		
		return $hosterLocation;
	
	}	
	
	
	private function preparePageLoader(X_PageParser_Page $page) {

		$loader = $page->getLoader();
		if ( $loader instanceof X_PageParser_Loader_Http || $loader instanceof X_PageParser_Loader_HttpAuthRequired ) {
		
			$http = $loader->getHttpClient()->setConfig(array(
				'maxredirects'	=> $this->config('request.maxredirects', 10),
				'timeout'		=> $this->config('request.timeout', 25)
			));
			
			$http->setHeaders(array(
				'User-Agent' => 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11'
			));
			
		}		
	}
	
	
	/**
	 * Disable cache plugin is registered and enabled
	 */
	private function disableCache() {
		
		if ( X_VlcShares_Plugins::broker()->isRegistered('cache') ) {
			$cache = X_VlcShares_Plugins::broker()->getPlugins('cache');
			if ( method_exists($cache, 'setDoNotCache') ) {
				$cache->setDoNotCache();
			}
		}
		
	}
	
}
