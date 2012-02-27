<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'Zend/Config.php';
require_once 'Zend/Controller/Request/Abstract.php';

class X_VlcShares_Plugins_PluginInstaller extends X_VlcShares_Plugins_Abstract {
	
	public function __construct() {
		
		$this
			->setPriority('getTestItems')
			->setPriority('getIndexManageLinks');
		
	}	
		
	/**
	 * Add the link for -manage-output-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_plugininstaller_mlink'));
		$link->setTitle(X_Env::_('p_plugininstaller_managetitle'))
			->setIcon('/images/plugininstaller/logo.png')
			->setLink(array(
					'controller'	=>	'plugin',
					'action'		=>	'index',
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}
	
	/**
	 * Scan all directory checking permissions
	 * @param Zend_Config $options
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_Message
	 */
	public function getTestItems(Zend_Config $options,Zend_Controller_Action $controller) {
		
		$dirs = array('application', 'data', 'library', 'public', 'languages');
		$tests = new X_Page_ItemList_Test(); 
		$test = new X_Page_Item_Test($this->getId(), '[PluginInstaller] Checking for directory permissions');
		$test->setType(X_Page_Item_Message::TYPE_INFO);
		try {
			foreach ($dirs as $dir) {
				// if there is a write problem, an exception is raised
				
				$this->_checkWritable(APPLICATION_PATH . '/../'. $dir);
			}
			$test->setReason('OK');
		} catch (Exception $e) {
			$test->setType(X_Page_Item_Message::TYPE_FATAL);
			$test->setReason($e->getMessage());
		}
		$tests->append($test);
		return $tests;
	}
	
	private function _checkWritable($dir) {
		
		$dir = rtrim($dir, '\\/');
		//X_Debug::i('Checking dir '.$dir);
		if (is_dir($dir)) { 
			$objects = scandir($dir); 
			foreach ($objects as $object) { 
				if ($object != "." && $object != ".." && $object != '.svn') {
					if ( !is_writable($dir."/".$object) ) {
						throw new Exception("$dir/$object not writable");
					}
					if (filetype($dir."/".$object) == "dir") {
						$this->_checkWritable($dir."/".$object);
					}
				} 
			}
     		reset($objects); 
		}
	}
	
	public function installPlugin($source, $isUrl = false) {
		
		X_Debug::i("Installing plugin from {{$source}}: isUrl = {{$isUrl}}");
		
		if ( $isUrl ) {
			// perform a download in a temp file
			$http = new Zend_Http_Client($source, array(
				'headers' => array(
					'User-Agent' => "vlc-shares/".X_VlcShares::VERSION." plugininstaller/".X_VlcShares::VERSION
				)
			));
			$http->setStream(true);
			$source = $http->request()->getStreamName();
		}
		
		
		try {
			// unzip and manifest parse
			$egg = X_Egg::factory($source, APPLICATION_PATH . '/../', APPLICATION_PATH . '/../data/plugin/tmp/', true);
			
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
				if ( !$file->getProperty(X_Egg_File::P_REPLACE, false) && file_exists($file->getDestination()) ) {
					throw new Exception(X_Env::_('plugin_err_installerror_fileexists'). ": {$file->getDestination()}");
				}
				
				if ( !file_exists($file->getSource())) {
					if ( !$file->getProperty(X_Egg_File::P_IGNOREIFNOTEXISTS, false) ) {
						throw new Exception(X_Env::_('plugin_err_installerror_sourcenotexists'). ": {$file->getSource()}");
					}
					// ignore this item if P_IGNOREIFNOTEXISTS is true and file not exists
					continue;
				}
				
				$toBeCopied[] = array(
						'src' => $file->getSource(),
						'dest' => $file->getDestination(),
						'resource' => $file
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
				$copied = false;
				if ( !file_exists(dirname($copyInfo['dest'])) ) {
					@mkdir(dirname($copyInfo['dest']), 0777, true);
				}
				if ( !copy($copyInfo['src'], $copyInfo['dest']) ) {
					$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_installerror_copyerror').": <br/>".$copyInfo['src'].'<br/>'.$copyInfo['dest'], 'type' => 'error'));
				} else {
					X_Debug::i("File copied {{$copyInfo['dest']}}");
					$copied = true;
				}

				/* @var $xeggFile X_Egg_File */
				$xeggFile = $copyInfo['resource'];
				
				if ( $copied ) {
					// check permission
					$permission = $xeggFile->getProperty(X_Egg_File::P_PERMISSIONS, false);
					if ( $permission !== false ) {
						if ( !chmod($copyInfo['dest'], octdec($permission)) ) {
							X_Debug::e("Chmod {{$permission}} failed for file {{$copyInfo['dest']}}");
						} else {
							X_Debug::i("Permissions set to {{$permission}} for file {{$copyInfo['dest']}} as required");
						}
					}
					
				} else {
					if ( $xeggFile->getProperty(X_Egg_File::P_HALTONCOPYERROR, false) ) {
						X_Debug::f("File not copied {{$copyInfo['dest']}} and flagged as HaltOnCopyError");
						break;
					}
				}
			}
			
			// change database
			$installSql = $egg->getInstallSQL();
			if ( $installSql !== null && file_exists($installSql) ) {
		    	try {
		    		$dataSql = file_get_contents($installSql);
		    		if ( trim($dataSql) !== '' ) {
						$bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
				    	$db = $bootstrap->getResource('db'); 
				    	$db->getConnection()->exec($dataSql);
		    		}
		    	} catch ( Exception $e ) {
		    		X_Debug::e("DB Error while installind: {$e->getMessage()}");
		    		$this->_helper->flashMessenger(X_Env::_('plugin_err_installerror_sqlerror').": {$e->getMessage()}");
		    		//throw $e;
		    	}
			}
			$egg->cleanTmp();
			unlink($source);
			return true;
		} catch ( Exception $e) {
			if ( $egg !== null ) $egg->cleanTmp();
			// delete the uploaded file
			unlink($source);
			//$this->_helper->flashMessenger(array('text' => X_Env::_('plugin_err_installerror').": ".$e->getMessage(), 'type' => 'error'));
			//return false;
			throw $e;
		}		

	}
	
}
