<?php

require_once 'X/Controller/Action.php';
require_once 'X/VlcShares.php';
require_once 'X/Env.php';

class OutputsController extends X_Controller_Action {

		
	function indexAction() {
		
		// fetch all shares
		$wiimcOuts = Application_Model_OutputsMapper::i()->fetchAllForDevice(X_VlcShares_Plugins_Helper_Devices::DEVICE_WIIMC);
		$androidOuts = Application_Model_OutputsMapper::i()->fetchAllForDevice(X_VlcShares_Plugins_Helper_Devices::DEVICE_ANDROID);
		$pcOuts = Application_Model_OutputsMapper::i()->fetchAllForDevice(X_VlcShares_Plugins_Helper_Devices::DEVICE_PC);
		
		$generalOuts = Application_Model_OutputsMapper::i()->fetchAllForDevice(null);
		
		$form = new Application_Form_Output();
		$form->setAction($this->_helper->url('save', 'outputs'));
		
		$this->view->form = $form;
		$this->view->wiimcOuts = $wiimcOuts;
		$this->view->androidOuts = $androidOuts;
		$this->view->pcOuts = $pcOuts;
		$this->view->generalOuts = $generalOuts;
		$this->view->messages = array_merge($this->_helper->flashMessenger->getMessages(), $this->_helper->flashMessenger->getCurrentMessages());
		
	}
	
	function saveAction() {

		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		
		
		$form = new Application_Form_Output();
		
		if ( $request->isPost() && $form->isValid($request->getPost()) ) {
			$model = new Application_Model_Output();
			if ( $form->getValue('id') ) {
				Application_Model_OutputsMapper::i()->find($form->getValue('id'), $model);
			}
			$model->setLabel($form->getValue('label'));
			$model->setLink($form->getValue('link'));
			$model->setArg($form->getValue('arg'));
			$model->setWeight($form->getValue('weight'));
			$cond_devices = $form->getValue('cond_devices');
			if ( $cond_devices == '-1') $cond_devices = null;
			$model->setCondDevices($cond_devices);
			//$this->_helper->flashMessenger(print_r($request->getPost(), true));
			try {
				Application_Model_OutputsMapper::i()->save($model);
				$this->_helper->flashMessenger(X_Env::_('p_outputs_store_done'));
			} catch (Exception $e) {
				$this->_helper->flashMessenger(X_Env::_('p_outputs_err_db'));
			}
		} else {
			$this->_helper->flashMessenger(X_Env::_('p_outputs_err_invaliddata')/*.": ".var_export($form->getErrors(), true)*/);
		}
		$this->_helper->redirector('index', 'outputs');
	}
	
	function removeAction() {
		$id = $this->getRequest()->getParam('outputId', false);
		if ( $id !== false ) {
			$model = new Application_Model_Output();
			Application_Model_OutputsMapper::i()->find($id, $model);
			if ( $model->getId() == $id ) {
				try {
					Application_Model_OutputsMapper::i()->delete($model);
					$this->_helper->flashMessenger(X_Env::_('p_outputs_delete_done'));
				} catch (Exception $e) {
					$this->_helper->flashMessenger(X_Env::_('p_outputs_err_db'));
				}
			} else {
				$this->_helper->flashMessenger(X_Env::_('p_outputs_err_invaliddata'));
			}
		} else {
			$this->_helper->flashMessenger(X_Env::_('p_outputs_err_invaliddata'));
		}
		$this->_helper->redirector('index', 'outputs');
	}
}
