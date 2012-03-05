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
		
		$filter = $this->getRequest()->getParam('filter', 'all');
		$plugins = array();
		
		switch ($filter) {
			
			case 'installed': $plugins = Application_Model_PluginsMapper::i()->fetchByType(Application_Model_Plugin::USER);
				break;
			
			case 'disabled': $plugins = Application_Model_PluginsMapper::i()->fetchAll();
				// filter out disabled
				foreach ($plugins as $key => $value) {
					/* @var $value Application_Model_Plugin */
					if ( $value->isEnabled() ) {
						unset($plugins[$key]);
					}
				}
				break;
				
			case 'enabled': $plugins = Application_Model_PluginsMapper::i()->fetchAll();
				// filter out disabled
				foreach ($plugins as $key => $value) {
					/* @var $value Application_Model_Plugin */
					if ( !$value->isEnabled() ) {
						unset($plugins[$key]);
					}
				}
				break;
			
			case 'all':
			default: $plugins = Application_Model_PluginsMapper::i()->fetchAll();
				break;
		}

		$csrf = new Zend_Form_Element_Hash('csrf', array(
				'salt'  => __CLASS__
		));
		$csrf->initCsrfToken();
		 
		$this->view->csrf = $csrf->getHash();
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
		$this->view->plugins = $plugins;
		$this->view->filter = $filter;
		
	}
	
	function availablesAction() {
		
		try {
			/* @var $updateNotifier X_VlcShares_Plugins_UpdateNotifier */
			$updateNotifier = X_VlcShares_Plugins::broker()->getPlugins('updatenotifier');
			$updateNotifier->clearLastCheck();
				
			$foundplugins = $updateNotifier->getLastPlugins();
		
			$installablePlugins = array();
				
			foreach ($foundplugins as $key => $versions) {

				$plugin = null;
				
				// if a plugin key is already installer, skip it
				if ( X_VlcShares_Plugins::broker()->isRegistered($key) ) {
					$plugin = new Application_Model_Plugin();
					Application_Model_PluginsMapper::i()->fetchByKey($key, $plugin);
				}
				
				foreach ($versions as $version) {

					// check for valid version cMin cMax
					if ( version_compare(X_VlcShares::VERSION_CLEAN, $version['cMin'], '<')
							|| version_compare(X_VlcShares::VERSION_CLEAN, $version['cMax'], '>=') ) {
		
						continue;
					}
					
					// check if version is > that currently installed
					if ( $plugin ) {
						if ( $plugin->getVersion() == $version['version'] ) {
							continue;
						}
					}
						
					$installablePlugins[$key] = $version;
						
					// doesn't continue in versions traversal
					break;
				}
		
			}
		
			ksort($installablePlugins);
			
		} catch (Exception $e) {
			$installablePlugins = false;
		}
		
		
		$this->view->installablePlugins = $installablePlugins;
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
		//$this->view->plugins = $plugins;
		//$this->view->form = $form;		
		
	}
	
	public function disableAction() {
		 
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		$pluginId = $request->getParam('key', false);
		$plugin = new Application_Model_Plugin();
	
		$csrfValue = $request->getParam('csrf', false);
	
		$csrf = new Zend_Form_Element_Hash('csrf', array(
				'salt'  => __CLASS__
		));
	
		if ( $csrf->isValid($csrfValue) ) {
	
			if ( $pluginId !== false ) {
				Application_Model_PluginsMapper::i()->fetchByKey($pluginId, $plugin);
				if ( $plugin->getId() != null && $plugin->getKey() == $pluginId ) {
					if ( $plugin->getType() != Application_Model_Plugin::SYSTEM ) {
						try {
							$plugin->setEnabled(false);
							Application_Model_PluginsMapper::i()->save($plugin);
							$this->_helper->flashMessenger(X_Env::_('configs_plugins_plugindisabled'));
						} catch ( Exception $e) {
							$this->_helper->flashMessenger(X_Env::_('configs_plugins_err_db').": {$e->getMessage()}");
						}
					} else {
						$this->_helper->flashMessenger(X_Env::_('configs_plugins_err_pluginId_notdisable'));
					}
				} else {
					$this->_helper->flashMessenger(X_Env::_('configs_plugins_err_pluginId_unknown'));
				}
			} else {
				$this->_helper->flashMessenger(X_Env::_('configs_plugins_err_pluginId_missing'));
			}
		} else {
			$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('configs_plugins_err_invalidtoken')));
		}
			
		$redirect = $request->getParam('r', 'all');
		$this->_helper->redirector('index', 'plugin', null,  array('filter' => $redirect));
				 
	}
	
	public function enableAction() {
	
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		$pluginId = $request->getParam('key', false);
		$plugin = new Application_Model_Plugin();
	
		$csrfValue = $request->getParam('csrf', false);
	
		$csrf = new Zend_Form_Element_Hash('csrf', array(
				'salt'  => __CLASS__
		));
	
		if ( $csrf->isValid($csrfValue) ) {
			if ( $pluginId !== false ) {
				Application_Model_PluginsMapper::i()->fetchByKey($pluginId, $plugin);
				if ( $plugin->getId() != null && $plugin->getKey() == $pluginId ) {
					if ( $plugin->getType() != Application_Model_Plugin::SYSTEM ) {
						try {
							$plugin->setEnabled(true);
							Application_Model_PluginsMapper::i()->save($plugin);
							$this->_helper->flashMessenger(X_Env::_('configs_plugins_pluginenabled'));
						} catch ( Exception $e) {
							$this->_helper->flashMessenger(X_Env::_('configs_plugins_err_db').": {$e->getMessage()}");
						}
					} else {
						$this->_helper->flashMessenger(X_Env::_('configs_plugins_err_pluginId_notenable'));
					}
				} else {
					X_Debug::i(print_r($plugin, true));					
					$this->_helper->flashMessenger(X_Env::_('configs_plugins_err_pluginId_unknown'));
				}
			} else {
				$this->_helper->flashMessenger(X_Env::_('configs_plugins_err_pluginId_missing'));
			}
		} else {
			$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('configs_plugins_err_invalidtoken')));
		}
	
		$redirect = $request->getParam('r', 'all');
		$this->_helper->redirector('index', 'plugin', null,  array('filter' => $redirect));
		 
	}	
	
	function manualAction() {
		
		$messages = array();
		
		try {
				
			$form = new Application_Form_PluginInstall();
			$form->setAction($this->_helper->url('install', 'plugin'));
				
			//i have to check for permessions
				
		} catch (Exception $e) {
			$messages[] = array('text' => $e->getMessage(), 'type' => 'error');
			$form = '<p>'.X_Env::_('plugin_err_noform').'</p>';
		}
		
		$this->view->form = $form;
		$this->view->messages = array_merge($this->_helper->flashMessenger->getMessages(), $messages);
		
	}
	
	function menuAction() {
		
		$filter = $this->getRequest()->getParam('filter', 'all');

		$items = array(
			'all' => array(
					'label' => X_Env::_('plugin_menu_item_all'),
					'params' => array(
							'controller' 	=>	'plugin',
							'action'		=>	'index',
						)
				),
			'installed' => array(
					'label' => X_Env::_('plugin_menu_item_installed'),
					'params' => array(
							'controller' 	=>	'plugin',
							'action'		=>	'index',
							'filter'		=>	'installed'
					)
			),
				
			//'space1' =>	null,				
			'enabled' => array(
					'label' => X_Env::_('plugin_menu_item_enabled'),
					'params' => array(
							'controller' 	=>	'plugin',
							'action'		=>	'index',
							'filter'		=>	'enabled'
						)
				),
			'disabled' => array(
					'label' => X_Env::_('plugin_menu_item_disabled'),
					'params' => array(
							'controller' 	=>	'plugin',
							'action'		=>	'index',
							'filter'		=>	'disabled'
					)
			),
				
			'space2' =>	null,
				
			'availables' => array(
					'label' => X_Env::_('plugin_menu_item_availables'),
					'params' => array(
							'controller' 	=>	'plugin',
							'action'		=>	'availables',
						)
				),
			'manual' => array(
					'label' => X_Env::_('plugin_menu_item_manual'),
					'params' => array(
							'controller' 	=>	'plugin',
							'action'		=>	'manual',
						)
				),
		);
		
		$this->view->items = $items;
		$this->view->selected = $filter;
		
		
		
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
	

	function iconfirmAction() {
		
		$key = $this->getRequest()->getParam('key', false);
		
		try {
			/* @var $updateNotifier X_VlcShares_Plugins_UpdateNotifier */
			$updateNotifier = X_VlcShares_Plugins::broker()->getPlugins('updatenotifier');
			$foundplugins = $updateNotifier->getLastPlugins();
	
			$installablePlugins = array();
			
			foreach ($foundplugins as $_key => $versions) {
				
				// if a plugin key is already installer, skip it
				if ( X_VlcShares_Plugins::broker()->isRegistered($_key) ) continue;
				
				foreach ($versions as $version) {
					
					// check for valid version cMin cMax
					if ( version_compare(X_VlcShares::VERSION_CLEAN, $version['cMin'], '<')
						|| version_compare(X_VlcShares::VERSION_CLEAN, $version['cMax'], '>=') ) {
	
						continue;
					}
					
					$installablePlugins[$_key] = $version;
					
					// doesn't continue in versions traversal
					break;
				} 
			}
						
		} catch (Exception $e) {
			$installablePlugins = array();
		}		
		
		
		if ( $key !== false ) {
			
			
			if ( array_key_exists($key, $installablePlugins)) {
				
				$form = new Application_Form_PluginIConfirm(); 
				$form->setAction($this->_helper->url('installurl', 'plugin'));
				$form->key->setValue($key);

				$this->view->pluginKey = $key;
				$this->view->plugin = $installablePlugins[$key];
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
	
	function installurlAction() {
		
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		
		if ( $request->isPost() ) {
			
			$form = new Application_Form_PluginIConfirm();
			$form->setAction($this->_helper->url('installurl', 'plugin'));
			
			if ( $form->isValid($request->getPost())) {
				
				try {
					/* @var $updateNotifier X_VlcShares_Plugins_UpdateNotifier */
					$updateNotifier = X_VlcShares_Plugins::broker()->getPlugins('updatenotifier');
					$foundplugins = $updateNotifier->getLastPlugins();
			
					$installablePlugins = array();
					
					foreach ($foundplugins as $key => $versions) {
						
						// if a plugin key is already installer, skip it
						if ( X_VlcShares_Plugins::broker()->isRegistered($key) ) continue;
						
						foreach ($versions as $version) {
							
							// check for valid version cMin cMax
							if ( version_compare(X_VlcShares::VERSION_CLEAN, $version['cMin'], '<')
								|| version_compare(X_VlcShares::VERSION_CLEAN, $version['cMax'], '>=') ) {
			
								continue;
							}
							
							$installablePlugins[$key] = $version;
							
							// doesn't continue in versions traversal
							break;
						} 
					}
								
				} catch (Exception $e) {
					$installablePlugins = array();
				}		
				
				if ( array_key_exists($form->getValue('key'), $installablePlugins) ) {
					
					$filename = $installablePlugins[$form->getValue('key')]['download'];
					
					if ( $this->_install($filename, true) ) {
						
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
					$this->_helper->redirector('availables', 'plugin');
				} else {
					$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_invalidkey'), 'type' => 'error'));
					$this->_helper->redirector('availables', 'plugin');
				}
			} else {
				$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_invaliddata'), 'type' => 'error'));
				$this->_helper->redirector('availables', 'plugin');
			}
		} else {
			$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_invalidrequest'), 'type' => 'error'));
			$this->_helper->redirector('availables', 'plugin');
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
 
	
	private function _install($filepath, $isUrl = false) {

		try {

    		/* @var $pluginInstaller X_VlcShares_Plugins_PluginInstaller */
    		$pluginInstaller = X_VlcShares_Plugins::broker()->getPlugins('plugininstaller');
			
    		$pluginInstaller->installPlugin($filepath, $isUrl);
			return true;
		} catch ( Exception $e) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_installerror').": ".$e->getMessage(), 'type' => 'error'));
			return false;
		}
	}
	
	private function _uninstall($manifest) {
		
		/* @var $egg X_Egg */
		$egg = X_Egg::factory($manifest, APPLICATION_PATH.'/../');
		
		foreach ( $egg->getFiles() as $file ) {
			/* @var $file X_Egg_File */
			
			// if file doesn't exists, ignore it
			// this prevent errors when you retry to uninstall
			// a partially installed plugin
			if ( !file_exists($file->getDestination()) ) continue;
			
			if ( !$file->getProperty(X_Egg_File::P_REPLACE, false) || $file->getProperty(X_Egg_File::P_REMOVEREPLACEDONUNINSTALL, false) ) {
				$unlinkStatus = @unlink($file->getDestination());
				if ( !$file->getProperty(X_Egg_File::P_IGNOREUNLINKERROR, true) && !$unlinkStatus ) {
					X_Debug::e("File not unlinked: {{$file->getDestination()}}");
					throw new Exception("Cannot unlink file {{$file->getDestination()}} and ignoreUnlinkError flag is FALSE. Remove it manually and try again");
				}
			} else {
				X_Debug::i("Replaced file {{$file->getDestination()}} left because removeReplacedOnUninstall is FALSE");
			}
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
		
		// process acl fragment
		$aclHelper = X_VlcShares_Plugins::helpers()->acl();
		
		// added classes
		foreach ($egg->getAclClasses() as $aclClass) {
			/* @var $aclClass X_Egg_AclClass */
			$resources = Application_Model_AclResourcesMapper::i()->fetchByClassNotGenerated($aclClass->getName(), $egg->getKey());
			$restore = $aclClass->getOnDelete();
			foreach ($resources as $resource) {
				/* @var $resource Application_Model_AclResource */
				$resource->setClass($restore);
				try {
					Application_Model_AclResourcesMapper::i()->save($resource);
				} catch (Exception $e) {
					X_Debug::e("Can't restore class {{$restore}} for resource {{$resource->getKey()}}: {$e->getMessage()}");
				}
			}
			$aclHelper->removeClass($aclClass->getName());
		}
		// acl resources are automatically removed by foreign key with generator
				
		@unlink($manifest);
		@rmdir(dirname($manifest));
		
		return true;
	}
}

