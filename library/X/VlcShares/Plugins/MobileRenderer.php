<?php

require_once ('X/VlcShares/Plugins/Abstract.php');
require_once ('X/VlcShares/Plugins/ResolverDisplayableInterface.php');

/**
 * Enable interface for mobile devices
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_MobileRenderer extends X_VlcShares_Plugins_Abstract {

	function __construct() {
		$this->setPriority('gen_afterPageBuild')
		->setPriority('getIndexManageLinks');
	}
	
	public function gen_afterPageBuild(&$items, Zend_Controller_Action $controller) {
		// even if forced.enabled, don't build the page if the device is wiimc
		if ( $this->helpers()->devices()->isWiimc() || ( !((bool) $this->config('forced.enabled', false)) && !$this->helpers()->devices()->isAndroid() )) return;
		
		X_Debug::i("Plugin triggered");

		$request = $controller->getRequest();
		$urlHelper = $controller->getHelper('url');
		
		/* @var $view Zend_Controller_Action_Helper_ViewRenderer */
		$view = $controller->getHelper('viewRenderer');
		/* @var $layout Zend_Layout_Controller_Action_Helper_Layout */
		$layout = $controller->getHelper('layout');
		
		$view->setViewSuffix('mobile.phtml');
		$layout->getLayoutInstance()->setLayout('mobile', true);
		
		try {
			$providerObj = X_VlcShares_Plugins::broker()->getPlugins($request->getParam('p',''));
			$view->view->providerName = strtolower($providerObj->getId());
			if ( $providerObj instanceof X_VlcShares_Plugins_ResolverDisplayableInterface ) {
				// location in request obj are base64_encoded
				$view->view->location = $providerObj->resolveLocation(base64_decode($request->getParam('l', '')));
			}
			if ( $providerObj instanceof X_VlcShares_Plugins_ResolverInterface ) {
				// location in request obj are base64_encoded
				$view->view->locationRaw = $providerObj->resolveLocation(base64_decode($request->getParam('l', '')));
				$view->view->parentLocation = $providerObj->getParentLocation(base64_decode($request->getParam('l', '')));
			}
		} catch (Exception $e) {
			//die('No provider');
			X_Debug::i('No provider O_o');
		} 
		
		// set some vars for view
		$view->view->provider = $request->getParam('p','');
		$view->view->items = $items;
		$view->view->actionName = $request->getActionName();
		$view->view->controllerName = $request->getControllerName();

	}
	
	/**
	 * Add the link for -manage-output-
	 * @param Zend_Controller_Action $this
	 * @return array The format of the array should be:
	 * 		array(
	 * 			array(
	 * 				'title' => ITEM TITLE,
	 * 				'label' => ITEM LABEL,
	 * 				'link'	=> HREF,
	 * 				'highlight'	=> true|false,
	 * 				'icon'	=> ICON_HREF,
	 * 				'subinfos' => array(INFO, INFO, INFO)
	 * 			), ...
	 * 		)
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$urlHelper = $controller->getHelper('url');
		
		return array(
			array(
				'title'		=>	X_Env::_('p_mobilerenderer_managetitle'),
				'label'		=>	X_Env::_('p_mobilerenderer_mlink'),
				'link'		=>	$urlHelper->url(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'mobilerenderer'
				)),
				'icon'		=>	'/images/mobilerenderer/logo.png',
				'subinfos'	=> array()
			),
		);
	}
	
}


