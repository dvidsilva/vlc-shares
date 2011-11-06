<?php

require_once 'X/Controller/Action.php';
require_once 'X/VlcShares.php';
require_once 'X/Env.php';

class ProfilesController extends X_Controller_Action {
	
	/**
	 * 
	 * @var Application_Form_Profile
	 */
	private $form = null;
	
		
	function indexAction() {
		
		$profiles = Application_Model_ProfilesMapper::i()->fetchAll();

		$form = $this->_initForm();
		
		$this->view->form = $form;
		$this->view->profiles = $profiles;
		
		$this->view->messages = array_merge($this->_helper->flashMessenger->getMessages(), $this->_helper->flashMessenger->getCurrentMessages());
		
	}
	
	function saveAction() {

		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		
		//$form = new Application_Form_Output();
		$form = $this->_initForm();
		
		if ( $request->isPost() && $form->isValid($request->getPost()) ) {
			$model = new Application_Model_Profile();
			if ( $form->getValue('id') ) {
				Application_Model_ProfilesMapper::i()->find($form->getValue('id'), $model);
			}
			$model->setLabel($form->getValue('label'));
			$model->setLink($form->getValue('link'));
			$model->setArg($form->getValue('arg'));
			//$this->_helper->flashMessenger(print_r($request->getPost(), true));
			try {
				Application_Model_ProfilesMapper::i()->save($model);
				$this->_helper->flashMessenger(X_Env::_('p_profiles_store_done'));
			} catch (Exception $e) {
				$this->_helper->flashMessenger(X_Env::_('p_profiles_err_db'));
			}
		} else {
			$this->_helper->flashMessenger(X_Env::_('p_profiles_err_invaliddata').": ".var_export($form->getErrors(), true));
		}
		$this->_helper->redirector('index', 'profiles');
	}

	private function _initForm() {
		if ( $this->form === null) {
			$this->form = new Application_Form_Profile();
			
			$this->form->setAction($this->_helper->url('save', 'profiles'));
			
			// i need to create an array of audio and video codecs and device types
			
		}
		return $this->form;	
	}
	
	
	function removeAction() {
		$id = $this->getRequest()->getParam('id', false);
		$csrf = $this->getRequest()->getParam('csrf', false);
		
		if ( $id === false ) {
			$this->_helper->flashMessenger(X_Env::_('p_profiles_err_invaliddata'));
			$this->_helper->redirector('index', 'profiles');
		}
		
		$profile = new Application_Model_Profile();
		Application_Model_ProfilesMapper::i()->find($id, $profile);
		if ( is_null($profile->getId()) ) {
			$this->_helper->flashMessenger(X_Env::_('p_profiles_err_invaliddata'));
			$this->_helper->redirector('index', 'profiles');
		}

		$form = new X_Form();
		$form->setMethod(Zend_Form::METHOD_POST)->setAction($this->_helper->url('remove', 'profiles', 'default', array('id' => $profile->getId())));
		$form->addElement('hash', 'csrf', array(
			'salt'  => __CLASS__,
			'ignore' => true,
			'required' => false
		));
		
		$form->addElement('hidden', 'id', array(
			'ignore' => true,
			'required' => false
		));
		
		$form->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => X_Env::_('confirm'),
        ));
        
        $form->addDisplayGroup(array('submit', 'csrf', 'id'), 'buttons', array('decorators' => $form->getDefaultButtonsDisplayGroupDecorators()));
		
		// execute delete and redirect to index
		if ( $form->isValid($this->getRequest()->getPost()) ) {
			try {
				Application_Model_ProfilesMapper::i()->delete($profile);
				$this->_helper->flashMessenger(X_Env::_('p_profiles_delete_done'));
			} catch (Exception $e) {
				$this->_helper->flashMessenger(X_Env::_('p_profiles_err_db'));
			}
			$this->_helper->redirector('index', 'profiles');
		}
		
		$form->setDefault('id', $profile->getId());
		
		$this->view->form = $form;
		$this->view->profile = $profile;
		
	}
	
	function addAction() {
		
		//$form = new Application_Form_FileSystemShare();
		//$form->setAction($this->_helper->url('save', 'filesystem'));
		$form = $this->_initForm();
		
		$this->view->form = $form;
		$this->render('edit');
	}
	
	function editAction() {
		
		$id = $this->getRequest()->getParam('id', false);
		if ( $id === false ) {
			$this->_helper->flashMessenger(X_Env::_('p_profiles_err_invaliddata'));
			$this->_helper->redirector('index', 'profiles');
		}
		
		$profile = new Application_Model_Profile();
		Application_Model_ProfilesMapper::i()->find($id, $profile);

		if ( is_null($profile->getId()) ) {
			$this->_helper->flashMessenger(X_Env::_('p_profiles_err_invaliddata'));
			$this->_helper->redirector('index', 'profiles');
		}
		
		$defaults = array(
			'id' => $profile->getId(),
			'label' => $profile->getLabel(),
			'arg'	=> $profile->getArg(),
			'link' => $profile->getLink()
		);
		
		/*
		$form = new Application_Form_FileSystemShare();
		$form->setAction($this->_helper->url('save', 'filesystem'));
		*/
		$form = $this->_initForm();
		$form->setDefaults($defaults);
		
		$this->view->form = $form;
	}	
	
}
