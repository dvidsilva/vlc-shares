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
    		$form->setDefault('auth', 0);
    	} catch (Exception $e) {
    		// WTF?
    	}

    	
		// try to read the manifest of plugins index

    	$plugins = $this->getInstallablePlugins();
    	try {
    		$form->plugins->setMultiOptions($plugins);
    	} catch (Exception $e) {
    		// Connection kaboom?
    	}
    	
    	$ns = new Zend_Session_Namespace('vlc-shares::installer');
    	if ( isset($ns->errors) && $ns->errors ) {
    		$form->isValid($ns->data);
    		unset($ns->errors); 
    		unset($ns->data);
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
	    			$this->_helper->flashMessenger(array('type' => 'success', 'text' => X_Env::_('installer_language_done')));
	    			//$this->_helper->redirector('execute');
    			} catch (Exception $e) {
    				$this->_helper->flashMessenger(array('type' => 'fatal', 'text' => X_Env::_("installer_err_db").": {$e->getMessage()}"));
    			}
    		}
    	} else {
	    	$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('installer_invalid_language')));
	    	$this->_helper->redirector('index');
    	}
    	
    	
    	// check for admin username/password
    	$form = new Application_Form_Installer();
    	$form->removeElement('lang');
    	$form->removeElement('plugins');
    	
    	if ( !$form->isValid($this->getRequest()->getPost())) {
    		$ns = new Zend_Session_Namespace('vlc-shares::installer');
    		$ns->errors = true;
    		$ns->data = $this->getRequest()->getPost();
	    	$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('installer_invalid_data')));
	    	$this->_helper->redirector('index');
	    	return;
    	}
    	
    	$username = $form->getValue('username');
    	$password = $form->getValue('password');
    	
    	if ( Application_Model_AuthAccountsMapper::i()->getCount(true) == 0 ) {
    		// try to reenable/create a new account
    		try {
	    		$account = new Application_Model_AuthAccount();
	    		Application_Model_AuthAccountsMapper::i()->fetchByUsername($username);
	    		$account->setUsername($username)
	    			->setPassword(md5("$username:$password"))
	    			->setEnabled(true)
	    			->setPassphrase(md5("$username:$password:".rand(10000,99999).time()))
	    			->setAltAllowed(true)
	    			;
	    		Application_Model_AuthAccountsMapper::i()->save($account);
    			
	    		$this->_helper->flashMessenger(array('type' => 'success', 'text' => X_Env::_('installer_newaccount_done')));
	    		
    		} catch (Exception $e) {
		    	$this->_helper->flashMessenger(X_Env::_('installer_err_db').": {$e->getMessage()}");
		    	$this->_helper->redirector('index');
		    	return;
    		}
    		
    		// after that account is stored, try to do a login
    		if ( X_VlcShares_Plugins::broker()->isRegistered('auth') ) {
    			$auth = X_VlcShares_Plugins::broker()->getPlugins('auth');
    		} else {
    			$auth = new X_VlcShares_Plugins_Auth();
    		}
    		if ( !$auth->isLoggedIn() ) {
    			$auth->doLogin($username);
    		}
    	}
    	
    	
    	try {
    		// enable auth plugin after authentication
    		if ( $form->getValue('auth', '0') == '1' ) {
		    	$plugin = new Application_Model_Plugin();
		    	Application_Model_PluginsMapper::i()->fetchByClass('X_VlcShares_Plugins_Auth', $plugin);
		    	//Application_Model_PluginsMapper::i()->delete($plugin);
				$plugin->setEnabled(true);
				Application_Model_PluginsMapper::i()->save($plugin);
    		}
    	} catch (Exception $e) {
		    $this->_helper->flashMessenger(X_Env::_('installer_err_db').": {$e->getMessage()}");
		    $this->_helper->redirector('index');
    	}
    	
    	
    	$plugins = $this->getRequest()->getParam('plugins', array());
    	
    	//ini_set('max_execution_time', 0);
    	
    	ignore_user_abort(true);
    	
    	if ( is_array($plugins) ) {
    		
    		/* @var $pluginInstaller X_VlcShares_Plugins_PluginInstaller */
    		$pluginInstaller = X_VlcShares_Plugins::broker()->getPlugins('plugininstaller');
    		
    		foreach ( $plugins as $plugin ) {
    			
    			// allow 30 seconds for each plugin
    			set_time_limit(30);
    			
    			try {
    				$pluginInstaller->installPlugin($plugin, true);
    				$this->_helper->flashMessenger(array('type' => 'success', 'text' => X_Env::_('plugin_install_done') . ": $plugin"));
    			} catch (Exception $e) {
    				$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_installerror').": ".$e->getMessage(), 'type' => 'error'));
    			}
    			
    			// download the plugin file
    			/*
    			$http = new Zend_Http_Client($plugin);
    			$http->setStream(true);
    			
    			$response = $http->request();
    			
    			if ( $this->_installPlugin($response->getStreamName()) ) {
    				$this->_helper->flashMessenger(array('type' => 'success', 'text' => X_Env::_('plugin_install_done') . ": $plugin"));
    			}
    			*/
    			
    		}
    	}
    	
    	$this->_helper->redirector('execute');
    	
    }
    
    
    public function executeAction() {

    	// propagate current messages
    	$messages = $this->_helper->flashMessenger->getMessages();
    	foreach ($messages as $message) {
    		$this->_helper->flashMessenger->addMessage($message);
    	}
    	
    	try {
    		
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

    protected function getInstallablePlugins() {
		
		try {
		
			/* @var $updateNotifier X_VlcShares_Plugins_UpdateNotifier */
			$updateNotifier = X_VlcShares_Plugins::broker()->getPlugins('updatenotifier');
			$updateNotifier->clearLastCheck();
			
			$plugins = $updateNotifier->getLastPlugins();
	
			$return = array();
			
			foreach ($plugins as $key => $versions) {
				
				// if a plugin key is already installer, skip it
				if ( X_VlcShares_Plugins::broker()->isRegistered($key) ) continue;
				
				foreach ($versions as $version) {
					
					// only stable version allowed
					if ( $version['type'] != 'stable' && $version['type'] != '' ) {
						continue;
					}
					
					// check for valid version cMin cMax
					if ( version_compare(X_VlcShares::VERSION_CLEAN, $version['cMin'], '<')
						|| version_compare(X_VlcShares::VERSION_CLEAN, $version['cMax'], '>=') ) {
	
						continue;
					}
					
					$img = '';
					if ( $version['thumbnail'] ) {
						$img = '<img class="installer-plugin-thumb" src="' . $this->_helper->viewRenderer->view->baseUrl("/images/plugin-no-image.jpg") . '" data-src="'. $version['thumbnail'] .'" />';
					} else {
						$img = '<img class="installer-plugin-thumb" src="'. $this->_helper->viewRenderer->view->baseUrl("/images/plugin-no-image.jpg") .'"  />';
					}
					
					$return[$version['download']] = $img . "<span class=\"installer-plugin-title\">$key</span>" . ($version['description'] != '' ? "<span class=\"installer-plugin-description\">{$version['description']}</span>" : '');
					
					// doesn't continue in versions traversal
					break;
				} 
				
			}
			
		} catch (Exception $e) {
			X_Debug::e($e->getMessage());
			$return = array();
		}
		
		return $return;
		    	
    	
    }
    
}

