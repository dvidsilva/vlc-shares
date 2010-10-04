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
		
		$this->vlc = new X_Vlc($this->options->vlc);
		
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
			$redirector->gotoSimpleAndExit('pcstream');
		}
	}
	
	public function pcstreamAction() {
		X_Env::debug(__METHOD__);
		// Niente controllo flusso, se lo streaming non e' attivo
		if ( !$this->vlc->isRunning() ) {
	    	/**
	    	 * @var $redirector Zend_Controller_Action_Helper_Redirector
	    	 */
        	$redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
			$redirector->gotoSimpleAndExit('index', 'index');
		} else {
			$this->view->showVideo = $this->options->pcstream->get('showVideo', false);
			$this->view->stream = $this->options->vlc->get('stream', "http://{$_SERVER['SERVER_ADDR']}:8081" );
			$this->view->pause = X_Env::routeLink('controls', 'pause', array('ajax' => true));
			$this->view->stop = X_Env::routeLink('controls', 'shutdown', array('ajax' => true));
			$this->view->seek = X_Env::routeLink('controls', 'seek', array('ajax' => true, 'time' => ''));
		}
	}

	public function controlAction() {
		
		
		$request = $this->getRequest();
		
		//X_VlcShares_Plugins::broker()->gen_preProviderSelection($this);
		
		/*
		$provider = $request->getParam('p', false);
		if ( $provider === false || !X_VlcShares_Plugins::broker()->isRegistered($provider) ) {
			throw new Exception("Invalid provider");
		}
		$location = base64_decode($request->getParam('l', ''));
		*/

    	$pageItems = array();
    	
    	// links on top
    	$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->preGetControlItems($this));
    	
    	// add a separator
    	$pageItems[] = array(
			'label'		=>	X_Env::_('_____options_separator_____'),
			'link'		=>	X_Env::completeUrl(
				$this->_helper->url->url(array(
					'controller'	=> 'controls',
					'action'		=>	'control', // i want to be sure that fake buttons forward to main action to avoid multiple seek in time
					'pid'			=>	null, // no pid auto url
					'a'				=>	null, // no a for auto url
					'param'			=>	null,
				), 'default', false)
			)
		);
    	
    	// normal links
    	$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->getControlItems($this));
    	// bottom links
		$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->postGetControlItems($this));
		
		
		// trigger for page creation
		X_VlcShares_Plugins::broker()->gen_afterPageBuild(&$pageItems, $this);
		

	}
	
	public function executeAction() {
		
		
		$request = $this->getRequest();
		
		//X_VlcShares_Plugins::broker()->gen_preProviderSelection($this);
		
		/*
		$provider = $request->getParam('p', false);
		if ( $provider === false || !X_VlcShares_Plugins::broker()->isRegistered($provider) ) {
			throw new Exception("Invalid provider");
		}
		$location = base64_decode($request->getParam('l', ''));
		*/
		
		$pid = $request->getParam('pid', false);
		$a = $request->getParam('a', false);
		
		X_VlcShares_Plugins::broker()->preExecute($this->vlc, $pid, $a, $this);
		X_VlcShares_Plugins::broker()->execute($this->vlc, $pid, $a, $this);
		X_VlcShares_Plugins::broker()->postExecute($this->vlc, $pid, $a, $this);
		
		
    	$pageItems = array();
    	
    	// links on top
    	$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->preGetExecuteItems($pid, $a, $this));
    	
    	// add a separator
    	$pageItems[] = array(
			'label'		=>	X_Env::_('_____options_separator_____'),
			'link'		=>	X_Env::completeUrl(
				$this->_helper->url->url(array(
					'controller'	=> 'controls',
					'action'		=>	'control', // i want to be sure that fake buttons forward to main action to avoid multiple seek in time
					'pid'			=>	null, // no pid auto url
					'a'				=>	null, // no a for auto url
					'param'			=>	null,
				), 'default', false)
			)
		);
    	
    	// normal links
    	$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->getExecuteItems($pid, $a, $this));
    	// bottom links
		$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->postGetExecuteItems($pid, $a, $this));
		
		
		// trigger for page creation
		X_VlcShares_Plugins::broker()->gen_afterPageBuild(&$pageItems, $this);
		
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

