<?php


class AuthController extends X_Controller_Action
{

	/**
	 * @var X_VlcShares_Plugins_Auth
	 */
	protected $plugin = null;
	
	function init() {
		// call parent init, always
		parent::init();
		if ( !X_VlcShares_Plugins::broker()->isRegistered('auth') ) {
			/*
			$this->_helper->flashMessenger(X_Env::_('err_pluginnotregistered') . ": youtube");
			$this->_helper->redirector('index', 'manage');
			*/
			throw new Exception(X_Env::_('err_pluginnotregistered') . ": auth");
		} else {
			$this->plugin = X_VlcShares_Plugins::broker()->getPlugins('auth');
		}
	}
	
	function indexAction() {
		
		if ( X_VlcShares_Plugins::helpers()->devices()->isWiimc() ) {
			// everything have to be in plx format
			
			$plx = new X_Plx(
				X_Env::_('p_auth_loginindex_plxtitle'),
				X_Env::_('p_auth_loginindex_plxdesc')
			);
			
			$plx->addItem(new X_Plx_Item(X_Env::_('p_auth_login_advice'), X_Env::completeUrl($this->_helper->url('index', 'auth'))));
			$plx->addItem(new X_Plx_Item(X_Env::_('p_auth_login_pressbbutton'), X_Env::completeUrl($this->_helper->url('index', 'auth'))));
			
			$this->_helper->viewRenderer->setNoRender(true);
			$this->_helper->layout->disableLayout();
			$this->getResponse()->setHeader('Content-Type', 'text/plain');
			$this->getResponse()->setBody((string) $plx);
			//echo $plx;
			return;
		}
		
		//if ( $this->ns->enabled && isset($this->ns->username) ) { 
			//throw new Exception(X_Env::_('p_auth_already_loggedin'));
		//}

		if ( $this->plugin->isLoggedIn() ) {
			throw new Exception(X_Env::_('p_auth_already_loggedin'));
		}
		
		
		$form = new Application_Form_AuthLogin();
		$form->setAction($this->_helper->url('login', 'auth'));
		
		
		$this->view->form = $form;
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
		
	}
	
	function forbiddenAction() {
		throw new Exception("403: Forbidden");
	}
	
	function loginAction() {
				
		// provide alternative auth method
		$authM = $this->getRequest()->getParam('m', false);
		$authU = $this->getRequest()->getParam('u', false);
		$authP = $this->getRequest()->getParam('p', false);
		
		if ( $authM === 'alt' ) {
			
			// this auth should be allowed for mobile devices / wiimc (better if wiimc only)
			
			if ( !$this->plugin->checkAuth($authU, $authP, true) ) {
				$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_auth_err_invalidcredential')));
				if ( X_VlcShares_Plugins::helpers()->devices()->isWiimc() ) {
					$this->_forward('index', 'auth');
				} else {
					$this->_helper->redirector('index', 'auth');
				}
				return;
			}
						
			$this->plugin->doLogin($authU, true);
			
			// alt method forward always to collections
			$this->_forward('collections', 'index');
			return;
			
		} elseif ( $this->getRequest()->isPost() ) {

			if ( $this->plugin->isLoggedIn() ) {
				throw new Exception(X_Env::_('p_auth_already_loggedin'));
			}
			
			$form = new Application_Form_AuthLogin();
			if ( !$form->isValid($this->getRequest()->getPost()) ) {
				$form->setAction($this->_helper->url('login', 'auth'));
				$this->view->form = $form;
				$this->_helper->viewRenderer->setScriptAction('index');
				$this->view->messages = $this->_helper->flashMessenger->getMessages();
				return;
			}
			
			$username = $form->getValue('username');
			$password = $form->getValue('password');
			
			if ( !$this->plugin->checkAuth($username, $password) ) {
				$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_auth_err_invalidcredential')));
				$this->_helper->redirector('index', 'auth');
				return;
			}

			$this->plugin->doLogin($username);
			
			$this->_helper->flashMessenger(array('type' => 'success', 'text' => X_Env::_('p_auth_loginok')));
			$this->_helper->redirector('index', 'manage');
			return;
			
		} else {
			$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_auth_err_loginmethod')));
			$this->_helper->redirector('index', 'auth');
		}
	}
	
	function logoutAction() {
		
		$this->plugin->doLogout();
		
		$this->_helper->flashMessenger(array('type' => 'success', 'text' => X_Env::_('p_auth_logoutok')));
		$this->_helper->redirector('index','manage');
	}
	
	function accountsAction() {
		
		$accounts = Application_Model_AuthAccountsMapper::i()->fetchAll();
		
		$csrf = new Zend_Form_Element_Hash('csrf', array(
			'salt' => __CLASS__
		));
		$csrf->initCsrfToken();
		
		$this->view->ip = '%IP_ADDRESS%';
		
		$this->view->csrf = $csrf->getHash();
		$this->view->accounts = $accounts;
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
		
	}
	
	/*
	function enabledAction() {
		
	}
	*/
	
	function removeAction() {

		$hash = $this->getRequest()->getParam('csrf');
		$accountId = $this->getRequest()->getParam('id');
		
		$csrf = new Zend_Form_Element_Hash('csrf', array(
			'salt' => __CLASS__
		));
		
		if ( !$csrf->isValid($hash) ) {
			$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_auth_err_invalidhash')));
			$this->_helper->redirector('accounts', 'auth');
			return;
		}
		
		$account = new Application_Model_AuthAccount();
		Application_Model_AuthAccountsMapper::i()->find($accountId, $account);
		if ( is_null($account->getId()) ) {
			$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_auth_err_invalidaccount')));
			$this->_helper->redirector('accounts', 'auth');
			return;
		}
	
		if ( $this->plugin->getCurrentUser() == $account->getUsername() ) {
			$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_auth_err_currentremovalnotallowed')));
			$this->_helper->redirector('accounts', 'auth');
			return;
		}
		
		Application_Model_AuthAccountsMapper::i()->delete($account);
		$this->_helper->flashMessenger(array('type' => 'success', 'text' => X_Env::_('p_auth_accountremoved', $account->getUsername())));
		$this->_helper->redirector('accounts', 'auth');
		
	}
	
	function addAction() {
		$form = new Application_Form_AuthAccount();
		$form->setAction($this->_helper->url('save', 'auth'));

		
		$_permissions = X_VlcShares_Plugins::helpers()->acl()->getClasses();
		$permissions = array();
		$permissionsDefault = array();
		foreach ($_permissions as $perm) {
			/* @var $perm Application_Model_AclClass */
			$description = X_Env::_($perm->getDescription());
			$permissions[$perm->getName()] = "{$perm->getName()} - <i>{$description}</i>";
			if ( $perm->getName() == Application_Model_AclClass::CLASS_BROWSE ) {
				$permissionsDefault[] = $perm->getName();
			}
		}
		
		$form->permissions->setMultiOptions($permissions);

		$form->setDefault('enabled', 1)
			->setDefault('altallowed', 1)
			->setDefault('permissions', $permissionsDefault)
		;
			
		
		
		
		$this->view->form = $form;
		$this->_helper->viewRenderer->setScriptAction('edit');
	}
	
	function editAction() {
		
		$accountId = $this->getRequest()->getParam('id', false);
		
		$account = new Application_Model_AuthAccount();
		Application_Model_AuthAccountsMapper::i()->find($accountId, $account);
		
		if ( is_null($account->getId()) ) {
			$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_auth_err_invalidaccount')));
			$this->_helper->redirector('accounts', 'auth');
			return;
		}
		
		$form = new Application_Form_AuthAccount();
		$form->setAction($this->_helper->url('save', 'auth'));
		$form->username->setAttrib('disabled', true);
		
		
		$_permissions = X_VlcShares_Plugins::helpers()->acl()->getClasses();
		$permissions = array();
		$permissionsDefault = array();
		foreach ($_permissions as $perm) {
			/* @var $perm Application_Model_AclClass */
			$description = X_Env::_($perm->getDescription());
			$permissions[$perm->getName()] = "{$perm->getName()} - <i>{$description}</i>";
			if ( in_array($perm->getName(), X_VlcShares_Plugins::helpers()->acl()->getPermissions($account->getUsername())) ) {
				$permissionsDefault[] = $perm->getName();
			}
		}
		
		$form->permissions->setMultiOptions($permissions);
		
		
		$form->setDefault('username', $account->getUsername())
			->setDefault('id', $account->getId())
			->setDefault('password', $account->getPassword())
			->setDefault('enabled', $account->isEnabled() ? 1 : 0)
			->setDefault('altallowed', $account->isAltAllowed() ? 1 : 0)
			->setDefault('permissions', $permissionsDefault)
		;
		
		$this->view->form = $form;
	}
	
	function saveAction() {
		
		if ( !$this->getRequest()->isPost() ) {
			$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_auth_err_invalidrequest')));
			$this->_helper->redirector('accounts', 'auth');
			return;
		}
		
		$form = new Application_Form_AuthAccount();
		
		if ( $this->getRequest()->getPost('id', false) ) {
			$form->password->setAttrib('allowEmpty', true);
			$form->password->setRequired(false);
			$form->username->setRequired(false);
			$form->username->setAttrib('disabled', true);
		}

		$_permissions = X_VlcShares_Plugins::helpers()->acl()->getClasses();
		$permissions = array();
		$permissionsDefault = array();
		foreach ($_permissions as $perm) {
			/* @var $perm Application_Model_AclClass */
			$description = X_Env::_($perm->getDescription());
			$permissions[$perm->getName()] = "{$perm->getName()} - <i>{$description}</i>";
			//if ( in_array($perm->getName(), X_VlcShares_Plugins::helpers()->acl()->getPermissions($account->getUsername())) ) {
				//$permissionsDefault[] = $perm->getName();
			//}
		}
		
		$form->permissions->setMultiOptions($permissions);
		
		
		if ( $form->isValid($this->getRequest()->getPost())) {
			
			$account = new Application_Model_AuthAccount();
			$id = $form->getValue('id');
			if ( $id ) {
				Application_Model_AuthAccountsMapper::i()->find($id, $account);
				if ( is_null($account->getId()) ) {
					$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_auth_err_invalidaccount')));
					$this->_helper->redirector('accounts', 'auth');
					return;
				}
				
				// empty passwords are ignored: empty = do not change
				if ( strlen($form->getValue('password')) > 0 ) {
					$account->setPassword(md5("{$account->getUsername()}:{$form->getValue('password')}"))
						->setPassphrase(md5("{$account->getUsername()}:{$form->getValue('password')}:".rand(10000,99999).time()))
						;
				}
				
			} else {
				$account->setUsername($form->getValue('username'));
				$account->setPassword(md5("{$form->getValue('username')}:{$form->getValue('password')}"))
					->setPassphrase(md5("{$form->getValue('username')}:{$form->getValue('password')}:".rand(10000,99999).time()))
					;
			}
			
			$account
				->setEnabled((bool) $form->getValue('enabled'))
				->setAltAllowed((bool) $form->getValue('altallowed'))
			;
			
			try {
				Application_Model_AuthAccountsMapper::i()->save($account);
				
				// if is a new account, grant browse permission to the new account
				/*
				if ( !$id ) {
					X_VlcShares_Plugins::helpers()->acl()->grantPermission($account->getUsername(), Application_Model_AclClass::CLASS_BROWSE);
				}
				*/
				$acl = X_VlcShares_Plugins::helpers()->acl();
				$prevPermissions = array();
				if ( $id ) {
					$prevPermissions = $acl->getPermissions($account->getUsername());
				}
				
				$newPermissions = $form->getValue('permissions');
				
				// first remove all old permissions that are not available anymore
				foreach ($prevPermissions as $pPerm) {
					if ( !in_array($pPerm, $newPermissions) ) {
						$acl->revokePermission($account->getUsername(), $pPerm);
					}
				}
				
				// add new granted not in old permissions
				foreach ($newPermissions as $nPerm) {
					if ( !in_array($nPerm, $prevPermissions) ) {
						$acl->grantPermission($account->getUsername(), $nPerm);
					}
				}
				
				
				$this->_helper->flashMessenger(array('type' => 'success', 'text' => X_Env::_('p_auth_accountstored')));
				$this->_helper->redirector('accounts', 'auth');
			} catch (Exception $e) {
				$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_auth_dberror', $e->getMessage())));
				$this->_helper->redirector('accounts', 'auth');
			}
			
		} else {
			$form->setAction($this->_helper->url('save', 'auth'));
			$form->setDefaults($this->getRequest()->getPost());
			$this->view->form = $form;
			$this->_helper->viewRenderer->setScriptAction('edit');
		}
	}
	
}

