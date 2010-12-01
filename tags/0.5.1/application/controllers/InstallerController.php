<?php


class InstallerController extends Zend_Controller_Action
{
	
	function init() {
		parent::init();
		if ( !X_VlcShares_Plugins::broker()->isRegistered('firstrunsetup') ) {
			$this->_helper->redirector('index', 'manage');
		}
		
	}
	
    public function indexAction() {

    	$languages = array();
    	foreach ( new DirectoryIterator(APPLICATION_PATH ."/../languages/") as $entry ) {
    		if ( $entry->isFile() && pathinfo($entry->getFilename(), PATHINFO_EXTENSION) == 'ini' ) {
    			$languages[$entry->getFilename()] = pathinfo($entry->getFilename(), PATHINFO_FILENAME);
    		}
    	}
    	
    	$this->view->messages = array_merge($this->_helper->flashMessenger->getMessages(), $this->_helper->flashMessenger->getCurrentMessages()) ;
    	$this->view->languages = $languages;
    	
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
	    	$this->_helper->redirector('configs', 'manage');
	    	
    	} catch ( Exception $e) {
    		$this->_helper->flashMessenger(X_Env::_('installer_err_db').": {$e->getMessage()}");
    	}
    	
    }
    
}

