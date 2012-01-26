<?php 

class UpnpController extends X_Controller_Action {
	
	/**
	 * 
	 * Ref to X_VlcShares_Plugins_UpnpRenderer
	 * @var X_VlcShares_Plugins_UpnpRenderer
	 */
	private $plugin = null;
	
    public function init()
    {
        parent::init();
		if ( !X_VlcShares_Plugins::broker()->isRegistered('upnprenderer') ) {
			throw new Exception(X_Env::_('err_pluginnotregistered') . ": upnprenderer");
		} else {
			$this->plugin = X_VlcShares_Plugins::broker()->getPlugins('upnprenderer');
		}
    }
	
	
	function manifestAction() {
		
		$file = $this->getRequest()->getParam('file');
		
		X_Debug::i("Manifest required: $file");

		$this->_helper->viewRenderer->setScriptAction($file);
		
		$this->_helper->layout->disableLayout();
		$this->view->xmldef = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
		
		$this->getResponse()->setHeader('Content-Type', 'text/xml');
		
	}
	
	function indexAction() {
		
		$thread = X_Threads_Manager::instance()->getMonitor()->getThread('upnp-announcer');
		
		$this->view->thread = $thread;
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
		
	}
	
	function resumeAction() {
		
		$thread = X_Threads_Manager::instance()->getMonitor()->getThread('upnp-announcer');
		if ( $thread->getState() != X_Threads_Thread_Info::RUNNING ) {
			if ( X_Threads_Manager::instance()->getMessenger()->hasMessage($thread) ) {
				X_Threads_Manager::instance()->getMessenger()->clearQueue($thread);
			}
			X_Threads_Manager::instance()->appendJob('X_Upnp_Announcer', array(
				'url' => "http://{$this->plugin->config('server.ip', '192.168.1.8')}/vlc-shares/upnp/manifest/file/service-desc",
				'cooldown' => "5",
				//'bind' => '127.0.0.1',
				'bind' => $this->plugin->config('bind.interface', '0'),
			), 'upnp-announcer');
		}
		
		$this->_helper->redirector('index');
	}
	
	function stopAction() {
		
		$thread = X_Threads_Manager::instance()->getMonitor()->getThread('upnp-announcer');
		if ( $thread->getState() != X_Threads_Thread_Info::STOPPED ) {
			X_Threads_Manager::instance()->halt($thread);
		}
		
		$this->_helper->redirector('index');
	}
	
	function controlAction() {
		
		$this->getResponse()->setHeader("Content-Type" , "text/xml; charset=UTF-8");
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		
		// control point for Upnp Devices. This url is referenced by service-desc manifest action
		//X_Debug::i("CONTROL ACTION REQUIRED!!!");
		$requestRaw = file_get_contents('php://input');
		
		X_Debug::i("Raw request: $requestRaw");
		
		$parsed = X_Upnp::parseUPnPRequest($requestRaw);
		
		X_Debug::i("SOAP Request: ".print_r($parsed, true));
		
		// define a one time control flag, this will
		// be catched by UpnpPlugin to force rendering and
		// ignore DevicesPlugin settings
		if ( !defined('_X_UPNP_ORIGINAL_REQUEST_UPNP') ) {
			define('_X_UPNP_ORIGINAL_REQUEST_UPNP', true);
		}

		$this->plugin->setSOAPRequest($parsed);
		
		//$case = "{$parsed['action']}/{$parsed['browseflag']}";

		
		// if browseflag == BrowseMetadata I have to give back only
		// basic information about the container
		
		// if browseflag == BrowseDirectChildren I have to give back children
		
		
		
		if ( $parsed['action'] == 'browse' ) {
			
			// delegate rendering and items creation to normal LVL1 API
			if ( $parsed['objectid'] == '0' || $parsed['objectid'] == '-1' ) {
				
				$this->_helper->actionStack->actionToStack('collections', 'index');
				
				
			} else {
				// need to parse the request
				$objectId = $parsed['objectid'];

				/*
				
				// decode ObjectId
				$requestDecoded = X_Env::decode($objectId);
				
				$args = array();
				
				parse_str($requestDecoded, $args);
				
				// get the controller
				if ( isset($args['controller']) ) {
					$controller = $args['controller'];
					unset($args['controller']);
				} else {
					$controller = 'index';
				}

				// get the action
				if ( isset($args['action']) ) {
					$action = $args['action'];
					unset($args['action']);
				} else {
					$action = 'collections';
				}
				
				// get the module (for vlcs 0.6/2)
				if ( isset($args['module']) ) {
					$module = $args['module'];
					unset($args['module']);
				} else {
					$module = 'default';
				}
				
				*/
				
				// normal objectid format is:
				// controller/action/provider/location#/ca1key/ca1value/ca2key/ca2value/....
				
				// first split main string part from customs args
				@list($mainArgs, $customArgs) = @explode('#', $objectId, 2);

				// split main params
				@list($controller, $action, $provider, $location) = @explode('/', $mainArgs, 4);
				
				$args = array();
				// check if custom args and populate $args
				if ( $customArgs ) {
					// explode all
					$exploded = explode('/', ltrim($customArgs, '/'));
					
					// params are:
					//		key = 2k
					//		values = 2k + 1
					// the loop automatically skips key-only pair
					for ( $i = 0; ($i + 1) < count($exploded); $i = $i + 2 ) {
						$args[$exploded[$i]] = $exploded[($i + 1)]; 
					}
				}
				
				// provider and location in main params overwrite the same in customs (if any)
				
				if ( $provider && $provider != 'null' ) $args['p'] = $provider;
				if ( $location ) $args['l'] = $location; 
				
				X_Debug::i("Proxying request to {$controller}/{$action} with params: ".print_r($args, true));
				
				// proxy request to normal LVL1 API
				$this->_helper->actionStack->actionToStack($action, $controller, 'default', $args);
				
			}
			
		} // else ignore request
		
		
	}
	
	
	
	function eventAction() {
		
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		
	}

	
}
