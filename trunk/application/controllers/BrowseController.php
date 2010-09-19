<?php

/**
 * BrowseController
 * 
 * @author ximarx
 * @version 0.4.2
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
	
	/*
	// vlc check if running is done at plugin level
	// with a predispatch trigger
	public function preDispatch() {
		
		X_Env::debug(__METHOD__);
		
		// devo controllare se vlc e' attivo, ma non posso farlo 
		// tramite pid su windows
		if ( $this->vlc->isRunning() ) {
			$this->_forward('control', 'controls');
		}
	}
	*/
	
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

    	$pageItems = array();
    	
    	// links on top
    	$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->preGetShareItems($provider, $location, $this));
    	// normal links
    	$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->getShareItems($provider, $location, $this));
    	// bottom links
		$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->postGetShareItems($provider, $location, $this));
		
		// filter out items (parental-control / hidden file / system dir)
		foreach ($pageItems as $key => $item) {
			if ( in_array(false, X_VlcShares_Plugins::broker()->filterShareItems($item, $provider, $this)) ) {
				unset($pageItems[$key]);
			}
		}
		
		
		X_VlcShares_Plugins::broker()->orderShareItems(&$pageItems, $provider,  $this);
		
		
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
			'label'		=>	X_Env::_('Back'),
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

    	$pageItems = array();

    	
    	
    	
    	
		// trigger for page creation
		X_VlcShares_Plugins::broker()->gen_afterPageBuild(&$pageItems, $this);
		
		
		return;
		
		X_Env::debug(__METHOD__);
		$request = $this->getRequest();

		$shareId = $request->getParam('shareId');
		$currentPath = base64_decode($request->getParam('url', ''));

		$qId = $request->getParam('qId');
		//$subId = $request->getParam('subId');
		
		$shares = $this->options->shares->toArray();
		$profiles = $this->options->profiles->toArray();

		
		if ( !$shares[$shareId] ) {
			throw new Zend_Controller_Action_Exception($this->translate->_("share_id_not_valid"));
		}
		
		// info sullo share
		$share = $shares[$shareId];
		
		$completePath = $share['path'].$currentPath;
		$filename = pathinfo($completePath, PATHINFO_FILENAME);
		$dirpath = pathinfo($completePath, PATHINFO_DIRNAME);
		
		$this->vlc->registerArg('source',"\"$completePath\"");

		$plgParams = array($completePath, $dirpath, $filename);
		$additionalProfiles = X_Env::triggerEvent(X_VlcShares::TRG_PROFILES_ADDITIONALS, $plgParams);
		
		foreach ($additionalProfiles as $plgAddProfiles) {
			$profiles = array_merge($profiles, $plgAddProfiles);
		}
		
		// la sostituzione avviene solo se il valore non e'
		// per un plugin
		$this->vlc->registerArg('profile', $profiles[$qId]['args']);
		
		$plgArgs = array($request, $completePath, $dirpath, $filename);
		
		$plgArgs = X_Env::triggerEvent(X_VlcShares::TRG_VLC_ARGS_SUBTITUTE, $plgArgs);
		foreach ($plgArgs as $plgRet) {
			foreach (@$plgRet as $sKey => $sValue) {
				$this->vlc->registerArg($sKey, $sValue);
			}
		}
		
		X_Env::triggerEvent(X_VlcShares::TRG_VLC_SPAWN_PRE, $this->vlc);
		$this->vlc->spawn();
		X_Env::triggerEvent(X_VlcShares::TRG_VLC_SPAWN_POST, $this->vlc);
		
		$plx = new X_Plx("VLCShares - $completePath", $this->translate->_("title_description"));
		
		$plgArgs[] = $this->vlc;
		
		$prePlxItems = X_Env::triggerEvent(X_VlcShares::TRG_STREAM_MENU_PRE, $plgArgs);
		foreach ( $prePlxItems as $plgOutput ) {
			if ( is_array($plgOutput) ) {
				foreach ($plgOutput as $item ) {
					$plx->addItem($item);
				}
			} elseif ($plgOutput instanceof X_Plx_Item ) { 
				$plx->addItem($plgOutput);
			}
		}
		
		$stream = null;
		$stream = @$profiles[$qId]['stream'];
		if ( is_null($stream) ) {
			$stream = $this->options->vlc->get('stream', "http://{$_SERVER['SERVER_ADDR']}:8081" );
		}
		
		$plx->addItem(new X_Plx_Item(X_Env::_('start_play'),$stream,X_Plx_Item::TYPE_VIDEO));
		
		$postPlxItems = X_Env::triggerEvent(X_VlcShares::TRG_STREAM_MENU_POST, $plgArgs);
		foreach ( $postPlxItems as $plgOutput ) {
			if ( is_array($plgOutput) ) {
				foreach ($plgOutput as $item ) {
					$plx->addItem($item);
				}
			} elseif ($plgOutput instanceof X_Plx_Item ) { 
				$plx->addItem($plgOutput);
			}
		}
		
		$echoArrayPlg = X_Env::triggerEvent(X_VlcShares::TRG_ENDPAGES_OUTPUT_FILTER_PLX, $plx );
		$echo = '';
		foreach ($echoArrayPlg as $plgOutput) {
			$echo .= $plgOutput;
		}
		if ( $echo != '' ) {
			echo $echo;
		} else {
    		header('Content-Type:text/plain');
			echo $plx;
		}
		$this->_helper->viewRenderer->setNoRender(true);
		
		
	}
}
