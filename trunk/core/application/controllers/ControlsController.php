<?php

/**
 * ControlsController
 * 
 * @author
 * @version 
 */

require_once 'X/Controller/Action.php';
require_once 'X/Vlc.php';
require_once 'X/Env.php';
require_once 'X/VlcShares.php';

class ControlsController extends X_Controller_Action {
	
	/**
	 * 
	 * @var X_Vlc
	 */
	protected $vlc = null;
	
	public function init() {
		parent::init();
		
		$this->vlc = X_Vlc::getLastInstance();
		// bootstrap failed
		if ( is_null($this->vlc) ) {
			$this->vlc = new X_Vlc($this->options->vlc);
			X_VlcShares_Plugins::helpers()->streamer()->register(new X_Streamer_Engine_Vlc($this->vlc));
		}
				
	}
	
	
	public function indexAction() {
		
		if ( X_VlcShares_Plugins::helpers()->devices()->isWiimc() ) {
			// wiimc 1.0.9 e inferiori nn accetta redirect
			$this->_forward('control');
		} else {
	    	/**
	    	 * @var $redirector Zend_Controller_Action_Helper_Redirector
	    	 */
        	$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
			$redirector->gotoSimpleAndExit('control');
		}
	}

	public function controlAction() {
		
		$request = $this->getRequest();

		$engineId = X_Streamer::i()->getStreamingEngineId();
		$engine = X_VlcShares_Plugins::helpers()->streamer()->get($engineId);
		
    	$pageItems = new X_Page_ItemList_PItem();
    	
    	// links on top
    	$pageItems->merge(X_VlcShares_Plugins::broker()->preGetControlItems($engine, $this));
    	
    	// add separator between play items and options items
    	$separator = new X_Page_Item_PItem('core-separator', X_Env::_('_____options_separator_____'));
    	$separator->setType(X_Page_Item_PItem::TYPE_ELEMENT)
    		->setLink(array(
					'controller'	=> 'controls',
					'action'		=>	'control', // i want to be sure that fake buttons forward to main action to avoid multiple seek in time
					'pid'			=>	null, // no pid auto url
					'a'				=>	null, // no a for auto url
					'param'			=>	null,
				), 'default', false);
    	$pageItems->append($separator);
    	
    	// normal links
    	$pageItems->merge(X_VlcShares_Plugins::broker()->getControlItems($engine, $this));
    	// bottom links
		$pageItems->merge(X_VlcShares_Plugins::broker()->postGetControlItems($engine, $this));
		
		// filter out items (parental-control / hidden file / system dir / custom controls)
		foreach ($pageItems->getItems() as $key => $item) {
			$results = X_VlcShares_Plugins::broker()->filterControlItems($item, $engine, $this);
			if ( $results != null && in_array(false, $results) ) {
				$pageItems->remove($item);
			}
		}
		
		
		// trigger for page creation
		X_VlcShares_Plugins::broker()->gen_afterPageBuild($pageItems, $this);
		

	}
	
	public function executeAction() {
		
		
		$request = $this->getRequest();
		
		//X_VlcShares_Plugins::broker()->gen_preProviderSelection($this);
		
		/*
		$provider = $request->getParam('p', false);
		if ( $provider === false || !X_VlcShares_Plugins::broker()->isRegistered($provider) ) {
			throw new Exception("Invalid provider");
		}
		$location = X_Env::decode($request->getParam('l', ''));
		*/
		
		$pid = $request->getParam('pid', false);
		$a = $request->getParam('a', false);
		
		$engineId = X_Streamer::i()->getStreamingEngineId();
		$engine = X_VlcShares_Plugins::helpers()->streamer()->get($engineId);
		
		X_VlcShares_Plugins::broker()->preExecute($engine, $pid, $a, $this);
		X_VlcShares_Plugins::broker()->execute($engine, $pid, $a, $this);
		X_VlcShares_Plugins::broker()->postExecute($engine, $pid, $a, $this);
		
    	$pageItems = new X_Page_ItemList_PItem();
    	
    	$done = new X_Page_Item_PItem('core-opdone', X_Env::_('controls_done'));
    	$done->setCustom('vlc_still_alive', $this->vlc->isRunning())
    		->setType(X_Page_Item_PItem::TYPE_ELEMENT)
    		->setLink(array(
					'controller'	=> 'controls',
					'action'		=>	'control', // i want to be sure that fake buttons forward to main action to avoid multiple seek in time
					'pid'			=>	null, // no pid auto url
					'a'				=>	null, // no a for auto url
					'param'			=>	null,
				), 'default', false);
    	$pageItems->append($done);
    	
    	
    	// links on top
    	$pageItems->merge(X_VlcShares_Plugins::broker()->preGetExecuteItems($pid, $a, $this));
    	
    	// add separator between play items and options items
    	$separator = new X_Page_Item_PItem('core-separator', X_Env::_('_____options_separator_____'));
    	$separator->setType(X_Page_Item_PItem::TYPE_ELEMENT)
    		->setLink(array(
					'controller'	=> 'controls',
					'action'		=>	'control', // i want to be sure that fake buttons forward to main action to avoid multiple seek in time
					'pid'			=>	null, // no pid auto url
					'a'				=>	null, // no a for auto url
					'param'			=>	null,
				), 'default', false);
    	$pageItems->append($separator);
    	    	
    	// normal links
    	$pageItems->merge(X_VlcShares_Plugins::broker()->getExecuteItems($pid, $a, $this));
    	// bottom links
		$pageItems->merge(X_VlcShares_Plugins::broker()->postGetExecuteItems($pid, $a, $this));
		
		// trigger for page creation
		X_VlcShares_Plugins::broker()->gen_afterPageBuild($pageItems, $this);
		
	}
	
	public function pauseAction() {
		X_Env::debug(__METHOD__);
		$this->vlc->pause();
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'WiiMC') !== false ) {
			// wiimc 1.0.5 e inferiori nn accetta redirect
			$this->_forward('control');
		} else {
			$isAjax = $this->getRequest()->getParam('ajax', false);
			if ( $isAjax ) {
				$this->_forward('status');
			} else {
	        	$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
				$redirector->gotoSimpleAndExit('control');
			}
		}
	}
	
	public function stopAction() {
		X_Env::debug(__METHOD__);
		$this->vlc->stop();
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'WiiMC') !== false ) {
			// wiimc 1.0.5 e inferiori nn accetta redirect
			$this->_forward('control');
		} else {
			$isAjax = $this->getRequest()->getParam('ajax', false);
			if ( $isAjax ) {
				$this->_forward('status');
			} else {
	        	$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
				$redirector->gotoSimpleAndExit('control');
			}
		}
	}
	
	public function seekAction() {
		X_Env::debug(__METHOD__);
		$request = $this->getRequest();
		$time = (int) $request->getParam('time', 5*60);
		$relative = (bool) $request->getParam('time', true);
		$this->vlc->seek($time, $relative);
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'WiiMC') !== false ) {
			// wiimc 1.0.5 e inferiori nn accetta redirect
			$this->_forward('control');
		} else {
			$isAjax = $this->getRequest()->getParam('ajax', false);
			if ( $isAjax ) {
				$this->_forward('status');
			} else {
	        	$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
				$redirector->gotoSimpleAndExit('control');
			}
		}
	}

	public function shutdownAction() {
		X_Env::debug(__METHOD__);
		$this->vlc->forceKill();	
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'WiiMC') !== false ) {
			// wiimc 1.0.5 e inferiori nn accetta redirect
			$this->_forward('index', 'index');
		} else {
			$isAjax = $this->getRequest()->getParam('ajax', false);
			if ( $isAjax ) {
				$this->_forward('status');
			} else {
	        	$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
				$redirector->gotoSimpleAndExit('control');
			}
		}
	} 
	
	public function statusAction() {
		X_Env::debug(__METHOD__);
		//$this->vlc->getCurrentName();
		$this->_helper->viewRenderer->setNoRender(true);
		
	}
	
	public function customAction() {
		X_Env::debug(__METHOD__);
		
		// azioni compiute dai plugin
	}
}

