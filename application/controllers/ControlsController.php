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
require_once 'X/Plx.php';
require_once 'X/Plx/Item.php';

class ControlsController extends X_Controller_Action {
	
	/**
	 * 
	 * @var X_Vlc
	 */
	protected $vlc = null;
	
	public function init() {
		parent::init();
		X_Env::debug(__METHOD__);
		
		$this->vlc = new X_Vlc($this->options->vlc);
		
	}
	
	public function preDispatch() {
		
		X_Env::debug(__METHOD__);
		
		// devo controllare se vlc e' attivo, ma non posso farlo 
		// tramite pid su windows
		if ( !$this->vlc->isRunning() ) {
			$this->_forward('index', 'browse');
		}
	}
	
	
	public function indexAction() {
		X_Env::debug(__METHOD__);
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'WiiMC') !== false ) {
			// wiimc 1.0.5 e inferiori nn accetta redirect
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
		
		X_Env::debug(__METHOD__);
		
		$plx = new X_Plx("VLCShares - ".X_Env::_('playback_controls'), X_Env::_("title_description"));
		
		$thisPage = X_Env::routeLink('controls', 'control');
		
		/*
		$title = $this->vlc->getCurrentName();
		$now = X_Env::formatTime($this->vlc->getCurrentTime());
		$total = X_Env::formatTime($this->vlc->getTotalTime());
		*/
		
		$prePlxItems = X_Env::triggerEvent(X_VlcShares::TRG_CONTROLS_MENU_PRE, $this->vlc);
		foreach ( $prePlxItems as $plgOutput ) {
			if ( is_array($plgOutput) ) {
				foreach ($plgOutput as $item ) {
					$plx->addItem($item);
				}
			} elseif ($plgOutput instanceof X_Plx_Item ) { 
				$plx->addItem($plgOutput);
			}
		}

		// back to stream workaround
		$stream = $this->options->vlc->get('stream', "http://{$_SERVER['SERVER_ADDR']}:8081" );
		$plx->addItem(new X_Plx_Item(X_Env::_('resume'),$stream,X_Plx_Item::TYPE_VIDEO));
		
		// item standard
		$plx->addItem(new X_Plx_Item(X_Env::_("pause"),X_Env::routeLink('controls', 'pause')));
		$plx->addItem(new X_Plx_Item(X_Env::_("stop"),X_Env::routeLink('controls', 'shutdown')));
		
		
		$postPlxItems = X_Env::triggerEvent(X_VlcShares::TRG_CONTROLS_MENU_POST, $this->vlc);
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

