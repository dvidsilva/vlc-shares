<?php

require_once 'X/Controller/Action.php';
require_once 'X/VlcShares.php';
require_once 'X/Env.php';

class FilesystemController extends X_Controller_Action {

		
	function indexAction() {
		
		// fetch all shares
		$shares = Application_Model_FilesystemSharesMapper::i()->fetchAll();
		
		$form = new Application_Form_FileSystemShare();
		$form->setAction($this->_helper->url('save', 'filesystem'));
		
		$showNew = $this->getRequest()->getParam('a',false);
		if ( $showNew == 'new' ) {
			$showNew = true;
		} else {
			$showNew = false;
		}
		
		$this->view->showNew = $showNew;
		$this->view->form = $form;
		$this->view->shares = $shares;
		$this->view->messages = array_merge($this->_helper->flashMessenger->getMessages(), $this->_helper->flashMessenger->getCurrentMessages());
		
	}
	
	function saveAction() {

		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		
		
		$form = new Application_Form_FileSystemShare();
		
		if ( $request->isPost() && $form->isValid($request->getPost()) ) {
			$share = new Application_Model_FilesystemShare();
			if ( $form->getValue('id') ) {
				Application_Model_FilesystemSharesMapper::i()->find($form->getValue('id'), $share);
			}
			$share->setLabel($form->getValue('label'));
			$share->setPath(rtrim($form->getValue('path'),'\\/').'/');
			try {
				Application_Model_FilesystemSharesMapper::i()->save($share);
				$this->_helper->flashMessenger(X_Env::_('p_filesystem_store_done'));
			} catch (Exception $e) {
				$this->_helper->flashMessenger(X_Env::_('p_filesystem_err_db'));
			}
		} else {
			$this->_helper->flashMessenger(X_Env::_('p_filesystem_err_invaliddata'));
		}
		$this->_helper->redirector('index', 'filesystem');
	}
	
	function removeAction() {
		$shareId = $this->getRequest()->getParam('id', false);
		$csrf = $this->getRequest()->getParam('csrf', false);
		
		if ( $shareId === false ) {
			$this->_helper->flashMessenger(X_Env::_('p_filesystem_err_invaliddata'));
			$this->_helper->redirector('index', 'filesystem');
		}
		
		$share = new Application_Model_FilesystemShare();
		Application_Model_FilesystemSharesMapper::i()->find($shareId, $share);
		if ( is_null($share->getId()) ) {
			$this->_helper->flashMessenger(X_Env::_('p_filesystem_err_invaliddata'));
			$this->_helper->redirector('index', 'filesystem');
		}

		$form = new X_Form();
		$form->setMethod(Zend_Form::METHOD_POST)->setAction($this->_helper->url('remove', 'filesystem', 'default', array('id' => $share->getId())));
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
				Application_Model_FilesystemSharesMapper::i()->delete($share);
				$this->_helper->flashMessenger(X_Env::_('p_filesystem_delete_done'));
			} catch (Exception $e) {
				$this->_helper->flashMessenger(X_Env::_('p_filesystem_err_db'));
			}
			$this->_helper->redirector('index', 'filesystem');
		}
		
		$form->setDefault('id', $share->getId());
		
		$this->view->form = $form;
		$this->view->share = $share;
		
	}
	
	function addAction() {
		
		$form = new Application_Form_FileSystemShare();
		$form->setAction($this->_helper->url('save', 'filesystem'));
		
		$this->view->form = $form;
		$this->render('edit');
	}
	
	function editAction() {
		
		$shareId = $this->getRequest()->getParam('id', false);
		if ( $shareId === false ) {
			$this->_helper->flashMessenger(X_Env::_('p_filesystem_err_invaliddata'));
			$this->_helper->redirector('index', 'filesystem');
		}
		
		$share = new Application_Model_FilesystemShare();
		Application_Model_FilesystemSharesMapper::i()->find($shareId, $share);

		if ( is_null($share->getId()) ) {
			$this->_helper->flashMessenger(X_Env::_('p_filesystem_err_invaliddata'));
			$this->_helper->redirector('index', 'filesystem');
		}
		
		$defaults = array(
			'id' => $share->getId(),
			'label' => $share->getLabel(),
			'path'	=> $share->getPath()
		);
		
		$form = new Application_Form_FileSystemShare();
		$form->setAction($this->_helper->url('save', 'filesystem'));
		$form->setDefaults($defaults);
		
		$this->view->form = $form;
	}
}
