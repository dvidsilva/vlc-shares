<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/VlcShares/Plugins/ResolverInterface.php';
require_once 'Zend/Config.php';

/**
 * Filter out invalid files extension from the list of items for FileSystem provider
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_RedirectControls extends X_VlcShares_Plugins_Abstract {

	private $redirectCond_To = array(
		'index/collections',
		'browse/share',
		'browse/mode',
		'browse/selection',
		'browse/stream',
	);

	private $redirectCond_Away = array(
		'controls/control',
		'controls/execute'
	);
	
	function __construct() {
		$this->setPriority('gen_beforePageBuild');
	}
	
	/**
	 * Redirect to controls if vlc is running
	 * @param Zend_Controller_Action $controller
	 */
	public function gen_beforePageBuild(Zend_Controller_Action $controller ) {
		
		/*
		$vlc = X_Vlc::getLastInstance();
		
		if ( $vlc === null ) {
			X_Debug::i("No vlc instance");
			return;
		}
		*/
		
		$controllerName = $controller->getRequest()->getControllerName();
		$actionName = $controller->getRequest()->getActionName();
		
		$query = "$controllerName/$actionName";
		
		X_Debug::i("Plugin triggered for: {$query}");

		//$isRunning = $vlc->isRunning();
		$isRunning = X_Streamer::i()->isStreaming();
	
		if ( array_search($query, $this->redirectCond_To) !== false && $isRunning ) {
			$controller->getRequest()->setControllerName('controls')->setActionName('control')->setDispatched(false);
			X_Debug::i("Redirect to controls/control");
		} elseif ( array_search($query, $this->redirectCond_Away) !== false && !$isRunning ) {
			X_Debug::i("Redirect to index/collections");
			$controller->getRequest()->setControllerName('index')->setActionName('collections')->setDispatched(false);
		} else {
			X_Debug::i("No redirection: vlc is running? " . ($isRunning ? 'Yes' : 'No'));
		}
	}
}	

