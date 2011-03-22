<?php

/**
 * Enable auth features
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Auth extends X_VlcShares_Plugins_Abstract {

	protected $_whiteList = array(
		//'module/controller/action',
		'default/auth/index',
		'default/auth/login',
		'default/auth/logout',
	);
	
	protected $_ns;
	
	function __construct() {
		$this
			->setPriority('gen_beforePageBuild', 100)
			->setPriority('gen_beforeInit', 1)
			->setPriority('getStatusLinks', 99)
			->setPriority('getIndexManageLinks', 1)
			;
		 $this->_ns = new Zend_Session_Namespace('vlc-shares::auth');
	}
	
	function gen_beforeInit(Zend_Controller_Action $controller) {
		
		Application_Model_AuthSessionsMapper::i()->clearInvalid();
		
	}

	
	function gen_beforePageBuild(Zend_Controller_Action $controller) {
		
		$moduleName = $controller->getRequest()->getModuleName();
		$controllerName = $controller->getRequest()->getControllerName();
		$actionName = $controller->getRequest()->getActionName();
		
		// TODO:
		// replace this with an ACL call:
		//  if ( !$acl->canUse($resource, $currentStatus) )
		if ( array_search("$moduleName/$controllerName/$actionName", $this->_whiteList) === false
			&& !$this->isLoggedIn() // replace this with a session state check for user auth
			 ) {
			
			X_Debug::w("Auth required, redirecting to login");
			$controller->getRequest()->setControllerName("auth")
				->setActionName('index')
				->setDispatched(false);
			
		}
		
		
	}
	
	
	function getStatusLinks(Zend_Controller_Action $controller) {
		$items = new X_Page_ItemList_StatusLink();
		
		try {
			$username = $this->_ns->username;
		} catch (Exception $e) {
			$username = null;
		}
		if ( empty($username) ) $username = "SCONOSCIUTO";
		
		$item = new X_Page_Item_StatusLink('auth-username', "Ciao, <i>$username</i>");
		$item->setType(X_Page_Item_StatusLink::TYPE_LABEL);
		$items->append($item);
		
		
		$item = new X_Page_Item_StatusLink('auth-logout', "Logout");
		$item->setType(X_Page_Item_StatusLink::TYPE_BUTTON)
			->setLink(array(
				'controller' => 'auth',
				'action' => 'logout',
			), 'default', true)
			->setHighlight(true)
		;
		$items->append($item);
		
		return $items;
		
	}
	
	/**
	 * Add the link for -manage-accounts-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_auth_mlink'));
		$link->setTitle(X_Env::_('p_auth_managetitle'))
			->setIcon('/images/auth/logo.png')
			->setLink(array(
					'controller'	=>	'auth',
					'action'		=>	'accounts',
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
	}
	
	
	
	public function isLoggedIn($checkAlt = true) {
		// first check for sessions
		if ( $this->_ns->enabled && isset($this->_ns->username) ) {
			return true;
		} elseif ( $checkAlt ) {
			// then for ip session
			return Application_Model_AuthSessionsMapper::i()->fetchByIpUserAgent($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
		} else {
			return false;
		}
	}
	
	public function doLogin($username, $altMethod = false) {
		if ( $altMethod ) {
			$session = new Application_Model_AuthSession();
			Application_Model_AuthSessionsMapper::i()->fetchByIpUserAgent($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $session);
			$session->setIp($_SERVER['REMOTE_ADDR']);
			$session->setUserAgent($_SERVER['HTTP_USER_AGENT']);
			$session->setCreated(time());
			$session->setUsername($username);
			Application_Model_AuthSessionsMapper::i()->save($session);
		} else {
			$this->_ns->username = $username;
			$this->_ns->enabled = true;
		}
	}
	
	public function checkAuth($username, $password, $altMethod = false) {
		
		if ( !$altMethod ) {
			return Application_Model_AuthAccountsMapper::i()->fetchByUsernamePassword($username, $password);
		} else {
			return Application_Model_AuthAccountsMapper::i()->fetchByUsernamePassphrase($username, $password);
		}
	}
	
	public function doLogout() {
		
		$this->_ns->unsetAll();
		Application_Model_AuthSessionsMapper::i()->clearSessions($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']);
		
	}
	
}


