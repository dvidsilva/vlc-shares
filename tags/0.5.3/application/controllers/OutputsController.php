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
		
		$outputs = Application_Model_OutputsMapper::i()->fetchAll();
		
		$form = new Application_Form_Output();
		$form->setAction($this->_helper->url('save', 'outputs'));
		
		$devices = array(
			'' => X_Env::_('p_outputs_devicetype_generic'),
			X_VlcShares_Plugins_Helper_Devices::DEVICE_WIIMC => 'WiiMC',
			X_VlcShares_Plugins_Helper_Devices::DEVICE_ANDROID => 'Android',
			X_VlcShares_Plugins_Helper_Devices::DEVICE_PC => 'Pc',
		);
		
		$this->view->devices = $devices;
		$this->view->outputs = $outputs;
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
		$id = $this->getRequest()->getParam('id', false);
		$csrf = $this->getRequest()->getParam('csrf', false);
		
		if ( $id === false ) {
			$this->_helper->flashMessenger(X_Env::_('p_outputs_err_invaliddata'));
			$this->_helper->redirector('index', 'outputs');
		}
		
		$output = new Application_Model_Output();
		Application_Model_OutputsMapper::i()->find($id, $output);
		if ( is_null($output->getId()) ) {
			$this->_helper->flashMessenger(X_Env::_('p_outputs_err_invaliddata'));
			$this->_helper->redirector('index', 'outputs');
		}

		$form = new X_Form();
		$form->setMethod(Zend_Form::METHOD_POST)->setAction($this->_helper->url('remove', 'outputs', 'default', array('id' => $output->getId())));
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
				Application_Model_OutputsMapper::i()->delete($output);
				$this->_helper->flashMessenger(X_Env::_('p_outputs_delete_done'));
			} catch (Exception $e) {
				$this->_helper->flashMessenger(X_Env::_('p_outputs_err_db'));
			}
			$this->_helper->redirector('index', 'outputs');
		}
		
		$form->setDefault('id', $output->getId());
		
		$this->view->form = $form;
		$this->view->output = $output;
		
	}
	
	function addAction() {
		
		$form = new Application_Form_Output();
		$form->setAction($this->_helper->url('save', 'outputs'));
		
		$this->view->form = $form;
		$this->render('edit');
	}
	
	function editAction() {
		
		$id = $this->getRequest()->getParam('id', false);
		if ( $id === false ) {
			$this->_helper->flashMessenger(X_Env::_('p_outputs_err_invaliddata'));
			$this->_helper->redirector('index', 'outputs');
		}
		
		$output = new Application_Model_Output();
		Application_Model_OutputsMapper::i()->find($id, $output);

		if ( is_null($output->getId()) ) {
			$this->_helper->flashMessenger(X_Env::_('p_outputs_err_invaliddata'));
			$this->_helper->redirector('index', 'outputs');
		}
		
		$defaults = array(
			'id' => $output->getId(),
			'label' => $output->getLabel(),
			'link'	=> $output->getLink(),
			'cond_devices' => $output->getCondDevices(),
			'arg' => $output->getArg(),
			'weight' => $output->getWeight(),
		);
		
		$form = new Application_Form_Output();
		$form->setAction($this->_helper->url('save', 'outputs'));
		$form->setDefaults($defaults);
		
		$this->view->form = $form;
	}	
	
}
