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
			$share->setPath($form->getValue('path'));
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
		$shareId = $this->getRequest()->getParam('shareId', false);
		if ( $shareId !== false ) {
			$share = new Application_Model_FilesystemShare();
			Application_Model_FilesystemSharesMapper::i()->find($shareId, $share);
			if ( $share->getId() == $shareId ) {
				try {
					Application_Model_FilesystemSharesMapper::i()->delete($share);
					$this->_helper->flashMessenger(X_Env::_('p_filesystem_delete_done'));
				} catch (Exception $e) {
					$this->_helper->flashMessenger(X_Env::_('p_filesystem_err_db'));
				}
			} else {
				$this->_helper->flashMessenger(X_Env::_('p_filesystem_err_invaliddata'));
			}
		} else {
			$this->_helper->flashMessenger(X_Env::_('p_filesystem_err_invaliddata'));
		}
		$this->_helper->redirector('index', 'filesystem');
	}
}
