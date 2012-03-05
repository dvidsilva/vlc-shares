<?php


class AclController extends X_Controller_Action
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
		
		$resources = Application_Model_AclResourcesMapper::i()->fetchAll();
		
		$csrf = new Zend_Form_Element_Hash('csrf', array(
			'salt' => __CLASS__
		));
		$csrf->initCsrfToken();
		
		$_classes = X_VlcShares_Plugins::helpers()->acl()->getClasses();
		$classes = array();
		foreach ($_classes as $class) {
			$classes[$class->getName()] = $class->getName();
		}
		
		$this->view->classes = $classes;
		$this->view->csrf = $csrf->getHash();
		$this->view->resources = $resources;
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
		
	}
	
	function changeAction() {
		$key = $this->getRequest()->getParam('key', false);
		$class = $this->getRequest()->getParam('class', false);
		$csrf = $this->getRequest()->getParam('csrf', false);
		
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		
		if ( !$key ) {
			$this->_helper->json(array(
					'success' => false,
					'message' => X_Env::_("p_auth_acl_err_missingkey")
				), true, false);
			return;
		}
		$key = X_Env::decode($key);

		if ( !$class ) {
			$this->_helper->json(array(
					'success' => false,
					'message' => X_Env::_("p_auth_acl_err_missingclass")
			), true, false);
			return;
		}
		
		
		$hash = new Zend_Form_Element_Hash('csrf', array(
				'salt' => __CLASS__
		));
		
		if ( !$hash->isValid($csrf) ) {
			$this->_helper->json(array(
					'success' => false,
					'message' => X_Env::_("p_auth_acl_err_invalidcsrf")
			), true, false);
			return;
		}
		$hash->initCsrfToken();
		
		$resource = X_VlcShares_Plugins::helpers()->acl()->getResourceDescriptor($key);
		if ( $resource->isNew() ) {
			$this->_helper->json(array(
					'success' => false,
					'message' => X_Env::_("p_auth_acl_err_invalidkey")
			), true, false);
			return;
		}
		
		$resource->setClass($class);
		
		try {
			Application_Model_AclResourcesMapper::i()->save($resource);
			$this->_helper->json(array(
					'success' => true,
					'csrf' => $hash->getHash()
			), true, false);
			return;
				
		} catch (Exception $e) {
			$this->_helper->json(array(
					'success' => false,
					'message' => $e->getMessage() 
			), true, false);
			return;
		}
	}
	
	
	function removeAction() {

		$hash = $this->getRequest()->getParam('csrf');
		$key = X_Env::decode($this->getRequest()->getParam('key', false));
		
		$csrf = new Zend_Form_Element_Hash('csrf', array(
			'salt' => __CLASS__
		));
		
		if ( !$csrf->isValid($hash) ) {
			$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_auth_err_invalidhash')));
			$this->_helper->redirector('index', 'acl');
			return;
		}
		
		$resource = new Application_Model_AclResource();
		Application_Model_AclResourcesMapper::i()->find($key, $resource);
		if ( $resource->isNew() ) {
			$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_auth_acl_err_invalidkey')));
			$this->_helper->redirector('index', 'acl');
			return;
		}
		
		Application_Model_AclResourcesMapper::i()->delete($resource);
		$this->_helper->flashMessenger(array('type' => 'success', 'text' => X_Env::_('p_auth_acl_resourceremoved', $resource->getKey())));
		$this->_helper->redirector('index', 'acl');
		
	}
	
	function addAction() {
		
		$type = $this->getRequest()->getParam('type', 'class');
		
		if ( $type == 'resource') {
			$form = new Application_Form_AclResource();
			
			$_classes = X_VlcShares_Plugins::helpers()->acl()->getClasses();
			$classes = array();
			foreach ($_classes as $perm) {
				/* @var $perm Application_Model_AclClass */
				$classes[$perm->getName()] = "{$perm->getName()}";
			}
			$form->class->setMultiOptions($classes);
			$form->setDefault('class', Application_Model_AclClass::CLASS_BROWSE);

			$_plugins = X_VlcShares_Plugins::broker()->getPlugins();
			$plugins = array();
			foreach ($_plugins as $id => $plugin) {
				$plugins[$id] = $id == 'auth' ? 'CORE' : $id;
			}
			$form->generator->setMultiOptions($plugins);
			$form->setDefault('generator', 'auth');
						
		} else {
			$type = 'class';
			$form = new Application_Form_AclClass();
		}
		
		$form->setAction($this->_helper->url('save', 'acl', 'default', array('type' => $type)));
		
		$this->view->type = $type; 
		$this->view->form = $form;
		//$this->_helper->viewRenderer->setScriptAction('edit');
	}

	
	function saveAction() {
		
		$type = $this->getRequest()->getParam('type', false);
		
		if ( !$type || !$this->getRequest()->isPost() ) {
			$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_auth_err_invalidrequest')));
			$this->_helper->redirector('index', 'acl');
			return;
		}
		
		if ( $type == 'resource') {
			$form = new Application_Form_AclResource();
				
			$_classes = X_VlcShares_Plugins::helpers()->acl()->getClasses();
			$classes = array();
			foreach ($_classes as $perm) {
				/* @var $perm Application_Model_AclClass */
				$classes[$perm->getName()] = "{$perm->getName()}";
			}
			$form->class->setMultiOptions($classes);
			$form->setDefault('class', Application_Model_AclClass::CLASS_BROWSE);
		
			$_plugins = X_VlcShares_Plugins::broker()->getPlugins();
			$plugins = array();
			foreach ($_plugins as $id => $plugin) {
				$plugins[$id] = $id == 'auth' ? 'CORE' : $id;
			}
			$form->generator->setMultiOptions($plugins);
			$form->setDefault('generator', 'auth');
		
		} else {
			$type = 'class';
			$form = new Application_Form_AclClass();
		}
		
		if ( $form->isValid($this->getRequest()->getPost())) {
			
			if ( $type == 'resource') {
				$key = $form->getValue('key');
				$key = "default/$key";
				
				$model = new Application_Model_AclResource();
				$mapper = Application_Model_AclResourcesMapper::i(); 
				$mapper->find($key, $model);
				if ( $model->isNew() ) {
					$model->setKey($key);
					$model->setGenerator($form->getValue('generator'));
					$model->setClass($form->getValue('class'));
					X_Debug::i("Adding resource {key: {$model->getKey()}, class: {$model->getClass()}, generator: {$model->getGenerator()}}");
				} else {
					throw new Exception("This resource key already exists {{$key}}");
				}
			} elseif ( $type == 'class' ) {
				$model = new Application_Model_AclClass();
				$mapper = Application_Model_AclClassesMapper::i();
				$mapper->find($form->getValue('name'), $model);
				if ( $model->isNew() ) {
					$model->setName($form->getValue('name'));
					$model->setDescription($form->getValue('description'));
					X_Debug::i("Adding class {name: {$model->getName()}}");
				} else {
					throw new Exception("This class already exists {{$form->getValue('name')}}");
				}
			} else {
				throw new Exception("Invalid type {{$type}}");
			}
			
			try {
				$mapper->save($model);
				
				$this->_helper->flashMessenger(array('type' => 'success', 'text' => X_Env::_('p_auth_acl_datasaved')));
				$this->_helper->redirector('index', 'acl');
			} catch (Exception $e) {
				$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_auth_dberror', $e->getMessage())));
				$this->_helper->redirector('index', 'acl');
			}
			
		} else {
			$form->setAction($this->_helper->url('save', 'acl', 'default', array('type' => $type)));
			$form->setDefaults($this->getRequest()->getPost());
			
			$this->view->type = $type;
			$this->view->form = $form;
			$this->_helper->viewRenderer->setScriptAction('add');
		}
	}
	
}

