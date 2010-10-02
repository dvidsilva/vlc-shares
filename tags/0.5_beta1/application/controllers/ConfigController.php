<?php

require_once 'X/Controller/Action.php';

class ConfigController extends X_Controller_Action {

	/**
	 * 
	 * @var Application_Form_Configs
	 */
	private $configForm = null;
	
    /**
     * Show configs page
     */
    public function indexAction() {

    	$request = $this->getRequest();
    	if ( $request instanceof Zend_Controller_Request_Http) {
    		if ( $request->isXmlHttpRequest() ) {
    			$this->_helper->layout->disableLayout();
    		}
    	}
    	
    	//$section = $this->getRequest()->getParam('section', false);
    	$section = 'plugins';
    	$key = $this->getRequest()->getParam('key', false);
    	$redirect = base64_decode($this->getRequest()->getParam('r', ''));
    	if ( $redirect == '') {
    		$redirect = 'config:index';
    	}
    	
    	$configs = Application_Model_ConfigsMapper::i()->fetchBySectionNamespace($section, $key);
    	
    	$form = $this->_initConfigsForm($section, $key, $configs);
    	$form->addElement('hidden', 'redirect', array('value' => $redirect, 'ignore' => true, 'decorators' => array('ViewHelper')));

    	$defaultValues = array();
    	foreach($configs as $config) {
    		/* @var $config Application_Model_Config */
    		$elementName = $config->getSection().'_'.str_replace('.', '_', $config->getKey());
    		$defaultValues[$elementName] = $config->getValue();
    	}
    	
    	$form->setDefaults($defaultValues);
    	
    	$plugins = Application_Model_PluginsMapper::i()->fetchAll();
    	
    	$this->view->plugins = $plugins;
    	$this->view->form = $form;
    	$this->view->messages = array_merge($this->_helper->flashMessenger->getMessages(), $this->_helper->flashMessenger->getCurrentMessages()) ;
    	$this->view->key = strtolower($key);
    	
    }
    
    // save configs
    public function saveAction() {

    	/* @var $request Zend_Controller_Request_Http */
    	$request = $this->getRequest();
    	
    	$configs = Application_Model_ConfigsMapper::i()->fetchAll();
    	
    	if ( $request->isPost() ) {
    		
    		try {
    			$redirect = explode(':', $request->getPost('redirect', 'config:index'));
    		} catch ( Exception $e) {
    			$redirect = array('config', 'index');
    		}
    		
    		$form = $this->_initConfigsForm($configs, $request->getPost());
    		$form->addElement('hidden', 'redirect', array('value' => "{$redirect[0]}:{$redirect[1]}", 'ignore' => true, 'decorators' => array('ViewHelper')));
    		
    		if ( !$form->isErrors() ) {
    			$post = $request->getPost();
    			$isError = false;
    			foreach ( $configs as $config ) {
    				/* @var $config Application_Model_Config */
    				//if ( $config->getSection() == 'plugins' ) continue; // plugins config will not be handled here
    				
    				$postStoreName = $config->getSection()."_".str_replace('.', '_', $config->getKey());
    				
    				if ( array_key_exists($postStoreName, $post) && $config->getValue() != $request->getPost($postStoreName) ) {
    					// new value
    					try {
    						$config->setValue($request->getPost($postStoreName));
    						Application_Model_ConfigsMapper::i()->save($config);
    						X_Debug::i("New config: {$config->getKey()} = {$config->getValue()}");
    					} catch (Exception $e) {
    						$isError = true;
    						$form->$postStoreName->addError($e->getMessage());
    						$this->_helper->flashMessenger(X_Env::_('configs_save_err_db').": {$e->getMessage()}");
    					}
    				} 
    			}
    			if (!$isError) {
    				$this->_helper->flashMessenger(X_Env::_('configs_save_done'));
    				$this->_helper->redirector->gotoRoute(array('controller' => $redirect[0], 'action' => $redirect[1]), 'default', false);
    			}
    			
    			$this->_forward('index');
    			
    		} else {
    			$this->_forward('index');
    		}
    	} else {
    		$this->_helper->flashMessenger(X_Env::_('configs_save_nodata'));
    		$this->_helper->redirector('index', 'config');
    	}
    	
    }
	
	
    private function _initConfigsForm($section, $namespace, $configs, $posts = null) {
    	
    	if ( $this->configForm === null ) {
	    	$this->configForm = new Application_Form_AutoConfigs($configs);
	    	
	    	$this->configForm->setAction($this->_helper->url->url(array('action' => 'save', 'controller' => 'config'), 'default', false));
	    	

	    	foreach ($this->configForm->getElements() as $key => $element) {
	    		// plugins prepare known elements
	    		X_VlcShares_Plugins::broker()->prepareConfigElement($section, $namespace, $key, $element, $this->configForm, $this);
	    	}
	    	
    	}
    	
    	if ( $posts !== null && is_array($posts)  ) {
    		$this->configForm->isValid($posts);
    	}

    	return $this->configForm;
    }
    
    
}