<?php

require_once 'X/VlcShares/Plugins/Abstract.php';


/**
 * VlcShares 0.4.2+ plugin:
 * provide filesystem based collection.
 * With this plugin, you can add directories
 * to vlc-shares's collection
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_FileSystem extends X_VlcShares_Plugins_Abstract {
	
	public function __construct() {
		$this->setPriority('getCollectionsItems');
	}
	
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		// usando le opzioni, determino quali link inserire
		// all'interno della pagina delle collections
		
		$urlHelper = $controller->getHelper('url');
		/* @var $urlHelper Zend_Controller_Action_Helper_Url */
		
		//$serverUrl = $controller->getFrontController()->getBaseUrl();
		$request = $controller->getRequest();
		/* @var $request Zend_Controller_Request_Http */
		//$request->get
		
		return array(X_Env::_('p_filesystem_collectionindex'), X_Env::completeUrl($urlHelper->url(array(
				'controller' => 'browse',
				'action' => 'share',
				'p' => $this->getId(),
			), 'default', true)));
	}
	
}
