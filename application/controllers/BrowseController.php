<?php

/**
 * BrowseController
 * 
 * @author ximarx
 */

require_once 'X/Controller/Action.php';
require_once 'X/Vlc.php';
require_once 'X/Plx.php';
require_once 'X/Plx/Item.php';

class BrowseController extends X_Controller_Action {
	
	/**
	 * 
	 * @var X_Vlc
	 */
	protected $vlc = null;

	public function init() {
		
		parent::init();
		$this->vlc = new X_Vlc($this->options->vlc);
		
	}
	
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {
		$this->_forward('collections', 'index');
	}
	
	public function shareAction() {

		
		$request = $this->getRequest();
		
		X_VlcShares_Plugins::broker()->gen_preProviderSelection($this);
		
		$provider = $request->getParam('p', false);
		if ( $provider === false || !X_VlcShares_Plugins::broker()->isRegistered($provider) ) {
			throw new Exception("Invalid provider");
		}
		$location = base64_decode($request->getParam('l', ''));

    	$pageItems = new X_Page_ItemList_PItem();
    	
    	// links on top
    	$pageItems->merge(X_VlcShares_Plugins::broker()->preGetShareItems($provider, $location, $this));
    	// normal links
    	$pageItems->merge(X_VlcShares_Plugins::broker()->getShareItems($provider, $location, $this));
    	// bottom links
		$pageItems->merge(X_VlcShares_Plugins::broker()->postGetShareItems($provider, $location, $this));
		
		// filter out items (parental-control / hidden file / system dir)
		foreach ($pageItems->getItems() as $key => $item) {
			$results = X_VlcShares_Plugins::broker()->filterShareItems($item, $provider, $this);
			if ( $results != null && in_array(false, $results) ) {
				$pageItems->remove($item);
			}
		}
		
		// I have to rebuild the itemlist because I can't rearrange items orders
		$items = $pageItems->getItems();
		X_VlcShares_Plugins::broker()->orderShareItems(&$items, $provider,  $this);
		$pageItems = new X_Page_ItemList_PItem($items);
		
		
		// trigger for page creation
		X_VlcShares_Plugins::broker()->gen_afterPageBuild(&$pageItems, $this);
		
	}

	public function modeAction() {

		$request = $this->getRequest();
		
		X_VlcShares_Plugins::broker()->gen_preProviderSelection($this);
		
		$provider = $request->getParam('p', false);
		if ( $provider === false || !X_VlcShares_Plugins::broker()->isRegistered($provider) ) {
			throw new Exception("Invalid provider");
		}
		$location = base64_decode($request->getParam('l', ''));

    	$pageItems = array();
    	
    	// I add a "Play" button as first, this should redirect to stream action
    	// Plugins should add options button only 
    	
    	$pageItems[] = array(
			'label'		=>	X_Env::_('start_stream'),
			'link'		=>	X_Env::completeUrl(
				$this->_helper->url->url(
					array(
						'action' => 'stream'
						//'l'	=>	base64_encode("{$share->getId()}:/")
					), 'default', false
				)
			),
			//__CLASS__.':location'	=>	"{$share->getId()}:/"
		);
		
    	// links on top
    	$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->preGetModeItems($provider, $location, $this));
    	
    	$pageItems[] = array(
			'label'		=>	X_Env::_('_____options_separator_____'),
			'link'		=>	X_Env::completeUrl(
				$this->_helper->url->url()
			),
			//__CLASS__.':location'	=>	"{$share->getId()}:/"
		);
    	
    	// normal links
    	$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->getModeItems($provider, $location, $this));
    	// bottom links
		$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->postGetModeItems($provider, $location, $this));
		
		// filter out items (parental-control / hidden file / system dir)
		foreach ($pageItems as $key => $item) {
			if ( in_array(false, X_VlcShares_Plugins::broker()->filterModeItems($item, $provider, $this)) ) {
				unset($pageItems[$key]);
			}
		}
		
		// Items shouldn't be sorted: they already have a order
		//X_VlcShares_Plugins::broker()->orderModeItems(&$pageItems, $provider,  $this);
		
		// trigger for page creation
		X_VlcShares_Plugins::broker()->gen_afterPageBuild(&$pageItems, $this);
		
	}
	
	public function selectionAction() {

		$request = $this->getRequest();
		
		X_VlcShares_Plugins::broker()->gen_preProviderSelection($this);
		
		$provider = $request->getParam('p', false);
		if ( $provider === false || !X_VlcShares_Plugins::broker()->isRegistered($provider) ) {
			throw new Exception("Invalid provider");
		}
		$pid = $request->getParam('pid', false);
		if ( $pid === false || !X_VlcShares_Plugins::broker()->isRegistered($pid) ) {
			throw new Exception("Invalid pluginId");
		}
	
		$location = base64_decode($request->getParam('l', ''));

		
    	$pageItems = array();
    	
    	// I add a "Play" button as first, this should redirect to stream action
    	// Plugins should add options button only 
    	
    	$pageItems[] = array(
			'label'		=>	X_Env::_('back'),
			'link'		=>	X_Env::completeUrl(
				$this->_helper->url->url(
					array(
						'action' => 'mode',
						'pid'	=>	null
					), 'default', false
				)
			),
			//__CLASS__.':location'	=>	"{$share->getId()}:/"
		);
    	
		
    	// links on top
    	$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->preGetSelectionItems($provider, $location, $pid, $this));
    	
    	$pageItems[] = array(
			'label'		=>	X_Env::_('_____options_separator_____'),
			'link'		=>	X_Env::completeUrl(
				$this->_helper->url->url()
			),
			//__CLASS__.':location'	=>	"{$share->getId()}:/"
		);
    	
    	
    	// normal links
    	$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->getSelectionItems($provider, $location, $pid, $this));
    	// bottom links
		$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->postGetSelectionItems($provider, $location, $pid, $this));
		
		// filter out items (parental-control / hidden file / system dir)
		foreach ($pageItems as $key => $item) {
			if ( in_array(false, X_VlcShares_Plugins::broker()->filterSelectionItems($item, $provider, $pid, $this)) ) {
				unset($pageItems[$key]);
			}
		}
		
		// Items shouldn't be sorted: they already have a order
		//X_VlcShares_Plugins::broker()->orderModeItems(&$pageItems, $provider,  $this);
		
		// trigger for page creation
		X_VlcShares_Plugins::broker()->gen_afterPageBuild(&$pageItems, $this);
		
		
	}
	
	public function streamAction() {

		$request = $this->getRequest();
		
		X_VlcShares_Plugins::broker()->gen_preProviderSelection($this);
		
		$provider = $request->getParam('p', false);
		if ( $provider === false || !X_VlcShares_Plugins::broker()->isRegistered($provider) ) {
			throw new Exception("Invalid provider");
		}
		$location = base64_decode($request->getParam('l', ''));

		
    	// each arg is stored as in a LIFO stack. If i put top priority as first,
    	// low priority args could override it. So i use an inverse priority insertion  
    	// register low priority args
    	X_VlcShares_Plugins::broker()->preRegisterVlcArgs($this->vlc, $provider, $location, $this);
    	// register normal priority args
    	X_VlcShares_Plugins::broker()->registerVlcArgs($this->vlc, $provider, $location, $this);
    	// register top priority args
    	X_VlcShares_Plugins::broker()->postRegisterVlcArgs($this->vlc, $provider, $location, $this);
    	
    	X_VlcShares_Plugins::broker()->preSpawnVlc($this->vlc, $provider, $location, $this);
    	$this->vlc->spawn(); // in test leave this commented out
    	X_VlcShares_Plugins::broker()->postSpawnVlc($this->vlc, $provider, $location, $this);
    	
		$pageItems = array();
		
		// i can't add here the go to play button
		// because i don't know the output type
		// i need to leave this to the plugins, too
		// i hope that an output manager plugin
		// will be always enabled
    	
    	// top links
		$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->preGetStreamItems($provider, $location, $this));
    	// normal links
    	$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->getStreamItems($provider, $location, $this));
    	// bottom links
		$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->postGetStreamItems($provider, $location, $this));    	
    	
    	
		// trigger for page creation
		X_VlcShares_Plugins::broker()->gen_afterPageBuild(&$pageItems, $this);
		
	}
}
