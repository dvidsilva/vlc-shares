<?php

/**
 * BrowseController
 * 
 * @author Francesco Capozzo
 * @version 0.3.2
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
	/*
	public function init() {
		parent::init();
		X_Env::debug(__METHOD__);
		
		$this->vlc = new X_Vlc($this->options->vlc);
		
	}
	
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
		
		// stop here execution
		return;

	}
	
	public function fileAction() {
		
		X_Env::debug(__METHOD__);
		
		$request = $this->getRequest();

		$shareId = $request->getParam('shareId');
		$currentPath = base64_decode($request->getParam('url', ''));

		$shares = $this->options->shares->toArray();
		
		
		if ( !$shares[$shareId] ) {
			throw new Zend_Controller_Action_Exception($this->translate->_("share_id_not_valid"));
		}
		
		// info sullo share
		$share = $shares[$shareId];
		
		// escludo il nome del file
		$completePath = $share['path'].$currentPath;
		$completeDir = $share['path'].dirname($currentPath);
		$filename = pathinfo($completePath, PATHINFO_FILENAME);


		$plx = new X_Plx("VLCShares - $completePath", X_Env::_("title_description") );
		
		$plgParams = array($completePath, $completeDir, $filename);
		$prePlxItems = X_Env::triggerEvent(X_VlcShares::TRG_PROFILES_TRAVERSAL_PRE, $plgParams);
		foreach ( $prePlxItems as $plgOutput ) {
			if ( is_array($plgOutput) ) {
				foreach ($plgOutput as $item ) {
					$plx->addItem($item);
				}
			} elseif ($plgOutput instanceof X_Plx_Item ) { 
				$plx->addItem($plgOutput);
			}
		}

		$additionalMode = X_Env::triggerEvent(X_VlcShares::TRG_MODE_ADDITIONALS, $plgParams);
		$additionalProfiles = X_Env::triggerEvent(X_VlcShares::TRG_PROFILES_ADDITIONALS, $plgParams);

		try {
			$vlcProfiles = $this->options->profiles->toArray();
		} catch (Exception $e ) { $vlcProfiles = array(); }
		
		// array(array(array(ProfileKey => ProfileValue)))
		// L'ultimo array ha lo stesso formato dei profiles nel file config
		foreach ($additionalProfiles as $plgAddProfiles) {
			$vlcProfiles = array_merge($vlcProfiles, $plgAddProfiles);
		}
		
		foreach ($vlcProfiles as $pKey => $pValue) {
			$plgParams2 = $plgParams;
			$plgParams2[] = $pKey;
			
			// se anche solo 1 dei plugin esclude il profilo,
			// viene ignorato
			$results = X_Env::triggerEvent(X_VlcShares::TRG_PROFILES_TRAVERSAL, $plgParams2);
			foreach ($results as $result)
				if ( !$result )
					continue 2; // salto al padre foreach

			$baseArgv = array(
				'qId' => $pKey,
				'shareId' => $shareId,
				'url' => base64_encode($currentPath)
			);
					
			$plx->addItem(new X_Plx_Item(
				X_Env::_($pValue['name']),
				X_Env::routeLink('browse', 'stream', $baseArgv)
			));
			
			
			foreach ($additionalMode as $plgMode) {
				foreach ($plgMode as $modeName => $modeArgv) {
					
					$completeArgv = array_merge($baseArgv, $modeArgv);
					
					$plx->addItem(new X_Plx_Item(
						"  '--- $modeName",
						X_Env::routeLink('browse', 'stream', $completeArgv)
					));
				}
			}
		}
		
		// Chiamo il trigger, no input e output X_Plx_Item o array(X_Plx_Item)
		$postPlxItems = X_Env::triggerEvent(X_VlcShares::TRG_PROFILES_TRAVERSAL_POST, $plgParams);
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

	public function streamAction() {
		
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
