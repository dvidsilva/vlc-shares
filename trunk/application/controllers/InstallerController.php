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

    	
		// try to read the manifest of plugins index

    	$plugins = $this->getInstallablePlugins();
    	try {
    		$form->plugins->setMultiOptions($plugins);
    	} catch (Exception $e) {
    		// Connection kaboom?
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
	    			//$this->_helper->redirector('execute');
    			} catch (Exception $e) {
    				$this->_helper->flashMessenger(X_Env::_("installer_err_db").": {$e->getMessage()}");
    			}
    		}
    	} else {
	    	$this->_helper->flashMessenger(X_Env::_('installer_invalid_language'));
	    	$this->_helper->redirector('index');
    	}
    	
    	$plugins = $this->getRequest()->getParam('plugins', array());
    	
    	ini_set('max_execution_time', 0);
    	ignore_user_abort(true);
    	
    	if ( is_array($plugins) ) {
    		
    		foreach ( $plugins as $plugin ) {
    			
    			// download the plugin file
    			$http = new Zend_Http_Client($plugin);
    			$http->setStream(true);
    			
    			$response = $http->request();
    			
    			if ( $this->_installPlugin($response->getStreamName()) ) {
    				$this->_helper->flashMessenger(array('type' => 'success', 'text' => X_Env::_('plugin_install_done') . ": $plugin"));
    			}
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
		
			$client = new Zend_Http_Client('http://vlc-shares.googlecode.com/svn/updates/plugins/INDEX.xml', array(
				'maxredirects'	=> 10,
				'timeout'		=> 10
			));
			
			$client->setHeaders(array(
				'User-Agent: vlc-shares/'.X_VlcShares::VERSION.' firstrunsetup/'.X_VlcShares::VERSION
			));
			
			$response = $client->request();
			
			if ( $response->isError() ) {
				throw new Exception('Invalid manifest');
			}
			
			$xml = new SimpleXMLElement($response->getBody());
						
			/*
			<plugins>
				<plugin key="PLUGINKEY">
					<version name="VERSION" type="TYPE" cMin="CORE_MINIMAL" cMax="CORE_MAXIMAL">
						<update></update>
						<download></download>
						<changelog>
							<![CDATA[
							]]>
						</changelog>
					</version>
					...
				</plugin>
				...
			</plugins>
			 */
	
			$return = array();
			
			foreach ($xml->plugin as $plugin) {
				
				$key = (string) $plugin['key'];
				
				// if a plugin key is already installer, skip it
				if ( X_VlcShares_Plugins::broker()->isRegistered($key) ) continue;
				
				foreach ($plugin->version as $version) {
					$version = array(
						'version' => (string)  $version['name'],
						'type' => (string)  $version['type'],
						'cMin' => (string) $version['cMin'],
						'cMax' => (string) $version['cMax'],
						'update' => (string) $version->update,
						'download' => (string) $version->download,
						'changelog' => (string) $version->changelog
					);
					
					// only stable version allowed
					if ( $version['type'] != 'stable' && $version['type'] != '' ) {
						continue;
					}
					
					// check for valid version cMin cMax
					if ( version_compare(X_VlcShares::VERSION_CLEAN, $version['cMin'], '<')
						|| version_compare(X_VlcShares::VERSION_CLEAN, $version['cMax'], '>=') ) {
	
						continue;
					}
					
					$return[$version['download']] = $key;
					
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
    
	private function _installPlugin($filepath) {

		try {
			// unzip and manifest parse
			$egg = X_Egg::factory($filepath, APPLICATION_PATH . '/../', APPLICATION_PATH . '/../data/plugin/tmp/', true);
			
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
    
    
}

