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
    				$restorables[]= $entry->getFilename();
    				X_Debug::i("Valid backup file: $entry");
    			}
    		}
    		
    	} catch ( Exception $e) {
    		X_Debug::e("Error while parsing backupper data directory: {$e->getMessage()}");
    	}
    	
    	$this->view->backuppables = $backuppables;
    	$this->view->restorables = $restorables;
    	$this->view->messages = $this->_helper->flashMessenger->getMessages();
    	
    }
    
    function backupAction() {
    	
    	//$message = var_export($this->getRequest()->getPost(), true);

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
    			
    			$date = date("Ymd_His");

    			$data['metadata'] = array(
    				'version'	=> X_VlcShares::VERSION,
    				'created'	=> date('d/m/Y H:i:s')
    			);
    			
    			$data['plugins'] = $items;
    			
    			$filename = APPLICATION_PATH . "/../data/backupper/backup_{$date}.xml";
    			$configs = new Zend_Config($data);
    			$writer->setFilename($filename);
    			
    			try {
    				$writer->write(null, $configs, true);
    				$this->_helper->flashMessenger(array('text' => X_Env::_('p_backupper_backup_done'), 'type' => 'info' ));
    			} catch (Exception $e) {
    				$this->_helper->flashMessenger(array('text' => X_Env::_('p_backupper_err_writefile').": {$e->getMessage()}", 'type' => 'error' ));
    			}
    			
    		} else {
    			$this->_helper->flashMessenger(array('text' => X_Env::_('p_backupper_backup_nobackupactionneeded'), 'type' => 'info' ));
    		}
    	}
    	
    	//$this->_helper->flashMessenger($message);
    	$this->_helper->redirector('index', 'backupper');
    	
    }
    
}

