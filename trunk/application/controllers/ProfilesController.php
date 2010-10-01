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
		
		// fetch all shares
		/*
		$wiimcOuts = Application_Model_OutputsMapper::i()->fetchAllForDevice(X_VlcShares_Plugins_Helper_Devices::DEVICE_WIIMC);
		$androidOuts = Application_Model_OutputsMapper::i()->fetchAllForDevice(X_VlcShares_Plugins_Helper_Devices::DEVICE_ANDROID);
		$pcOuts = Application_Model_OutputsMapper::i()->fetchAllForDevice(X_VlcShares_Plugins_Helper_Devices::DEVICE_PC);
		
		$generalOuts = Application_Model_OutputsMapper::i()->fetchAllForDevice(null);
		
		*/
		
		$profiles = Application_Model_ProfilesMapper::i()->fetchAll();
		

		$acodecs = array(
			'unknown' => X_Env::_('p_profiles_conf_codectype_unknown'),
			X_VlcShares_Plugins_Helper_StreaminfoInterface::ACODEC_MP3 => 'MP3',
			X_VlcShares_Plugins_Helper_StreaminfoInterface::ACODEC_AAC => 'AAC',
			X_VlcShares_Plugins_Helper_StreaminfoInterface::ACODEC_AC3 => 'AC3',
		);
		
		$vcodecs = array(
			'unknown' => X_Env::_('p_profiles_conf_codectype_unknown'),
			X_VlcShares_Plugins_Helper_StreaminfoInterface::VCODEC_XVID => 'XVID',
			X_VlcShares_Plugins_Helper_StreaminfoInterface::VCODEC_FLV => 'FLV (H.263)',
			X_VlcShares_Plugins_Helper_StreaminfoInterface::VCODEC_H264 => 'AVC (H.264)',
		);
		
		$devices = array(
			'unknown' => X_Env::_('p_profiles_conf_devicetype_unknown'),
			X_VlcShares_Plugins_Helper_Devices::DEVICE_WIIMC => 'WiiMC',
			X_VlcShares_Plugins_Helper_Devices::DEVICE_ANDROID => 'Android',
			X_VlcShares_Plugins_Helper_Devices::DEVICE_PC => 'Pc',
		);
		

		$form = $this->_initForm();
		
		
		$this->view->form = $form;
		
		$this->view->vcodecs = $vcodecs;
		$this->view->acodecs = $acodecs;
		$this->view->devices = $devices;
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
			//$model->setLink($form->getValue('link'));
			$model->setArg($form->getValue('arg'));
			$model->setWeight($form->getValue('weight'));
			$device = $form->getValue('device');
			$audio = $form->getValue('audio');
			$video = $form->getValue('video');
			if ( $device == 'unknown') $device = null;
			if ( $audio == 'unknown' || $video == 'unknown' ) {
				$audiovideo = null;
			} else {
				$audiovideo = "$video+$audio";
			}
			$model->setCondDevices($device);
			$model->setCondFormats($audiovideo);
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
	
	function removeAction() {
		$id = $this->getRequest()->getParam('profileId', false);
		if ( $id !== false ) {
			$model = new Application_Model_Profile();
			Application_Model_ProfilesMapper::i()->find($id, $model);
			if ( $model->getId() == $id ) {
				try {
					Application_Model_ProfilesMapper::i()->delete($model);
					$this->_helper->flashMessenger(X_Env::_('p_profiles_delete_done'));
				} catch (Exception $e) {
					$this->_helper->flashMessenger(X_Env::_('p_profiles_err_db'));
				}
			} else {
				$this->_helper->flashMessenger(X_Env::_('p_profiles_err_invaliddata'));
			}
		} else {
			$this->_helper->flashMessenger(X_Env::_('p_profiles_err_invaliddata'));
		}
		$this->_helper->redirector('index', 'profiles');
	}
	
	public function testAction() {
		
		$audio = $this->getRequest()->getParam('audio', 'unknown');
		$video = $this->getRequest()->getParam('video', 'unknown');
		$device = $this->getRequest()->getParam('device', 'unknown');
		
		if ( $device == 'unknown') {
			$device = null;
		} else {
			$device = (int) $device;
		}
		if ( $audio == 'unknown' || $video == 'unknown' ) {
			$audiovideo = null;
		} else {
			$audiovideo = "$video+$audio";
		}
		
		$profile = new Application_Model_Profile();
		Application_Model_ProfilesMapper::i()->findBest($audiovideo, $device, null, $profile);

		$return = array('profileId' => $profile->getId());
		
		$this->_helper->json($return);
		
	}

	private function _initForm() {
		if ( $this->form === null) {
			$this->form = new Application_Form_Profile();
			
			$this->form->setAction($this->_helper->url('save', 'profiles'));
			
			// i need to create an array of audio and video codecs and device types
			
			$audioCodecs = array(
				'unknown' => X_Env::_('p_profiles_conf_codectype_unknown'),
				X_VlcShares_Plugins_Helper_StreaminfoInterface::ACODEC_MP3 => 'MP3',
				X_VlcShares_Plugins_Helper_StreaminfoInterface::ACODEC_AAC => 'AAC',
				X_VlcShares_Plugins_Helper_StreaminfoInterface::ACODEC_AC3 => 'AC3',
			);
			
			$videoCodecs = array(
				'unknown' => X_Env::_('p_profiles_conf_codectype_unknown'),
				X_VlcShares_Plugins_Helper_StreaminfoInterface::VCODEC_XVID => 'XVID',
				X_VlcShares_Plugins_Helper_StreaminfoInterface::VCODEC_FLV => 'FLV (H.263)',
				X_VlcShares_Plugins_Helper_StreaminfoInterface::VCODEC_H264 => 'AVC (H.264)',
			);
			
			$devices = array(
				'unknown' => X_Env::_('p_profiles_conf_devicetype_unknown'),
				X_VlcShares_Plugins_Helper_Devices::DEVICE_WIIMC => 'WiiMC',
				X_VlcShares_Plugins_Helper_Devices::DEVICE_ANDROID => 'Android',
				X_VlcShares_Plugins_Helper_Devices::DEVICE_PC => 'Pc',
			);
			
			
			$this->form->audio->setMultiOptions($audioCodecs);
			$this->form->video->setMultiOptions($videoCodecs);
			$this->form->device->setMultiOptions($devices);
			
		}
		return $this->form;	
	}
	
}
