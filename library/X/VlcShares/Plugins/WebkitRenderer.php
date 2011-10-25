<?php

require_once ('X/VlcShares/Plugins/Abstract.php');
require_once ('X/VlcShares/Plugins/ResolverDisplayableInterface.php');

/**
 * Enable interface for mobile devices
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_WebkitRenderer extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_RendererInterface {

	function __construct() {
		$this->setPriority('gen_afterPageBuild')
		/*->setPriority('getIndexManageLinks')*/;
	}
	
	public function gen_afterPageBuild(X_Page_ItemList_PItem $items, Zend_Controller_Action $controller) {
		if ( !$this->isDefaultRenderer() ) { 
			// to be used, this 
			return;
		}
		
		X_Debug::i("Plugin triggered");

		$request = $controller->getRequest();
		$urlHelper = $controller->getHelper('url');

		
		if ( $request instanceof Zend_Controller_Request_Http ) {
			if ( $request->isXmlHttpRequest() || $request->getParam('webkit:json', false) ) {
				$this->dispatchRequest($request, $items, $controller);
			} else {
				$this->showMainPage($request, $controller);
			}
		} else {
			X_Debug::f("Request isn't HTTP");
		}

	}
	
	
	protected function showMainPage(Zend_Controller_Request_Http $request, Zend_Controller_Action $controller ) {
		
		/* @var $view Zend_Controller_Action_Helper_ViewRenderer */
		$view = $controller->getHelper('viewRenderer');
		/* @var $layout Zend_Layout_Controller_Action_Helper_Layout */
		$layout = $controller->getHelper('layout');
		
		$view->setViewSuffix('webkit.phtml');
		$layout->getLayoutInstance()->setLayout('webkit', true);
		
	}
	
	protected function dispatchRequest(Zend_Controller_Request_Http $request, X_Page_ItemList_PItem $items, Zend_Controller_Action $controller) {

		/* @var $view Zend_Controller_Action_Helper_ViewRenderer */
		$view = $controller->getHelper('viewRenderer');
		/* @var $layout Zend_Layout_Controller_Action_Helper_Layout */
		$layout = $controller->getHelper('layout');
		
		try {
			$view->setNoRender(true);
			$layout->disableLayout();
		} catch (Exception $e) {
			X_Debug::e("Layout or View not enabled: ".$e->getMessage());
		}
		
		$result = array();
		
		
		$actionName = $request->getActionName();
		$controllerName = $request->getControllerName();
		
		$result['controller'] = $controllerName;
		$result['action'] = $actionName;
		$result['success'] = true;
		$result['items'] = array();
		
		/* @var $urlHelper Zend_Controller_Action_Helper_Url */
		$urlHelper = $controller->getHelper('url');
		
		$skipMethod = array(
			'getCustom',
			'getLinkParam',
			'getLinkAction',
			'getLinkController'
		);
		
		foreach ($items->getItems() as $itemId => $item) {
			/* @var $item X_Page_Item_PItem */
			$aItem = array();
			$methods = get_class_methods(get_class($item));
			
			foreach ($methods as $method ) {
				if ( array_search($method, $skipMethod) !== false ) continue;
				
				if ( $method == "getIcon" ) {
					$aItem['icon'] = $request->getBaseUrl().$item->getIcon();
				} elseif ( X_Env::startWith($method, 'get') ) {
					$aItem[lcfirst(substr($method, 3))] = $item->$method();
				} elseif ( X_Env::startWith($method, 'is' )) {
					$aItem[lcfirst(substr($method, 2))] = $item->$method();
				}
			}
			
			$result['items'][] = $aItem;
		}
		
		/* @var $jsonHelper Zend_Controller_Action_Helper_Json */
		$jsonHelper = $controller->getHelper('Json');
		
		$jsonHelper->direct($result, true, false);
		
		
	}
	
	
	/**
	 * Add the link for -manage-output-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_webkitrenderer_mlink'));
		$link->setTitle(X_Env::_('p_webkitrenderer_managetitle'))
			->setIcon('/images/webkitrenderer/logo.png')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'webkitrenderer'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}
	
	/*====================
	 RendererInterface
	 =====================*/
	function getName() {
		return X_Env::_('p_webkitrenderer_interfacename');
	}
	
	function getDescription() {
		return X_Env::_('p_webkitrenderer_interfacedescription');
	}
	
	function getRequiredFeatures() {
		return array(
			self::FEATURES_AJAX,
			self::FEATURES_HTML5,
			self::FEATURES_WEBKIT,
			self::FEATURES_JS,
			self::FEATURES_IMAGES,
			self::FEATURES_ADWORDS,
			self::FEATURES_STANDALONEPLAYER,
			self::FEATURES_FLASH
		);
	}
	
	// bad hack for autooptions
	private $_forceRendering = false;
	public function setDefaultRenderer($force = true) {
		$this->_forceRendering = $force;
	}
	public function isDefaultRenderer() {
		return $this->_forceRendering;
	}
	
	
	
	
}


