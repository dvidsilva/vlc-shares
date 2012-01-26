<?php 


class X_VlcShares_Plugins_RealDebrid extends X_VlcShares_Plugins_Abstract {
		
	const VERSION_CLEAN = '0.1.2';
	const VERSION = '0.1.2';
	
	private $hosters = array(
		// replace-id => with class
		'megavideo' => 'X_VlcShares_Plugins_Helper_Hoster_RealDebridMegavideo',
		'megaupload' => 'X_VlcShares_Plugins_Helper_Hoster_RealDebridMegaupload',
		'videobb' => 'X_VlcShares_Plugins_Helper_Hoster_RealDebridVideoBB',
		'4shared' => 'X_VlcShares_Plugins_Helper_Hoster_RealDebrid4Shared',
		'megaporn' => 'X_VlcShares_Plugins_Helper_Hoster_RealDebridMegaporn',
		'rapidshare' => 'X_VlcShares_Plugins_Helper_Hoster_RealDebridRapidShare',
		'videozer' => 'X_VlcShares_Plugins_Helper_Hoster_RealDebridVideozer',
		'hulu' => 'X_VlcShares_Plugins_Helper_Hoster_RealDebridHulu',
		'2shared' => 'X_VlcShares_Plugins_Helper_Hoster_RealDebrid2Shared',
		'cwtv' => 'X_VlcShares_Plugins_Helper_Hoster_RealDebridCwtv',
		'cbs' => 'X_VlcShares_Plugins_Helper_Hoster_RealDebridCbs',
		'videoweed' => 'X_VlcShares_Plugins_Helper_Hoster_RealDebridVideoweed',
	);
	
	public function __construct() {
		$this
			// gen_beforeInit must be loaded with low priority, other hosters must be setted first 
			->setPriority('gen_beforeInit', 99)
			->setPriority('getIndexManageLinks')
			->setPriority('getModeItems')
			->setPriority('preGetSelectionItems')
			->setPriority('getSelectionItems')
			->setPriority('prepareConfigElement');
		
	}
	
	public function gen_beforeInit(Zend_Controller_Action $controller) {
		// translation
		$this->helpers()->language()->addTranslation(__CLASS__);
		
		$this->helpers()->registerHelper('realdebrid', new X_VlcShares_Plugins_Helper_RealDebrid(array(
			'username' => $this->config('auth.username', ''),
			'password' => $this->config('auth.password', ''),
			'cache' => $this->config('cache.validity', 24 * 60),
		)));
		
		foreach ( $this->hosters as $hId => $hClass ) {
			// only if hosters is enabled
			if ( $this->config("hosters.$hId.enabled", true) ) {
				$pHoster = null;
				try {
					$pHoster = $this->helpers()->hoster()->getHoster($hId);
					$this->helpers()->hoster()->unregisterHoster($hId);
				} catch (Exception $e) {
					// no problem, wasn't registered
				}
				$hObj = new $hClass();
				// allow to use a standard hoster (if available) for fetch informations
				// because it should work better than realdebrid hoster
				if ( method_exists($hObj, 'setParentHoster') ) {
					$hObj->setParentHoster($pHoster);
				}
				// register the new one:
				//  - repleace the old hoster id if there is one (so old links work with new hoster)
				//  - otherwise register it as a new one (with the right icon), so new links are locked to this hoster
				$this->helpers()->hoster()->registerHoster($hObj, ($pHoster === null ? null : $hId ) );
			}
		}
		
	}
	
	/**
	 * Allow to choose the stream type (if more than one is available)
	 * 
	 * @param string $provider
	 * @param string $location
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function getModeItems($provider, $location, Zend_Controller_Action $controller) {

		// if provider is FileSystem, This isn't needed for sure
		if ( X_VlcShares_Plugins::broker()->getPluginClass($provider) == 'X_VlcShares_Plugins_FileSystem' ) {
			return;
		}
		
		try {
		
			/* @var $realdebridHelper X_VlcShares_Plugins_Helper_RealDebrid */
			$realdebridHelper = $this->helpers('realdebrid');
			
			// check if a valid location has been setted
			if ( !$realdebridHelper->isValid() ) {
				X_Debug::i("Location is not provided by RealDebrid");
				return;
			}
			
			$lists = new X_Page_ItemList_PItem();
			
			X_Debug::i('Plugin triggered. Location could be provided by RealDebrid');
			
			$urlHelper = $controller->getHelper('url');
			
			$links = $realdebridHelper->getUrls();
			
			if ( count($links) <= 1 ) {
				X_Debug::i('Links count is <= 1, no choose needed');
			} else {
			
				$selectedIndex = $controller->getRequest()->getParam('realdebrid', 0);
				if ( count($links) < $selectedIndex ) {
					$selectedIndex = 0;
				}
				
				$linkPart = explode('/', $links[$selectedIndex]);
				$selectedLink = urldecode(array_pop($linkPart));
		
				$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_realdebrid_selected', $selectedLink, $links[$selectedIndex]));
				$link->setIcon('/images/realdebrid/logo.png')
					->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setLink(array(
							'action'	=>	'selection',
							'pid'		=>	$this->getId()
						), 'default', false);
		
				$lists->append($link);
			}
			
			
			$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_realdebrid_regenerate'));
			$link->setIcon('/images/realdebrid/logo.png')
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(array(
						'action'	=>	'selection',
						'pid'		=>	"{$this->getId()}",
						"{$this->getId()}:refresh" => 1
					), 'default', false);
	
			$lists->append($link);
			
			return $lists;
			
		} catch (Exception $e) {
			X_Debug::i("Location is not provided by RealDebrid");
		}
	}

	/**
	 * Set the header of stream type selection
	 * @param string $provider
	 * @param string $location
	 * @param string $pid
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function preGetSelectionItems($provider, $location, $pid, Zend_Controller_Action $controller) {
		// we want to expose items only if pid is this plugin
		if ( $this->getId() != $pid ) return;
		
		X_Debug::i('Plugin triggered');	
		
		$urlHelper = $controller->getHelper('url');
		$link = new X_Page_Item_PItem($this->getId().'-header', X_Env::_('p_realdebrid_streamselection_title'));
		$link->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setLink(X_Env::completeUrl($urlHelper->url()));
		return new X_Page_ItemList_PItem(array($link));
		
	}
	
	
	/**
	 * Show the list of valid streams
	 * @param string $provider
	 * @param string $location
	 * @param string $pid
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function getSelectionItems($provider, $location, $pid, Zend_Controller_Action $controller) {
		// we want to expose items only if pid is this plugin
		if ( $this->getId() != $pid ) return;
		
		X_Debug::i('Plugin triggered');
		
		$urlHelper = $controller->getHelper('url');
		
		
		// i try to mark current selected sub based on $this->getId() param
		// in $currentSub i get the name of the current profile
		$currentStream = $controller->getRequest()->getParam($this->getId(), false);

		$return = new X_Page_ItemList_PItem();

		
		try {
		
			/* @var $realdebridHelper X_VlcShares_Plugins_Helper_RealDebrid */
			$realdebridHelper = $this->helpers('realdebrid');
			
			// check if a valid location has been setted
			if ( !$realdebridHelper->isValid() ) {
				X_Debug::i("Try to force location retrieval");
				$providerObj = X_VlcShares_Plugins::broker()->getPlugins($provider);
				if ( $providerObj instanceof X_VlcShares_Plugins_ResolverInterface ) {
					$providerObj->resolveLocation($location);
				} elseif (method_exists($providerObj, 'resolveLocation') ) {
					// try to check if resolveLocation is there, even if not the interface
					$providerObj->resolveLocation($location);
				} else {
					X_Debug::e("Provider can't be called for location resolving");
					return;
				}
				
			}
			
			$links = $realdebridHelper->getUrls();
			
			X_Debug::i('Plugin triggered. Location could be provided by RealDebrid');

						// if $pid == ID:refresh, only force refresh the link
			if ( $controller->getRequest()->getParam("{$this->getId()}:refresh", false) != false ) {
				
				$realdebridHelper->cleanCurrentCacheEntry();

				$item = new X_Page_Item_PItem($this->getId(), X_Env::_('p_realdebrid_refreshdone'));
				$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setLink(array(
							'action'				=> 'mode',
							"{$this->getId()}:refresh" => null,
							'pid'					=> null
						), 'default', false);
				$return->append($item);
				
			} else {
			
				foreach ($links as $i => $streamlink) {
				
					$linkPart = explode('/', $streamlink);
					$label = urldecode(array_pop($linkPart));
					
					$item = new X_Page_Item_PItem($this->getId()."-$i", X_Env::_('p_realdebrid_streamoption', $label, $streamlink));
					$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
						->setLink(array(
								'action'				=> 'mode',
								$this->getId()			=> $i == 0 ? null : $i, // unset this plugin selection
								'pid'					=> null
							), 'default', false)
						->setHighlight(($currentStream == $i));
					$return->append($item);
				
				}
			}
			
		} catch ( Exception $e) {
			X_Debug::f("Location is not provided by RealDebrid, but i'm inside the stream type selection O_o");
		}
		
		return $return;
	}		
	
	
	/**
	 * Add the link for -manage-megavideo-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_realdebrid_mlink'));
		$link->setTitle(X_Env::_('p_realdebrid_managetitle'))
			->setIcon('/images/realdebrid/logo.png')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'realdebrid'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
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
			case 'plugins_realdebrid_auth_password':
				$password = $form->createElement('password', 'plugins_realdebrid_auth_password', array(
					'label' => $element->getLabel(),
					'description' => $element->getDescription(),
					'renderPassword' => true,
				));
				$form->plugins_realdebrid_auth_password = $password;
				break;
		}
		
		// remove cookie.jar if somethings has value
		if ( !$form->isErrors() && !is_null($element->getValue()) && $key = 'plugins_realdebrid_auth_password' ) {
			// clean cookies
			try {
				X_Debug::i("Cleaning up cookies in cache");
				/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
				$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
				try {
					$cacheHelper->retrieveItem("realdebrid::cookies");
					// set expire date to now!
					$cacheHelper->storeItem("realdebrid::cookies", '', 0);
				} catch (Exception $e) {
					// nothing to do
				}
			} catch (Exception $e) {
				X_Debug::w("Cache plugin disabled? O_o");
			}
		}
	}
	
	
}
