<?php

require_once 'X/Controller/Action.php';
require_once 'Zend/Config.php';
require_once 'Zend/Config/Xml.php';
require_once 'Zend/Config/Writer/Xml.php';

class BackupperController extends X_Controller_Action
{
	
	function init() {
		parent::init();
		if ( !X_VlcShares_Plugins::broker()->isRegistered('backupper') ) {
			$this->_helper->flashMessenger(X_Env::_('p_backupper_err_pluginnotregistered'));
			$this->_helper->redirector('index', 'manage');
		}
		
	}
	
    public function indexAction() {

    	// i need to generate a list of backupable items
    	// reading plugin list
    	// Core settings must be insered in first position
    	
    	$backuppables = array(
    		'backupper' => X_Env::_('p_backupper_backup_core')
    	);
    	
    	$pluginList = X_VlcShares_Plugins::broker()->getPlugins();
    	
    	foreach ($pluginList as $pluginId => $plugin) {
    		if ($plugin instanceof X_VlcShares_Plugins_BackuppableInterface) {
    			// plugin is backupable
    			$translationKey = explode('_', get_class($plugin));
    			$translationKey = strtolower(array_pop($translationKey));
    			$backuppables[$pluginId] = X_Env::_("p_{$translationKey}_backupper_itemlabel");
    		}
    	}
    	
    	// i need the list of backup files
    	$restorables = array();
    	
    	try {
    		$backupDir = new DirectoryIterator(APPLICATION_PATH . "/../data/backupper/");
    		
    		foreach ($backupDir as $entry) {
    			if ( $entry->isFile() && pathinfo($entry->getFilename(), PATHINFO_EXTENSION) == 'xml' && X_Env::startWith($entry->getFilename(), 'backup_') ) {
    				$restorables[$entry->getFilename()] = $entry->getFilename();
    				X_Debug::i("Valid backup file: $entry");
    			}
    		}
    		
    	} catch ( Exception $e) {
    		X_Debug::e("Error while parsing backupper data directory: {$e->getMessage()}");
    	}
    	
    	krsort($restorables);
    	
    	// look in plugin config for alert.enabled status
    	$showConfig = new Application_Model_Config();
    	Application_Model_ConfigsMapper::i()->fetchByKey("backupper.alert.enabled", $showConfig);
    	
    	if ( $showConfig->getId() == null ) {
    		$showActiveAlert = true; 
    	} else {
    		$showActiveAlert = !((bool) $showConfig->getValue());
    	}
    	
    	$this->view->showActiveAlert = $showActiveAlert;
    	$this->view->backuppables = $backuppables;
    	$this->view->restorables = $restorables;
    	$this->view->messages = $this->_helper->flashMessenger->getMessages();
    	
    }
    
    function backupAction() {
    	
    	//$message = var_export($this->getRequest()->getPost(), true);
    	
    	ignore_user_abort();

    	/* @var $request Zend_Controller_Request_Http */
    	$request = $this->getRequest();
    	$fastAction = $request->getParam('a', false);
    	
    	if ( $request->isPost() || $fastAction !== false ) {
    		$components = $request->getPost('components', array());
    		$plugins = X_VlcShares_Plugins::broker()->getPlugins();
    		$items = array();
    		foreach ($plugins as $pId => $plugin ) {
    			if (( array_key_exists($pId, $components) && ((bool) $components[$pId]) ) || $fastAction == 'all' || $fastAction == $pId) {
    				if ( $plugin instanceof X_VlcShares_Plugins_BackuppableInterface ) {
    					//$toBackup[$pId] = $plugin;
    					$items[$pId] = $plugin->getBackupItems();
    					X_Debug::i('Backuppable plugin: '.$pId);
    				} elseif ($fastAction != 'all') {
    					$this->_helper->flashMessenger(array('text' => X_Env::_('p_backupper_backup_invalidplugin'). ": $pId", 'type' => 'error' ));
    				}
    			} else {
    				//X_Debug::i('Discarded plugin: '.$pId);
    			}
    		}
    		
    		//$this->_helper->flashMessenger(var_export($items, true));
    		
    		if ( count($items) ) {
    			$writer = new Zend_Config_Writer_Xml();
    			
    			$date = date("Y-m-d_H-i-s");

    			$type = $fastAction !== false ? $fastAction : 'custom';
    			
    			$data['metadata'] = array(
    				'version'	=> X_VlcShares::VERSION,
    				'created'	=> date('d/m/Y H:i:s')
    			);
    			
    			$data['plugins'] = $items;
    			
    			$filename = APPLICATION_PATH . "/../data/backupper/backup_{$date}_{$type}.xml";
    			$configs = new Zend_Config($data);
    			$writer->setFilename($filename);
    			
    			try {
    				$writer->write(null, $configs, true);
    				$this->_helper->flashMessenger(array('text' => X_Env::_('p_backupper_backup_done'), 'type' => 'info' ));
    			} catch (Exception $e) {
    				$this->_helper->flashMessenger(array('text' => X_Env::_('p_backupper_err_writefile').": {$e->getMessage()}", 'type' => 'error' ));
    			}
    			
    		} else {
    			$this->_helper->flashMessenger(array('text' => X_Env::_('p_backupper_backup_nobackupactionneeded'), 'type' => 'warning' ));
    		}
    	}
    	
    	//$this->_helper->flashMessenger($message);
    	$this->_helper->redirector('index', 'backupper');
    	
    }
    
    function rinfoAction() {
    	
    	/* @var $request Zend_Controller_Request_Http */
    	$request = $this->getRequest();
    	//$fastAction = $request->getParam('a', false);
    	
    	if ( ! ( $request->isPost() 
    			&& ($file = $request->getPost('file', false)) !== false
    			&& X_Env::startWith(realpath(APPLICATION_PATH . "/../data/backupper/$file"), realpath(APPLICATION_PATH . "/../data/backupper/")) // this ensure no ../
    			&& file_exists(APPLICATION_PATH . "/../data/backupper/$file") ) ) {
    				
    		$this->_helper->flashMessenger(array('text' => X_Env::_('p_backupper_err_invalidrestorefile'), 'type' => 'error' ));
    		$this->_helper->redirector('index', 'backupper');
    	}
	    	
		$backup = new Zend_Config_Xml(file_get_contents(APPLICATION_PATH . "/../data/backupper/$file"));
		
		try {
			
			$plugins = array();
	    	$pluginList = X_VlcShares_Plugins::broker()->getPlugins();
	    	$backupPlugins = $backup->plugins->toArray();
	    	
	    	foreach ($pluginList as $pluginId => $plugin) {
	    		if ($plugin instanceof X_VlcShares_Plugins_BackuppableInterface) {
	    			// plugin is backupable
	    			$translationKey = explode('_', get_class($plugin));
	    			$translationKey = strtolower(array_pop($translationKey));
	    			if ( array_key_exists($pluginId, $backupPlugins) ) {
	    				$plugins[$pluginId] = X_Env::_("p_{$translationKey}_backupper_itemlabel");
	    			}
	    		}
	    	}
	    	$this->view->components = $plugins;
			$this->view->file = $file;
			$this->view->created = $backup->metadata->created;
			$this->view->version = $backup->metadata->version;
			
		} catch (Exception $e) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_backupper_err_malformedrestorefile'), 'type' => 'error' ));
		}
		
    }
    
    
    function restoreAction() {
    	
    	ignore_user_abort();
    	
    	/* @var $request Zend_Controller_Request_Http */
    	$request = $this->getRequest();
    	
    	if ( $request->isPost() ) {
    		
    		$file = $request->getPost('file', false);
    		if ( $file === false 
    				|| !X_Env::startWith(realpath(APPLICATION_PATH . "/../data/backupper/$file"), realpath(APPLICATION_PATH . "/../data/backupper/")) // this ensure no ../
    				|| !file_exists(APPLICATION_PATH . "/../data/backupper/$file")) {
    				
    			$this->_helper->flashMessenger(array('text' => X_Env::_('p_backupper_err_invalidrestorefile'), 'type' => 'warning' ));
    			$this->_helper->redirector('index', 'backupper');
    		}
    		
    		try {
    			/* @var $backuppedData Zend_Config_Xml */
    			$backuppedData = new Zend_Config_Xml(APPLICATION_PATH . "/../data/backupper/$file");
    		} catch (Exception $e) {
    			$this->_helper->flashMessenger(array('text' => X_Env::_('p_backupper_err_malformedrestorefile'), 'type' => 'error' ));
    			$this->_helper->redirector('index', 'backupper');
    		}
    		
    		//die('<pre>'.var_export($backuppedData->toArray(), true).'</pre>');
    		
    		$components = $request->getPost('components', array());
    		
    		if ( count($components) ) {
	    		$plugins = X_VlcShares_Plugins::broker()->getPlugins();
	    		$items = array();
	    		foreach ($plugins as $pId => $plugin ) {
	    			if ( array_key_exists($pId, $components) && ((bool) $components[$pId]) )  {
	    				if ( $plugin instanceof X_VlcShares_Plugins_BackuppableInterface ) {
	    					//$toBackup[$pId] = $plugin;
	    					try {
	    						$returned = $plugin->restoreItems($backuppedData->plugins->$pId->toArray());
	    						X_Debug::i("Plugins $pId restored");
	    						if ( $returned ) {
	    							$this->_helper->flashMessenger(array('text' => $returned, 'type' => 'info' ));
	    						} else {
	    							$this->_helper->flashMessenger(array('text' => X_Env::_('p_backupper_restore_done_plugin').": $pId", 'type' => 'info' ));
	    						}
	    					} catch (Exception $e) {
	    						X_Debug::e("Error restoring $pId: {$e->getMessage()}");
	    						$this->_helper->flashMessenger(array('text' => X_Env::_('p_backupper_err_pluginnotrestored').": $pId, {$e->getMessage()}", 'type' => 'error' ));
	    					}
	    				}
	    			}
	    		}
	    		$this->_helper->flashMessenger(array('text' => X_Env::_('p_backupper_restore_done'), 'type' => 'info' ));
    		} else {
    			$this->_helper->flashMessenger(array('text' => X_Env::_('p_backupper_restore_norestoreactionneeded'), 'type' => 'warning' ));
    		}
    	}
    	
    	$this->_helper->redirector('index', 'backupper');
    	
    	
    }
    
    function alertAction() {
    	
    	$status = $this->getRequest()->getParam('status', false);
    	
    	$config = new Application_Model_Config();
    	Application_Model_ConfigsMapper::i()->fetchByKey("backupper.alert.enabled", $config);
    	
    	if ( $config->getId() == null ) {
    		// i need to add a new config, yeah!
    		$config->setKey('backupper.alert.enabled')
    			->setValue(1)
    			->setDefault(1)
    			->setSection('plugins')
    			->setType(Application_Model_Config::TYPE_BOOLEAN);
    	}
    	
    	switch ($status) {
    		case 'on':
    			$config->setValue(1);
    			break;
    		case 'off':
    			$config->setValue(0);
    			break;
    		default:
    			$this->_helper->flashMessenger(array('text' => X_Env::_('p_backupper_err_unknownstatus'), 'type' => 'error' ));
    			$this->_helper->redirector('index', 'backupper');
    			break;
    	}
    	
    	try {
    		Application_Model_ConfigsMapper::i()->save($config);
    		$this->_helper->flashMessenger(array('text' => X_Env::_('p_backupper_alertstatuschanged'), 'type' => 'info' ));
    	} catch (Exception $e) {
    		X_Debug::e('Unable to store alert.enabled status: '.$e->getMessage());
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_backupper_err_dberror').": {$e->getMessage()}", 'type' => 'error' ));
    	}
    	$this->_helper->redirector('index', 'backupper');
    	
    }
    
}

