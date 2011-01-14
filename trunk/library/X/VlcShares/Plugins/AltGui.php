<?php

require_once ('X/VlcShares/Plugins/Abstract.php');

/**
 * Enable interface for mobile devices
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_AltGui extends X_VlcShares_Plugins_Abstract {

	function __construct() {
		$this->setPriority('gen_beforePageBuild');
	}
	
	public function gen_beforePageBuild(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");

		$request = $controller->getRequest();
		$urlHelper = $controller->getHelper('url');


		$changeMap = array(
			//array(LAYOUT, VIEW, RESET)
			'manage/index' => array(true, true, false),
			'error/error' => array(true, true, false),
		);
		
		
		
		$controllerName = $request->getControllerName();
		$actionName = $request->getActionName();
		
		//die($controllerName);
		
		/* @var $view Zend_Controller_Action_Helper_ViewRenderer */
		$view = $controller->getHelper('viewRenderer');
		/* @var $layout Zend_Layout_Controller_Action_Helper_Layout */
		$layout = $controller->getHelper('layout');
		
		$key = "$controllerName/$actionName";
		
		if ( array_key_exists($key, $changeMap) ) {
			
			list($layoutC, $viewC, $resetC) = $changeMap[$key];
			
			if ( $resetC ) {
				$view->setViewSuffix('phtml');
				$layout->getLayoutInstance()->setLayout('layout', true);
			} else {
				if ( $layoutC) { 
					$layout->getLayoutInstance()->setLayout('altgui', true);
				}
				if ( $viewC) {
					$view->setViewSuffix('altgui.phtml');
				}
			}
		} else {
			return;
		}
	}
}


