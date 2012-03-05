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
		'default/auth/forbidden',
		'default/threads/start',
	);
	
	protected $_ns;
	
	function __construct() {
		$this
			->setPriority('gen_beforeInit', 1)
			;
		 $this->_ns = new Zend_Session_Namespace('vlc-shares::auth');
	}
	
	function gen_beforeInit(Zend_Controller_Action $controller) {
		
		//X_Debug::i(print_r($controller->getRequest()->getParams(), true));
		
		if ( $this->config('login.enabled', false) ) {
			$this
				->setPriority('gen_beforePageBuild', 100)
				->setPriority('getStatusLinks', 99)
				->setPriority('getIndexManageLinks', 1);
			
			X_Debug::i("Clearing outdated sessions");
			Application_Model_AuthSessionsMapper::i()->clearInvalid();
		}
		
	}

	
	function gen_beforePageBuild(Zend_Controller_Action $controller) {
		
		$moduleName = $controller->getRequest()->getModuleName();
		$controllerName = $controller->getRequest()->getControllerName();
		$actionName = $controller->getRequest()->getActionName();
		$providerName = $controller->getRequest()->getParam('p', false);
		
		// check permission class
		if ( $controllerName == 'browse' && $controllerName == 'share' ) {
			// check provider too only if controller == browse
			$resourceKey = "$moduleName/$controllerName/$actionName" . $providerName !== false ? "/$providerName" : '';
		} else {
			$resourceKey = "$moduleName/$controllerName/$actionName";
		}
		
		// TODO:
		// replace this with an ACL call:
		//  if ( !$acl->canUse($resource, $currentStatus) )
		if ( array_search("$moduleName/$controllerName/$actionName", $this->_whiteList) === false ) {
			if ( !$this->isLoggedIn() ) {

				X_Debug::i("Login required and user not logged in");
				
				if ( $this->helpers()->devices()->isVlc() ) {
					X_Debug::i("Look like it's this vlc: {$_SERVER['REMOTE_ADDR']}");
					if ( gethostbyname($_SERVER['REMOTE_ADDR']) == '::1' || gethostbyname($_SERVER['REMOTE_ADDR']) == '127.0.0.1' ) {
						X_Debug::i("Skipping auth, it should be the local vlc");
						return;
					}
				} elseif ( $this->helpers()->devices()->isWiimc() && $this->helpers()->devices()->isWiimcBeforeVersion('1.1.6')) {
					// wiimc <= 1.1.5 send 2 different user-agent string
					// for browsing mode and video mode
					// so i have care about this
					// TODO remove this when 1.1.5 an older will be deprecated
					
					// anyway i take care only of vanilla wiimc based on ios58. custom version
					// not supported
					$useragent = "{$_SERVER['HTTP_USER_AGENT']} (IOS58)";
					// try to add " (ISO58)" to the useragent and check again
					if ( Application_Model_AuthSessionsMapper::i()->fetchByIpUserAgent($_SERVER['REMOTE_ADDR'], $useragent) ) {
						X_Debug::i("Workaround for WIIMC user-agent-incongruence");
						return;
					}
				} elseif ( $this->helpers()->acl()->canUseAnonymously($resourceKey) ) {
					X_Debug::i("Resource can be used anonymously");
					return;
				}
				 	
				X_Debug::w("Auth required, redirecting to login");
				$controller->getRequest()->setControllerName("auth")
					->setActionName('index')
					->setDispatched(false);
				
			} elseif ( $this->config('acl.enabled', false) ) {
				
				X_Debug::i("ACL enabled, checking resource {{$resourceKey}}");
				
				$username = $this->getCurrentUser(true);
				
				if ( !$this->helpers()->acl()->canUse($username, $resourceKey) ) {
					X_Debug::w("Forbidden, can't access resource");
					$controller->getRequest()->setControllerName("auth")
					->setActionName('forbidden')
					->setDispatched(false);
				} else {
					X_Debug::i("Access granted");
				}
			} else {
				X_Debug::i("ACL disabled");
			}
		} else {
			X_Debug::i("Whitelisted resource");
		}
	}
	
	
	function getStatusLinks(Zend_Controller_Action $controller) {
		$items = new X_Page_ItemList_StatusLink();
		
		try {
			$username = $this->_ns->username;
		} catch (Exception $e) {
			$username = null;
		}
		
		if ( is_null($username) ) return;
		
		if ( is_null($username) ) $username = X_Env::_('p_auth_userunknown');
		
		$item = new X_Page_Item_StatusLink('auth-username', X_Env::_("p_auth_welcome", "<b><i>$username</b></i>"));
		$item->setType(X_Page_Item_StatusLink::TYPE_LABEL);
		$items->append($item);
		
		
		$item = new X_Page_Item_StatusLink('auth-logout', X_Env::_('p_auth_logout'));
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

		$list = new X_Page_ItemList_ManageLink();
		
		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_auth_mlink'));
		$link->setTitle(X_Env::_('p_auth_managetitle'))
			->setIcon('/images/auth/logo.png')
			->setLink(array(
					'controller'	=>	'auth',
					'action'		=>	'accounts',
			), 'default', true);
		$list->append($link);
		
		if ( $this->config('acl.enabled', false) ) {
			$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_auth_mlink_resources'));
			$link->setTitle(X_Env::_('p_auth_managetitle_resources'))
			->setIcon('/images/auth/permissions.png')
			->setLink(array(
					'controller'	=>	'acl',
					'action'		=>	'index',
			), 'default', true);
			$list->append($link);
		}
		
		
		return $list;
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
		}
		// anyway, try to use sessions
		$this->_ns->username = $username;
		$this->_ns->enabled = true;
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
	
	public function getCurrentUser($checkAltSession = false) {
		if ( $this->isLoggedIn(false) ) {
			return $this->_ns->username;
		} else {
			if ( $checkAltSession ) {
				$session = new Application_Model_AuthSession();
				if ( Application_Model_AuthSessionsMapper::i()->fetchByIpUserAgent($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $session) ) {
					return $session->getUsername();
				} else {
					throw new Exception("User not authenticated");
				}
			} else {
				throw new Exception("User not authenticated (in normal way)");
			}
		}
	}
	
}


