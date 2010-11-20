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
	
	/**
	 * Browses inside a location provided by a provider
	 */
	public function shareAction() {

		
		$request = $this->getRequest();
		
		X_VlcShares_Plugins::broker()->gen_preProviderSelection($this);
		
		$provider = $request->getParam('p', false);
		if ( $provider === false || !X_VlcShares_Plugins::broker()->isRegistered($provider) ) {
			throw new Exception("Invalid provider");
		}
		$location = X_Env::decode($request->getParam('l', ''));

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
		X_VlcShares_Plugins::broker()->gen_afterPageBuild($pageItems, $this);
		
	}

	public function modeAction() {

		$request = $this->getRequest();
		
		X_VlcShares_Plugins::broker()->gen_preProviderSelection($this);
		
		$provider = $request->getParam('p', false);
		if ( $provider === false || !X_VlcShares_Plugins::broker()->isRegistered($provider) ) {
			throw new Exception("Invalid provider");
		}
		$location = X_Env::decode($request->getParam('l', ''));

    	$pageItems = new X_Page_ItemList_PItem();
    	
    	// I add a "Play" button as first, this should redirect to stream action
    	// Plugins should add options button only 
    	$play = new X_Page_Item_PItem('core-play', X_Env::_('start_stream'));
    	$play->setType(X_Page_Item_PItem::TYPE_ELEMENT)
    		->setLink(array(
				'action' => 'stream'
			), 'default', false);
    	$pageItems->append($play);
		
    	// links on top
    	$pageItems->merge(X_VlcShares_Plugins::broker()->preGetModeItems($provider, $location, $this));
    	
    	// add separator between play items and options items
    	$separator = new X_Page_Item_PItem('core-separator', X_Env::_('_____options_separator_____'));
    	$separator->setType(X_Page_Item_PItem::TYPE_ELEMENT)
    		->setLink(
	    		X_Env::completeUrl(
					$this->_helper->url->url()
				));
    	$pageItems->append($separator);
    	
    	// normal links
    	$pageItems->merge(X_VlcShares_Plugins::broker()->getModeItems($provider, $location, $this));
    	// bottom links
		$pageItems->merge(X_VlcShares_Plugins::broker()->postGetModeItems($provider, $location, $this));
		
		// filter out items (parental-control / hidden file / system dir / unwanted options)
		foreach ($pageItems->getItems() as $key => $item) {
			$results = X_VlcShares_Plugins::broker()->filterModeItems($item, $provider, $this);
			if ( $results != null && in_array(false, $results) ) {
				$pageItems->remove($item);
			}
		}
		
		// trigger for page creation
		X_VlcShares_Plugins::broker()->gen_afterPageBuild($pageItems, $this);
		
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
	
		$location = X_Env::decode($request->getParam('l', ''));

		
    	$pageItems = new X_Page_ItemList_PItem();
    	
    	// I add a "Back" button as first, this should redirect to mode action
    	$back = new X_Page_Item_PItem('core-back', X_Env::_('back'));
    	$back->setType(X_Page_Item_PItem::TYPE_ELEMENT)
    		->setLink(array(
				'action' => 'mode',
    			'pid'	=> null
			), 'default', false);
    	$pageItems->append($back);
    	
		
    	// links on top
    	$pageItems->merge(X_VlcShares_Plugins::broker()->preGetSelectionItems($provider, $location, $pid, $this));
    	
    	// add separator between header items and options items
    	$separator = new X_Page_Item_PItem('core-separator', X_Env::_('_____options_separator_____'));
    	$separator->setType(X_Page_Item_PItem::TYPE_ELEMENT)
    		->setLink(
	    		X_Env::completeUrl(
					$this->_helper->url->url()
				));
    	$pageItems->append($separator);

    	
    	// normal links
    	$pageItems->merge(X_VlcShares_Plugins::broker()->getSelectionItems($provider, $location, $pid, $this));
    	// bottom links
		$pageItems->merge(X_VlcShares_Plugins::broker()->postGetSelectionItems($provider, $location, $pid, $this));
		
		// filter out items (parental-control / hidden file / system dir)
		foreach ($pageItems->getItems() as $key => $item) {
			$results = X_VlcShares_Plugins::broker()->filterSelectionItems($item, $provider, $pid, $this);
			if ( $results != null && in_array(false, $results) ) {
				$pageItems->remove($item);
			}
		}
		
		// trigger for page creation
		X_VlcShares_Plugins::broker()->gen_afterPageBuild($pageItems, $this);
		
		
	}
	
	public function streamAction() {

		$request = $this->getRequest();
		
		X_VlcShares_Plugins::broker()->gen_preProviderSelection($this);
		
		$provider = $request->getParam('p', false);
		if ( $provider === false || !X_VlcShares_Plugins::broker()->isRegistered($provider) ) {
			throw new Exception("Invalid provider");
		}
		$location = X_Env::decode($request->getParam('l', ''));

		
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
    	
		$pageItems = new X_Page_ItemList_PItem();
		
		// i can't add here the go to play button
		// because i don't know the output type
		// i need to leave this to the plugins, too
		// i hope that an output manager plugin
		// will be always enabled
    	
    	// top links
		$pageItems->merge(X_VlcShares_Plugins::broker()->preGetStreamItems($provider, $location, $this));
    	// normal links
    	$pageItems->merge(X_VlcShares_Plugins::broker()->getStreamItems($provider, $location, $this));
    	// bottom links
		$pageItems->merge(X_VlcShares_Plugins::broker()->postGetStreamItems($provider, $location, $this));    	
    	
    	
		// trigger for page creation
		X_VlcShares_Plugins::broker()->gen_afterPageBuild($pageItems, $this);
		
	}
}
