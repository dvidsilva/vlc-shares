<?php

class DevicesController extends X_Controller_Action {

		
	function indexAction() {
		
		$_profiles = Application_Model_ProfilesMapper::i()->fetchAll();
		$profiles = array();
		foreach ($_profiles as $profile) {
			$profiles[$profile->getId()] = $profile->getLabel();
		}
		unset($_profiles);
		
		$_guis = X_VlcShares_Plugins::broker()->getPlugins();
		$guis = array();
		foreach ($_guis as $gui) {
			if ( $gui instanceof X_VlcShares_Plugins_RendererInterface ) {
				$guis[get_class($gui)] = $gui->getName();
			}
		}
		
		$lastdevices = false;
		try {
			/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache  */
			$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
			$lastdevices = $cacheHelper->retrieveItem('devices::lastdevices');
		} catch (Exception $e) { /* key missing */ }
		if ( $lastdevices ) {
			$lastdevices = @unserialize($lastdevices);
		} 
		if ( !is_array($lastdevices) ) {
			$lastdevices = array();
		}
		foreach ($lastdevices as $key => $time ) {
			if ( $time < time() ) {
				unset($lastdevices[$key]);
			} else {
				$lastdevices[$key] = $key;  
			}
		}
		
		
		
		$devices = Application_Model_DevicesMapper::i()->fetchAll();

		$this->view->lastdevices = $lastdevices;
		$this->view->devices = $devices;
		$this->view->profiles = $profiles;
		$this->view->guis = $guis;
		
		$this->view->messages = array_merge($this->_helper->flashMessenger->getMessages(), $this->_helper->flashMessenger->getCurrentMessages());
		
	}
	
	function saveAction() {

		
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		
		/*
		echo '<pre>'.print_r($request->getPost(), true).'</pre>';
		return;
		*/
		
		$form = $this->initForm();
		
		if ( $request->isPost() && $form->isValid($request->getPost()) ) {
			$model = new Application_Model_Device();
			if ( $form->getValue('id') ) {
				Application_Model_DevicesMapper::i()->find($form->getValue('id'), $model);
			} else {
				$model->setPriority(100);
			}
			$model->setLabel($form->getValue('label'));
			$model->setPattern($form->getValue('pattern'));
			$model->setExact((bool) $form->getValue('exact'));
			$model->setGuiClass($form->getValue('gui'));
			$model->setIdProfile($form->getValue('profile'));
			$model->setExtra('alt-profiles', $form->getValue('profiles'));
			
			try {
				Application_Model_DevicesMapper::i()->save($model);
				$this->_helper->flashMessenger(X_Env::_('p_devices_store_done'));
			} catch (Exception $e) {
				$this->_helper->flashMessenger(X_Env::_('p_devices_err_db', $e->getMessage()));
			}
		} else {
			$this->_helper->flashMessenger(X_Env::_('p_devices_err_invaliddata')/*.": ".var_export($form->getErrors(), true)*/);
		}
		$this->_helper->redirector('index', 'devices');
		
	}
	
	function removeAction() {
		$id = $this->getRequest()->getParam('id', false);
		$csrf = $this->getRequest()->getParam('csrf', false);
		
		if ( $id === false ) {
			$this->_helper->flashMessenger(X_Env::_('p_devices_err_invaliddata'));
			$this->_helper->redirector('index', 'devices');
		}
		
		$device = new Application_Model_Device();
		Application_Model_DevicesMapper::i()->find($id, $device);
		if ( is_null($device->getId()) ) {
			$this->_helper->flashMessenger(X_Env::_('p_devices_err_invaliddata'));
			$this->_helper->redirector('index', 'devices');
		}

		$form = new X_Form();
		$form->setMethod(Zend_Form::METHOD_POST)->setAction($this->_helper->url('remove', 'devices', 'default', array('id' => $device->getId())));
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
				Application_Model_DevicesMapper::i()->delete($device);
				$this->_helper->flashMessenger(X_Env::_('p_devices_delete_done'));
			} catch (Exception $e) {
				$this->_helper->flashMessenger(X_Env::_('p_devices_err_db'));
			}
			$this->_helper->redirector('index', 'devices');
		}
		
		$form->setDefault('id', $device->getId());
		
		$this->view->form = $form;
		$this->view->device = $device;
		
	}
	
	function addAction() {
		
		
		/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache  */
		$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
		$lastdevices = false;
		try {
			$lastdevices = $cacheHelper->retrieveItem('devices::lastdevices');
		} catch (Exception $e) { /* key missing */ }
		if ( $lastdevices ) {
			$lastdevices = @unserialize($lastdevices);
		} 
		if ( !is_array($lastdevices) ) {
			$lastdevices = array();
		}
		foreach ($lastdevices as $key => $time ) {
			if ( $time < time() ) {
				unset($lastdevices[$key]);
			}
		}
		
		$lastdevices = array_keys($lastdevices);
		
		$form = $this->initForm();
		$form->setAction($this->_helper->url('save', 'devices'));

		$this->view->lastdevices = $lastdevices;
		$this->view->form = $form;
		
		$this->render('edit');
	}
	
	function editAction() {
		
		$id = $this->getRequest()->getParam('id', false);
		if ( $id === false ) {
			$this->_helper->flashMessenger(X_Env::_('gen_err_invaliddata'));
			$this->_helper->redirector('index', 'devices');
			return;
		}

		$device = new Application_Model_Device();
		Application_Model_DevicesMapper::i()->find($id, $device);

		if ( is_null($device->getId()) ) {
			$this->_helper->flashMessenger(X_Env::_('gen_err_invaliddata'));
			$this->_helper->redirector('index', 'devices');
			return;
		}
		
		
		$lastdevices = false;
		try {
			/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache  */
			$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
			$lastdevices = $cacheHelper->retrieveItem('devices::lastdevices');
		} catch (Exception $e) { /* key missing */ }
		if ( $lastdevices ) {
			$lastdevices = @unserialize($lastdevices);
		} 
		if ( !is_array($lastdevices) ) {
			$lastdevices = array();
		}
		foreach ($lastdevices as $key => $time ) {
			if ( $time < time() ) {
				unset($lastdevices[$key]);
			}
		}
		
		$lastdevices = array_keys($lastdevices);
		
		$defaults = array(
			'id' => $device->getId(),
			'label' => $device->getLabel(),
			'pattern'	=> $device->getPattern(),
			'exact' => (int) $device->isExact(),
			'profile' => $device->getIdProfile(),
			'gui' => $device->getGuiClass(),
			'priority' => $device->getPriority(),
			'profiles' => $device->getExtra('alt-profiles')
		);
		
		
		$form = $this->initForm();
		$form->setAction($this->_helper->url('save', 'devices'));
		$form->setDefaults($defaults);

		$this->view->lastdevices = $lastdevices;
		/*
		$this->view->outputs = $outputs;
		$this->view->profiles = $profiles;
		$this->view->guis = $guis;
		*/
		$this->view->device = $device;		
		
		$this->view->form = $form;
	}	
	
	public function upAction() {
		
		$id = $this->getRequest()->getParam('id', false);
		if ( $id === false ) {
			$this->_helper->flashMessenger(X_Env::_('gen_err_invaliddata'));
			$this->_helper->redirector('index', 'devices');
			return;
		}
		
		$devices = Application_Model_DevicesMapper::i()->fetchAll();
		
		for ($j = 0, $i = ( count($devices) -1 ); $i >= 0; $i--, $j++) {
			/* @var $device Application_Model_Device */
			//$device = &$devices[$i];
			
			if ( $devices[$i]->getId() == $id  ) {
				// check if already the top
				if ( $i > 0 ) {
					$devices[$i-1]->setPriority($j);
					$devices[$i]->setPriority($j+1);

					// save changes
					Application_Model_DevicesMapper::i()->save($devices[$i-1]);
					Application_Model_DevicesMapper::i()->save($devices[$i]);
					
					// jump to i + 2
					$i--;
					$j++;
					continue;
				}
			}
			$devices[$i]->setPriority($j);
			Application_Model_DevicesMapper::i()->save($devices[$i]);
		}
		
		
		$this->_helper->redirector('index', 'devices');
		
	}

	public function downAction() {
		
		$id = $this->getRequest()->getParam('id', false);
		if ( $id === false ) {
			$this->_helper->flashMessenger(X_Env::_('gen_err_invaliddata'));
			$this->_helper->redirector('index', 'devices');
			return;
		}
		
		$devices = Application_Model_DevicesMapper::i()->fetchAll();
		
		for ($i = 0, $j = ( count($devices) -1 ); $i < count($devices); $i++, $j--) {
			/* @var $device Application_Model_Device */
			//$device = &$devices[$i];
			
			if ( $devices[$i]->getId() == $id  ) {
				// check if already the top
				if ( $i < count($devices) - 1  ) {
					$devices[$i+1]->setPriority($j);
					$devices[$i]->setPriority($j-1);

					// save changes
					Application_Model_DevicesMapper::i()->save($devices[$i]);
					Application_Model_DevicesMapper::i()->save($devices[$i+1]);
					
					// jump to i + 2
					$i++;
					$j--;
					continue;
				}
			}
			$devices[$i]->setPriority($j);
			Application_Model_DevicesMapper::i()->save($devices[$i]);
		}
		
		
		$this->_helper->redirector('index', 'devices');
		
	}
	
	
	public function testAction() {
		
		$useragent = $this->getRequest()->getParam('user-agent', '');

		$devices = Application_Model_DevicesMapper::i()->fetchAll();
		$deviceFound = false;
		/* @var Application_Model_Device $device */
		foreach ($devices as $device) {
			// if exact do an == comparison
			if ( ($device->isExact() && $device->getPattern() == $useragent)
				// otherwise a regex match
					|| (!$device->isExact() && preg_match($device->getPattern(), $useragent ) > 0 ) ) {
				
				// valid $device found;
				$deviceFound = $device;
				break;
					
			} // false + 0 matches
		}
		
		if ( $deviceFound ) {
			$return = array('deviceId' => $deviceFound->getId(), 'success' => true);
		} else {
			$return = array('success' => false);
		}
		
		$this->_helper->json($return);
		
	}	
	
	/**
	 * @return Application_Form_Device
	 */
	protected function initForm() {
		
		$form = new Application_Form_Device();
		
		$profiles = Application_Model_ProfilesMapper::i()->fetchAll();
		$profilesMO = array();
		foreach ($profiles as $profile) {
			$profilesMO[(string) $profile->getId()] = "{$profile->getId()} - {$profile->getLabel()}";
		}
		
		$guis = X_VlcShares_Plugins::broker()->getPlugins();
		$guisMO = array();
		foreach ($guis as $gui) {
			if ( $gui instanceof X_VlcShares_Plugins_RendererInterface ) {
				$guisMO[get_class($gui)] = "{$gui->getName()} - {$gui->getDescription()}";
			}
		}
		
		$form->setProfilesValues($profilesMO)
			->setAltProfilesValues($profilesMO)
			->setGuisValues($guisMO);
		
		return $form;
		
	}
	
}
