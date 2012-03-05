<?php

class X_VlcShares_Plugins_Cache extends X_VlcShares_Plugins_Abstract {
	
	private $cacheEnabled = false;
	/**
	 * @var Application_Model_Cache
	 */
	private $inCache = false;
	
	protected $cacheAllowed = array(
		//'default/index/collections',
		'default/browse/share'
	);
	
	public function __construct() {
		
		$this
			->setPriority('gen_beforeInit')
			->setPriority('gen_beforePageBuild', 100)
			->setPriority('getIndexStatistics')
			->setPriority('getIndexManageLinks');
		
	}	
		
	/**
	 * Initialize cache helper
	 */
	public function gen_beforeInit(Zend_Controller_Action $controller) {
		$this->helpers()->registerHelper('cache', new X_VlcShares_Plugins_Helper_Cache($this));
	}
	
	/**
	 * Retrieve core statistics
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Statistic
	 */
	public function getIndexStatistics(Zend_Controller_Action $controller) {
		
		
		$entries = Application_Model_CacheMapper::i()->getCount();
		
		$stat = new X_Page_Item_Statistic($this->getId(), X_Env::_('p_cache_statstitle'));
		$stat->setTitle(X_Env::_('p_cache_statstitle'))
			->appendStat(X_Env::_('p_cache_stats_storedentries').": $entries");
			
		if ( $entries ) { 

			$urlHelper = $controller->getHelper('url');
			
			$clearOldHref = $urlHelper->url(array(
				'controller' => 'cache',
				'action'	=> 'clearold'
			), 'default', true);
			
			$clearAllHref = $urlHelper->url(array(
				'controller' => 'cache',
				'action'	=> 'clearall'
			), 'default', true);
			
			
			$clearOldLink = '<a href="'.$clearOldHref.'">'.X_Env::_('p_cache_stats_clearold').'</a>';
			$clearAllLink = '<a href="'.$clearAllHref.'">'.X_Env::_('p_cache_stats_clearall').'</a>';
			
			$stat->appendStat($clearOldLink)
				->appendStat($clearAllLink);
		}

		return new X_Page_ItemList_Statistic(array($stat));
	}
	
	
	
	/**
	 * Add the link for -cache-settings-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_cache_mlink'));
		$link->setTitle(X_Env::_('p_cache_managetitle'))
			//->setIcon('/images/cache/logo.png')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	$this->getId()
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}

	
	/**
	 * Return the "disable cache" button on top of collections index
	 * 
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return X_Page_ItemList_PItem
	 */
	public function preGetCollectionsItems(Zend_Controller_Action $controller) {
		X_Debug::i("Plugin triggered");
		return new X_Page_ItemList_PItem(array($this->getDisableCacheButton()));
	}
	
	
	public function preGetShareItems($provider, $location, Zend_Controller_Action $controller) {
		X_Debug::i("Plugin triggered");
		return new X_Page_ItemList_PItem(array($this->getDisableCacheButton()));
	}
	
	protected function getDisableCacheButton() {

		$link = new X_Page_Item_PItem('core-cache-disable', X_Env::_('p_cache_disablebutton', date('d/m/Y H:i', $this->inCache->getCreated() ) ));
		$link//->setIcon('/images/animeland/logo.png')
			->setDescription(X_Env::_('p_cache_disablebutton_desc'))
			->setType(X_Page_Item_PItem::TYPE_CONTAINER)
			->setLink(
				array(
					$this->getId() => '0',
				), 'default', false
			);
		
		return $link;
		
	}
	
	public function getShareItems($provider, $location, Zend_Controller_Action $controller) {
		X_Debug::i("Using cached content");
		if ( $this->inCache !== false ) {
			return unserialize($this->inCache->getContent());
		}
	}

	public function getCollectionsItems(Zend_Controller_Action $controller) {
		X_Debug::i("Using cached content");
		if ( $this->inCache !== false ) {
			return unserialize($this->inCache->getContent());
		}
	}
	
	/**
	 * check for cache
	 * @param Zend_Controller_Action $controller
	 */
	public function gen_beforePageBuild(Zend_Controller_Action $controller ) {
		
		/* @var $request Zend_Controller_Request_Http */
		$request = $controller->getRequest();
		
		$controllerName = $controller->getRequest()->getControllerName();
		$actionName = $controller->getRequest()->getActionName();
		$moduleName = $controller->getRequest()->getModuleName();
		
		// cache controller/action check
		if ( array_search("$moduleName/$controllerName/$actionName", $this->cacheAllowed) === false ) {
			return false;
		}
		
		X_Debug::i("Cache enabled");
		$this->cacheEnabled = true;
		// i have to remove all plugins registered plugins and enable this as only provider

		
		$cacheEntry = new Application_Model_Cache();
		Application_Model_CacheMapper::i()->fetchByUri($this->getCleanUri($controller), $cacheEntry);
		if ( !$cacheEntry->isValid(time() ) ) {
			
			// no valid cache entry
			X_Debug::i("Invalid cache entry. Is new? " . ($cacheEntry->isNew() ? 'yes' : 'no'));
			
			if ( !$cacheEntry->isNew() ) {
				X_Debug::i('Deleting cache entry, it\'s too old');
				Application_Model_CacheMapper::i()->delete($cacheEntry);
			}
			
			$this->setPriority('gen_afterPageBuild'); 
			return false;
		}

		// check for cache=0 for forced cache refresh
		if ( $this->config('refresh.allowed', true) && $request->getParam($this->getId(), '1') == '0' ) {
			
			X_Debug::i('Deleting cache entry for refresh');
			Application_Model_CacheMapper::i()->delete($cacheEntry);
			
			$params = $request->getParams();
			unset($params[$this->getId()]);
			
			$controller->getRequest()->clearParams()->setParams($params);
			
			unset($_GET[$this->getId()]);
			
			/* @var $router Zend_Controller_Router_Rewrite */
			$router = $controller->getFrontController()->getRouter();
			if ( method_exists($router, 'setGlobalParam' ) ) {
				$router->setGlobalParam($this->getId(), null);
			}
			
			$router->setParam($this->getId(), null);
			
			X_Debug::i('Ignoring cache entry');

			$this->setPriority('gen_afterPageBuild'); 
			
			return false;
		}		
		
		$this->inCache = $cacheEntry;
		
		X_Debug::i("Unregistering all plugins except this one");
		//X_VlcShares_Plugins::broker()->unregisterAll();
		//X_VlcShares_Plugins::broker()->registerPlugin($this->getId(), $this, true);
		
		$plugins = X_VlcShares_Plugins::broker()->getPlugins();
		foreach ($plugins as $plugin ) {
			/* @var $plugin X_VlcShares_Plugins_Abstract */
			$plugin
				->setPriority('preGetCollectionsItems', -1)
				->setPriority('getCollectionsItems', -1)
				->setPriority('postGetCollectionsItems', -1)
				->setPriority('filterCollectionsItems', -1)
				->setPriority('preGetShareItems', -1)
				->setPriority('getShareItems', -1)
				->setPriority('postGetShareItems', -1)
				->setPriority('filterShareItems', -1)
				->setPriority('orderShareItems', -1)
				;
		} 
		
		if ( (bool) $this->config('refresh.allowed', true) ) {
			$this->setPriority('preGetShareItems');
			$this->setPriority('preGetCollectionsItems');
		}
		
		$this->setPriority('getShareItems');
		$this->setPriority('getCollectionsItems');
		
	}
	
	
	/**
	 * store in cache
	 */
	public function gen_afterPageBuild(X_Page_ItemList_PItem $list, Zend_Controller_Action $controller) {
		
		if ( $this->cacheEnabled && $this->inCache === false ) {
			// store in cache the $list
			
			X_Debug::i("Storing value in cache");
			
			$cacheEntry = new Application_Model_Cache();
			
			Application_Model_CacheMapper::i()->fetchByUri($this->getCleanUri($controller), $cacheEntry);
			$cacheEntry->setUri($this->getCleanUri($controller))
				->setContent(serialize($list))
				->setValidity(time() + ($this->config('validity', 60) * 60 ));
				
			Application_Model_CacheMapper::i()->save($cacheEntry);
				
		}
		
	}
	
	protected function getCleanUri(Zend_Controller_Action $controller) {
		
		$uri = $controller->getRequest()->getRequestUri();
		
		$uri = str_replace(array(
			"/{$this->getId()}/0",
			"/{$this->getId()}/1",		
		 ), array('', ''), $uri);
		
		return $uri;
		
	}
	
	public function setDoNotCache() {
		if ( $this->cacheEnabled ) {
			X_Debug::i('This request will be not cached');
			$this->cacheEnabled = false;
			$this->setPriority('gen_afterPageBuild', -1);
		}
	}
	
	public function clearCache() {
		// should works as a truncate
		Application_Model_CacheMapper::i()->clearAll();
	}
	
	public function clearInvalid() {
		Application_Model_CacheMapper::i()->clearOutdated(time());
	}
	
	/**
	 * Store an item in cache manually for $validity time
	 * @param string $key content key for retrieval
	 * @param string $content
	 * @param int $validity number of minutes entry will be valid
	 * @return string
	 */
	public function storeItem($key, $content, $validity) {
		$key = "manual:$key";
		
		$cacheEntry = new Application_Model_Cache();
		
		Application_Model_CacheMapper::i()->fetchByUri($key, $cacheEntry);
		$cacheEntry->setUri($key)
			->setContent($content)
			->setCreated(time())
			->setValidity(time() + ($validity * 60));
			
		Application_Model_CacheMapper::i()->save($cacheEntry);
		
	}
	
	/**
	 * Force a cache storing even if plugin is disabled.
	 * Forced items are stored under the manual:forced: namespace
	 * @param string $key content key for retrieval
	 * @param string $content
	 * @param int $validity number of minutes entry will be valid
	 * @return string
	 */
	public static function forcedStoreItem($key, $content, $validity) {
		$key = "manual:forced:$key";
		
		$cacheEntry = new Application_Model_Cache();
		
		Application_Model_CacheMapper::i()->fetchByUri($key, $cacheEntry);
		$cacheEntry->setUri($key)
		->setContent($content)
		->setCreated(time())
		->setValidity(time() + ($validity * 60));
			
		Application_Model_CacheMapper::i()->save($cacheEntry);
	}

	/**
	 * Retrieve an item from the cache using $key
	 * @param string $key
	 * @return string
	 * @throws Exception if no valid item with $key found 
	 */
	public function retrieveItem($key) {
		$key = "manual:$key";
		
		$cacheEntry = new Application_Model_Cache();
		Application_Model_CacheMapper::i()->fetchByUri($key, $cacheEntry);
		
		if ( !$cacheEntry->isValid(time()) ) {
			throw new Exception("Invalid cache key");
		}
		
		return $cacheEntry->getContent();
	}

	/**
	 * Retrieve a forced item even if plugin is disabled
	 * Forced items are stored under the manual:forced: namespace
	 * 
	 * @param string $key
	 * @return string
	 * @throws Exception if no valid item with $key found 
	 */
	public static function forcedRetrieveItem($key) {
		$key = "manual:forced:$key";
		
		$cacheEntry = new Application_Model_Cache();
		Application_Model_CacheMapper::i()->fetchByUri($key, $cacheEntry);
		
		if ( !$cacheEntry->isValid(time()) ) {
			throw new Exception("Invalid cache key");
		}
		
		return $cacheEntry->getContent();
	}
	
	
}
