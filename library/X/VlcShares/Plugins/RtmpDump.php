<?php 


class X_VlcShares_Plugins_RtmpDump extends X_VlcShares_Plugins_Abstract {
	
	function __construct() {
		$this->setPriority('preSpawnVlc', 99)
			->setPriority('execute');
	}
	
	public function preSpawnVlc(X_Vlc $vlc, $provider, $location, Zend_Controller_Action $controller) {
		
		if ( !$this->helpers()->rtmpdump()->isEnabled() ) return;
		
		$source = $vlc->getArg('source');
		// remove quotes, if present
		$source = trim($source, '"');
		
		if ( X_Env::startWith($source, "rtmpdump://") ) {
			X_Debug::i("Source is a dummy uri for rtmpdump forwarding. Setting pipe");
			// parsing rtmpdump params from the uri and overriding the quiet param to be sure it's ok for the pipe
			$vlc->setPipe(X_RtmpDump::getInstance()->parseUri($source)->setQuiet(true)->setStreamPort('8081')/*->setLive(true)*/);
			$vlc->registerArg('source', '- --play-and-stop');
			$vlc->registerArg('profile', '');
			$vlc->registerArg('output', '');
			
			$this->setPriority('getStreamItems');
			
			// unregister output and profile plugin
			try {
				X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_Outputs');
				X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_Profiles');
				// unregister redirect control too
				X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_Controls');
			} catch ( Exception $e) {
				
			}
		}
		
	}
	
	public function getStreamItems($provider, $location, Zend_Controller_Action $controller) {

		X_Debug::i('Plugin triggered');
		
		$return = new X_Page_ItemList_PItem();
		
		
		$outputLink = "http://{%SERVER_NAME%}:8081/";
		$outputLink = str_replace(
			array(
				'{%SERVER_IP%}',
				'{%SERVER_NAME%}'
			),array(
				$_SERVER['SERVER_ADDR'],
				strstr($_SERVER['HTTP_HOST'], ':') ? strstr($_SERVER['HTTP_HOST'], ':') : $_SERVER['HTTP_HOST']
			), $outputLink
		);
		
		$item = new X_Page_Item_PItem($this->getId(), X_Env::_('p_outputs_gotostream'));
		$item->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
			->setIcon('/images/icons/play.png')
			->setLink($outputLink);
		$return->append($item);		
		

		$item = new X_Page_Item_PItem('controls-stop', X_Env::_('p_controls_stop'));
		$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setIcon('/images/icons/stop.png')
			->setLink(array(
				'controller'		=>	'controls',
				'action'	=>	'execute',
				'a'			=>	'stop',
				'pid'		=>	$this->getId(),
			), 'default', false);
		$return->append($item);
		
		
		return $return;
		
	}
	

	/**
	 * Execute the shutdown action 
	 * 
	 * @param X_Vlc $vlc
	 * @param string $pid
	 * @param string $action
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 */
	public function execute(X_Vlc $vlc, $pid, $action, Zend_Controller_Action $controller) {
		// the trigger isn't for this plugin
		//if ( $this->getId() != $pid ) return;
		
		// force rtmpstop always
		
		X_Debug::i("Plugin triggered for action {$action}");
		
		$param = $controller->getRequest()->getParam('param', null);
		
		if ( method_exists($this, "_action_$action") ) {
			$method = "_action_$action";
			$this->$method($vlc, $param);
		} else {
			X_Debug::e("Invalid action $action");
		}
		
		$controller->getRequest()->setControllerName('controls')->setActionName('control')->setDispatched(false);
		
	}	

	private function _action_stop(X_Vlc $vlc, $param) {
		$vlc->forceKill();
		X_RtmpDump::getInstance()->forceKill();
		sleep(1); // wait here so i will get "no vlc running" when i'll try later
	}
	
	
}
