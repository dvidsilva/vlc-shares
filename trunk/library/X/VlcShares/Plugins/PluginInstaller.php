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
}
