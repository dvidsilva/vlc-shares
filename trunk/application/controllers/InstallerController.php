<?php


class InstallerController extends X_Controller_Action
{
	
	function init() {
		parent::init();
		if ( !X_VlcShares_Plugins::broker()->isRegistered('firstrunsetup') ) {
			$this->_helper->redirector('index', 'manage');
		}
	}
	
    public function indexAction() {
    	
    	$lang = $this->getRequest()->getParam('lang', false);
    	

    	$languages = array();
    	foreach ( new DirectoryIterator(APPLICATION_PATH ."/../languages/") as $entry ) {
    		if ( $entry->isFile() && pathinfo($entry->getFilename(), PATHINFO_EXTENSION) == 'ini' ) {
    			if ( count(explode('.',$entry->getFilename())) == 2 ) {
    				$languages[$entry->getFilename()] = pathinfo($entry->getFilename(), PATHINFO_FILENAME);
    			}
    		}
    	}

    	if ( $lang != false ) {
    		// cleanup from ./ and ../
    		$lang = str_replace(array('.', '/'), '', $lang);
    		if ( file_exists(APPLICATION_PATH ."/../languages/$lang.ini") && array_key_exists("$lang.ini", $languages) ) {
	    		$translation = new Zend_Translate('ini', APPLICATION_PATH ."/../languages/$lang.ini");
	    		X_Env::initTranslator($translation);
    		} else {
    			$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('installer_invalid_language') ));
    			$lang = false;
    		}
    	}
    	
    	
    	$form = new Application_Form_Installer();
    	$form->setAction($this->_helper->url('save', 'installer'));
    	try {
    		$form->lang->setMultiOptions($languages);
    		$form->setDefault('lang', $lang !== false ? "$lang.ini" : 'en_GB.ini');
    	} catch (Exception $e) {
    		// WTF?
    	}
    	
    	$this->view->messages = array_merge($this->_helper->flashMessenger->getMessages(), $this->_helper->flashMessenger->getCurrentMessages()) ;
    	$this->_helper->flashMessenger->clearCurrentMessages();
    	$this->view->languages = $languages;
    	$this->view->form = $form;
    	
    }
    
    public function saveAction() {
    	
    	$lang = $this->getRequest()->getParam('lang', false);
    	$lang = $lang !== false ? str_replace('../', '', $lang) : $lang;
    	if ( $lang !== false && file_exists(APPLICATION_PATH ."/../languages/$lang") ) {
    		$config = new Application_Model_Config();
    		Application_Model_ConfigsMapper::i()->fetchByKey('languageFile', $config);
    		if ( $config->getId() !== null ) {
    			$config->setValue($lang);
    			try {
    				Application_Model_ConfigsMapper::i()->save($config);
	    			$this->_helper->flashMessenger(X_Env::_('installer_language_done'));
	    			$this->_helper->redirector('execute');
    			} catch (Exception $e) {
    				$this->_helper->flashMessenger(X_Env::_("installer_err_db").": {$e->getMessage()}");
    			}
    		}
    	}
    	$this->_helper->flashMessenger(X_Env::_('installer_invalid_language'));
    	$this->_helper->redirector('index');
    }
    
    
    public function executeAction() {

    	try {
    		
	    	if ( false && file_exists(APPLICATION_PATH.'/../scripts/update.sqlite.sql') ) {

	    		// an update is needed
	    		// script should use transition
	    		$dataSql = file_get_contents(APPLICATION_PATH.'/../scripts/update.sqlite.sql');
	    		
				$bootstrap = $this->getFrontController()->getParam('bootstrap');
		    	$db = $bootstrap->getResource('db'); 
	    		
		    	$db->getConnection()->exec($dataSql);
		    	
	    	}
	    	
	    	try {

		    	if ( file_exists(APPLICATION_PATH.'/../scripts/backup.sqlite.sql') ) {
	
		    		// an update is needed
		    		// script should use transition
		    		$dataSql = file_get_contents(APPLICATION_PATH.'/../scripts/backup.sqlite.sql');
		    		
		    		if ( trim($dataSql) !== '' ) {
						$bootstrap = $this->getFrontController()->getParam('bootstrap');
				    	$db = $bootstrap->getResource('db'); 
			    		
				    	$db->getConnection()->exec($dataSql);
		    		}
		    	}
	    		
	    	} catch ( Exception $e ) {
	    		X_Debug::e("DB Error while restoring: {$e->getMessage()}");
	    		$this->_helper->flashMessenger(X_Env::_('installer_err_db').": {$e->getMessage()}");
	    	}
	    	
	    	
	    	// after all, i will delete first run plugin from the db
	    	$plugin = new Application_Model_Plugin();
	    	Application_Model_PluginsMapper::i()->fetchByClass('X_VlcShares_Plugins_FirstRunSetup', $plugin);
	    	//Application_Model_PluginsMapper::i()->delete($plugin);
			$plugin->setEnabled(false);
			Application_Model_PluginsMapper::i()->save($plugin);
	    	
	    	$this->_helper->flashMessenger(X_Env::_('installer_op_completed'));
	    	
	    	// all done, redirect to config page
	    	$this->_helper->redirector('index', 'configs');
	    	
    	} catch ( Exception $e) {
    		$this->_helper->flashMessenger(X_Env::_('installer_err_db').": {$e->getMessage()}");
    	}
    	
    }
    
}

