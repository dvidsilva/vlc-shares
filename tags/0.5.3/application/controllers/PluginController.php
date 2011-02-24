<?php

require_once 'X/VlcShares.php';
require_once 'X/Env.php';
require_once 'X/Controller/Action.php';
require_once 'X/Plx.php';

class PluginController extends X_Controller_Action
{

	function init() {
		parent::init();
		
		// if is registered all is ok
		if ( !X_VlcShares_Plugins::broker()->isRegistered('plugininstaller') ) {
			// if it isn't registered, we must check if it's installed
			
			$plugin = new Application_Model_Plugin();
			Application_Model_PluginsMapper::i()->fetchByKey('plugininstaller', $plugin);
			
			// we don't allow to access the page if plugin is installed, but disabled
			if ( $plugin->getId() !== null ) {
				$this->_helper->flashMessenger(X_Env::_('p_plugininstaller_err_pluginnotregistered'));
				$this->_helper->redirector('index', 'manage');
			}
		}
		
	}
	
	function indexAction() {
		
		$plugins = Application_Model_PluginsMapper::i()->fetchByType(Application_Model_Plugin::USER);
		
		$messages = array();
		
		try {
			
			$form = new Application_Form_PluginInstall();
			$form->setAction($this->_helper->url('install', 'plugin'));
			
			//i have to check for permessions
			
		} catch (Exception $e) {
			$messages[] = array('text' => $e->getMessage(), 'type' => 'error');
			$form = '<p>'.X_Env::_('plugin_err_noform').'</p>';
		}
		
		$this->view->messages = array_merge($this->_helper->flashMessenger->getMessages(), $messages);
		$this->view->plugins = $plugins;
		$this->view->form = $form;
		
	}
	
	function installAction() {
		
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		
		if ( $request->isPost() ) {
			
			$form = new Application_Form_PluginInstall();
			$form->setAction($this->_helper->url('install', 'plugin'));
			
			if ( $form->isValid($request->getPost())) {
				// time to copy the valid things
				if ( $form->file->isUploaded() ) {
					try {
						$form->file->receive();
					} catch ( Exception $e) {
						$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_invalidsendfile'), 'type' => 'error'));
						$this->_helper->redirector('index', 'plugin');
						// maybe an exit is needed
					}
					
					$filename = $form->file->getFileName();
					
					if ( $this->_install($filename) ) {
						
						// rebuild update notifier cache
						if ( X_VlcShares_Plugins::broker()->isRegistered('updatenotifier') ) {
							$notifier = X_VlcShares_Plugins::broker()->getPlugins('updatenotifier');
							if ( method_exists($notifier, 'clearLastCheck' ) ) {
								$notifier->clearLastCheck();
							}
						}
						
						$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_install_done'), 'type' => 'info'));
					} else {
						$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_install_errors'), 'type' => 'error'));
					}
					$this->_helper->redirector('index', 'plugin');
				}
			} else {
				$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_invaliddata'), 'type' => 'error'));
				$this->_helper->redirector('index', 'plugin');
			}
		} else {
			$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_invalidrequest'), 'type' => 'error'));
			$this->_helper->redirector('index', 'plugin');
		}		
		
		
	}
	
	function uconfirmAction() {
		
		$key = $this->getRequest()->getParam('key', false);
		if ( $key !== false ) {
			$plugin = new Application_Model_Plugin();
			Application_Model_PluginsMapper::i()->fetchByKey($key, $plugin);
			if ( $plugin->getId() !== null && $plugin->getKey() == $key && $plugin->getType() == Application_Model_Plugin::USER) {
				
				$form = new Application_Form_PluginUConfirm();
				$form->setAction($this->_helper->url('uninstall', 'plugin'));
				$form->key->setValue($key);
				
				$this->view->plugin = $plugin;
				$this->view->form = $form;
			} else {
				$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_invalidkey'), 'type' => 'error'));
				$this->_helper->redirector('index', 'plugin');
			}
		} else {
			$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_invalidkey'), 'type' => 'error'));
			$this->_helper->redirector('index', 'plugin');
		}
	}
	
	function uninstallAction() {
		
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		
		if ( !$request->isPost() ) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_invalidrequest'), 'type' => 'error'));
			$this->_helper->redirector('index', 'plugin');
		}
		
		$form = new Application_Form_PluginUConfirm();

		if ( !$form->isValid($request->getPost())) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_invalidrequest'), 'type' => 'error'));
			$this->_helper->redirector('index', 'plugin');
		}

		$key = $form->getValue('key', false);
		if ( $key === false ) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_invalidkey'), 'type' => 'error'));
			$this->_helper->redirector('index', 'plugin');
		}
		
		$plugin = new Application_Model_Plugin();
		Application_Model_PluginsMapper::i()->fetchByKey($key, $plugin);
		if ( $plugin->getId() === null || $plugin->getKey() != $key || $plugin->getType() != Application_Model_Plugin::USER) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_invalidplugin'), 'type' => 'error'));
			$this->_helper->redirector('index', 'plugin');
		}

		// time to get uninstall informations
		
		$manifest = APPLICATION_PATH . '/../data/plugin/_uninstall/'.$plugin->getKey().'/manifest.xml';
		if ( file_exists($manifest) ) {
			try {
				$this->_uninstall($manifest);
				
				// rebuild update notifier cache
				if ( X_VlcShares_Plugins::broker()->isRegistered('updatenotifier') ) {
					$notifier = X_VlcShares_Plugins::broker()->getPlugins('updatenotifier');
					if ( method_exists($notifier, 'clearLastCheck' ) ) {
						$notifier->clearLastCheck();
					}
				}
				
				
				// all done, continue
			} catch (Exception $e) {
				$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_uninstall_processingmanifest').": {$e->getMessage()}", 'type' => 'error'));
				$this->_helper->redirector('index', 'plugin');
			}
		} else {
			$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_warn_uninstall_manifestnotfound'), 'type' => 'warning'));
		}
		
		Application_Model_PluginsMapper::i()->delete($plugin);
		
		$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_uninstall_done'), 'type' => 'info'));
		$this->_helper->redirector('index', 'plugin');
		
	}
 
	
	private function _install($filepath) {

		try {
			// unzip and manifest parse
			$egg = X_Egg::factory($filepath, APPLICATION_PATH . '/../', APPLICATION_PATH . '/../data/plugin/tmp/');
			
			$pluginKey = $egg->getKey();
			
			// first we must check if key already exists in the db
			$plugin = new Application_Model_Plugin();
			Application_Model_PluginsMapper::i()->fetchByKey($pluginKey, $plugin);
			if ( $plugin->getId() !== null ) {
				throw new Exception(X_Env::_('plugin_err_installerror_keyexists'). ": $pluginKey");
			}

			// time to check if plugin support this vlc-shares version
			$vFrom = $egg->getCompatibilityFrom();
			$vTo = $egg->getCompatibilityTo();
			if ( version_compare(X_VlcShares::VERSION_CLEAN, $vFrom, '<')
					|| ( $vTo !== null && version_compare(X_VlcShares::VERSION_CLEAN, $vTo, '>=')) ) {
						
				throw new Exception(X_Env::_('plugin_err_installerror_unsupported'). ": $vFrom - $vTo");
			}
			
			// copy the files: first check if some file exists...
			$toBeCopied = array();
			foreach ($egg->getFiles() as $file) {
				/* @var $file X_Egg_File */
				if ( !false && file_exists($file->getDestination()) ) {
					throw new Exception(X_Env::_('plugin_err_installerror_fileexists'). ": {$file->getDestination()}");
				}
				
				if ( !file_exists($file->getSource())) {
					throw new Exception(X_Env::_('plugin_err_installerror_sourcenotexists'). ": {$file->getSource()}");
				}
				
				$toBeCopied[] = array(
					'src' => $file->getSource(),
					'dest' => $file->getDestination() 
				);
			}
			
			// before copy act, i must be sure to be able to revert changes
			$plugin = new Application_Model_Plugin();
			$plugin->setLabel($egg->getLabel())
				->setKey($pluginKey)
				->setDescription($egg->getDescription())
				->setFile($egg->getFile())
				->setClass($egg->getClass())
				->setType(Application_Model_Plugin::USER)
				->setVersion($egg->getVersion());
				
			Application_Model_PluginsMapper::i()->save($plugin);
			
			// so i must copy uninstall information inside a uninstall dir in data
			
			$dest = APPLICATION_PATH . '/../data/plugin/_uninstall/' . $pluginKey;
			// i have to create the directory
			if ( !mkdir($dest, 0777, true) ) {
				throw new Exception(X_Env::_('plugin_err_installerror_uninstalldircreation').": $dest");
			}
			if ( !copy($egg->getManifestFile(), "$dest/manifest.xml") ) {
				throw new Exception(X_Env::_('plugin_err_installerror_uninstallmanifestcopy').": ".$egg->getManifestFile(). " -> $dest/manifest.xml");
			}
			
			$uninstallSql = $egg->getUninstallSQL();
			if ( $uninstallSql !== null && file_exists($uninstallSql) ) {
				if ( !copy($uninstallSql, "$dest/uninstall.sql") ) {
					throw new Exception(X_Env::_('plugin_err_installerror_uninstallsqlcopy').": $dest");
				}
			}
			
			// ... then copy
			foreach ($toBeCopied as $copyInfo) {
				if ( !file_exists(dirname($copyInfo['dest'])) ) {
					@mkdir(dirname($copyInfo['dest']), 0777, true);
				}
				if ( !copy($copyInfo['src'], $copyInfo['dest']) ) {
					$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_installerror_copyerror').": <br/>".$copyInfo['src'].'<br/>'.$copyInfo['dest'], 'type' => 'error'));
				}
			}
			
			// change database
			$installSql = $egg->getInstallSQL();
			if ( $installSql !== null && file_exists($installSql) ) {
		    	try {
		    		$dataSql = file_get_contents($installSql);
		    		if ( trim($dataSql) !== '' ) {
						$bootstrap = $this->getFrontController()->getParam('bootstrap');
				    	$db = $bootstrap->getResource('db'); 
				    	$db->getConnection()->exec($dataSql);
		    		}
		    	} catch ( Exception $e ) {
		    		X_Debug::e("DB Error while installind: {$e->getMessage()}");
		    		$this->_helper->flashMessenger(X_Env::_('plugin_err_installerror_sqlerror').": {$e->getMessage()}");
		    	}
			}
			$egg->cleanTmp();
			unlink($filepath);
			return true;
		} catch ( Exception $e) {
			if ( $egg !== null ) $egg->cleanTmp();
			// delete the uploaded file
			unlink($filepath);
			$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_installerror').": ".$e->getMessage(), 'type' => 'error'));
			return false;
		}
	}
	
	private function _uninstall($manifest) {
		
		/* @var $egg X_Egg */
		$egg = X_Egg::factory($manifest, APPLICATION_PATH.'/../');
		
		foreach ( $egg->getFiles() as $file ) {
			/* @var $file X_Egg_File */
			@unlink($file->getDestination());
		}
		
		$uninstallSql = $egg->getUninstallSQL();
		if ( $uninstallSql !== null && file_exists(dirname($manifest)."/uninstall.sql") ) {
	    	try {
	    		$dataSql = file_get_contents(dirname($manifest)."/uninstall.sql");
	    		if ( trim($dataSql) !== '' ) {
					$bootstrap = $this->getFrontController()->getParam('bootstrap');
			    	$db = $bootstrap->getResource('db'); 
			    	$db->getConnection()->exec($dataSql);
	    		}
	    	} catch ( Exception $e ) {
	    		X_Debug::e("DB Error while uninstalling: {$e->getMessage()}");
	    		$this->_helper->flashMessenger(X_Env::_('plugin_err_uninstallerror_sqlerror').": {$e->getMessage()}");
	    	}
	    	@unlink(dirname($manifest)."/uninstall.sql");
		}
		
		@unlink($manifest);
		@rmdir(dirname($manifest));
		
		return true;
	}
}

