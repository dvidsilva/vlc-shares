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
		$view->view->coverflowEnabled = $this->config('coverflow.enabled', true);

	}
	
	/**
	 * Add the link for -manage-output-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_mobilerenderer_mlink'));
		$link->setTitle(X_Env::_('p_mobilerenderer_managetitle'))
			->setIcon('/images/mobilerenderer/logo.png')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'mobilerenderer'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}
	
}


