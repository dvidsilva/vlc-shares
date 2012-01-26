<?php

/**
 *
 * Render the response as an UPNP one 
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_UpnpRenderer extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_RendererInterface {

	function __construct() {
		$this->setPriority('gen_afterPageBuild')  
		->setPriority('gen_beforePageBuild', 3) // trigger after "device" but before other renderers
		->setPriority('getIndexManageLinks');
		
		X_Upnp::setDeviceName('Bresaola');
	}
	
	/**
	 * Show an wiimc compatible error page in plx format
	 */
	function gen_beforePageBuild(Zend_Controller_Action $controller) {
		if ( !$this->isDefaultRenderer() ) {
			if ( defined('_X_UPNP_ORIGINAL_REQUEST_UPNP') ) {
				// disable all renderers
				foreach (X_VlcShares_Plugins::broker()->getPlugins() as $pluginKey => $pluginObj) {
					if ( $pluginObj instanceof X_VlcShares_Plugins_RendererInterface ) {
						/* @var $pluginObj X_VlcShares_Plugins_RendererInterface */
						$pluginObj->setDefaultRenderer(false);
					}
				}
				$this->setDefaultRenderer(true);
			}
		}
	}
	
	public function gen_afterPageBuild(X_Page_ItemList_PItem $list, Zend_Controller_Action $controller) {
		
		if ( !$this->isDefaultRenderer() ) return;
		
		X_Debug::i("Plugin triggered");

		$request = $controller->getRequest();
		
		$responseType = 'u:BrowseResponse';
		$num = count($list->getItems());
		
		
		if ( $this->request['browseflag'] == 'BrowseMetadata') {
			
			$parentID = $this->_getParent($controller->getRequest());
			
			$item = new X_Page_Item_PItem('fake-item', "Container");
			
			$item->setLink(array_merge(array(
				'controller' => $controller->getRequest()->getControllerName(),
				'action' => $controller->getRequest()->getActionName()
			), $controller->getRequest()->getParams()));
			$item->setDescription("Fake description");
			
			$didl = X_Upnp::createMetaDIDL($item, $parentID, $num, 
				$controller->getRequest()->getControllerName(),
				$controller->getRequest()->getActionName(),
				$controller->getRequest()->getParam('p', 'null')
			);
			
		} elseif ( $this->request['browseflag'] == 'BrowseDirectChildren') {

			$parentID = $this->request['objectid'];
			
			$didl = X_Upnp::createDIDL($list->getItems(), $parentID, $num, 
				$controller->getRequest()->getControllerName(),
				$controller->getRequest()->getActionName(),
				$controller->getRequest()->getParam('p', 'null')
			);

		}
		
		$xmlDIDL = $didl->saveXML();
		
		X_Debug::i("DIDL response: $xmlDIDL");
		
		// Build SOAP-XML reply from DIDL-XML and send it to upnp device
		$domSOAP = X_Upnp::createSOAPEnvelope($xmlDIDL, $num, $num, $responseType, $parentID);
		
		$soapXML = $domSOAP->saveXML();
		
		
		// turn off viewRenderer and Layout, add Content-Type and set response body
		$this->_render($soapXML, $controller);
	}
	
	/**
	 * Add the link for -manage-upnp-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		return X_VlcShares_Plugins_Utils::getIndexManageEntryList($this->getId(), null, null, null, array(
			'controller' => 'upnp',
			'action' => 'index'
		));
		
	}
	
	
	/**
	 * Send to output plx playlist
	 * @param X_Plx $plx
	 */
	private function _render($soap, Zend_Controller_Action $controller) {
		$this->_disableRendering($controller);
		
		$controller->getResponse()->setHeader('Content-type', 'text/xml', true);
		$controller->getResponse()->setBody($soap);
		
		return $soap;
	}
	
	/**
	 * Disable layout and viewRenderer
	 * @param Zend_Controller_Action $controller
	 */
	private function _disableRendering(Zend_Controller_Action $controller) {
		try {
			$controller->getHelper('viewRenderer')->setNoRender(true);
			// disableLayout must be called at the end
			// i don't know if layout is enabled
			// and maybe an exception is raised if
			// i call e disableLayout without layout active
			$controller->getHelper('layout')->disableLayout();
		} catch (Exception $e) {
			X_Debug::w("Unable to disable viewRenderer or Layout: {$e->getMessage()}");
		}
	}
	
	private function _getParent(Zend_Controller_Request_Http $request) {
		
		$controller = $request->getControllerName();
		$action = $request->getActionName();
		
		if ( $controller == 'index' && $action == 'collections' ) {
			// we don't need to know anything: we are in the root
			return '0';
		}
		
		
		if ( $controller == 'browse' ) {
			
			// we are in mode selection
			// parent is the same, but action = share
			if ( $action == 'mode' ) {
				
				// rebuild the query, keep only location and provider
				$parent = array(
					'controller' => 'browse',
					'action' => 'share',
					'p'	=> $request->getParam('p'),
					'l' => $request->getParam('l')
				);
				
				return X_Env::encode(http_build_query($parent));
				//$request->getParams();
			}
			
			//
			if ( $action == 'share' ) {
				// we need the providerObj to know the parent :(
				$location = $request->getParam('l', false);
				
				if ( $location === false ) {
					// NICE, parent is the ROOT
					return '0';
				}
				
				$providerId = $request->getParam('p');
				$providerObj = X_VlcShares_Plugins::broker()->getPlugins($providerId);
				if ( $providerObj instanceof X_VlcShares_Plugins_ResolverInterface ) {
					// easy: provider will give us the parent
					$location = X_Env::decode($location);
					return X_Env::encode($providerObj->getParentLocation($location));
				}
				
				// so providerObj is not Resolver.... fuck
				// rude way?
				return '0';
			}
			

			if ( $action == 'selection' ) {
				// same as 'controls' Q_Q
				return '0';
			}
		}
		
		if ( $controller == 'controls') {
			// fuck: how can i manage this?
			return '0';
		}
		
		$data = array(
			'controller' => $controller,
			'action' => $action
		);
		
		$data = array_merge($data, $request->getParams());
		
		return X_Env::encode(http_build_query($data));
		
		
	} 
	
	private $request = array();
	
	public function setSOAPRequest($request) {
		$this->request = $request;
	}
	
	/*====================
	 RendererInterface
	 =====================*/
	function getName() {
		return X_Env::_('p_upnp_interfacename');
	}
	
	function getDescription() {
		return X_Env::_('p_upnp_interfacedescription');
	}
	
	function getRequiredFeatures() {
		return array(
			self::FEATURES_STANDALONEPLAYER,
			self::FEATURES_DNLA,
			self::FEATURES_UPNP,
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


