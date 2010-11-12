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
	 * @return array The format of the array should be:
	 * 		array(
	 * 			array(
	 * 				'title' => ITEM TITLE,
	 * 				'label' => ITEM LABEL,
	 * 				'link'	=> HREF,
	 * 				'highlight'	=> true|false,
	 * 				'icon'	=> ICON_HREF,
	 * 				'subinfos' => array(INFO, INFO, INFO)
	 * 			), ...
	 * 		)
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$urlHelper = $controller->getHelper('url');
		
		return array(
			array(
				'title'		=>	X_Env::_('p_plugininstaller_managetitle'),
				'label'		=>	X_Env::_('p_plugininstaller_mlink'),
				'link'		=>	$urlHelper->url(array(
					'controller'	=>	'plugin',
					'action'		=>	'index',
				)),
				'icon'		=>	'/images/plugininstaller/logo.png',
				'subinfos'	=> array()
			),
		);
	}
	
	/**
	 * Scan all directory checking permissions
	 * @param Zend_Config $options
	 * @param Zend_Controller_Action $controller
	 * @return array format: array(array('testname', 'teststatus', 'testmessage'), array...)
	 */
	public function getTestItems(Zend_Config $options,Zend_Controller_Action $controller) {
		
		$dirs = array('application', 'data', 'library', 'public', 'languages');
		$tests = array();
		try {
			foreach ($dirs as $dir) {
				// if there is a write problem, an exception is raised
				$this->_checkWritable(APPLICATION_PATH . '/../'. $dir);
			}
			$tests[] = array('[PluginInstaller] Checking for directory permissions', true, 'Success');
		} catch (Exception $e) {
			$tests[] = array('[PluginInstaller] Checking for directory permissions', false, $e->getMessage());
		}
		return $tests;
	}
	
	private function _checkWritable($dir) {
		
		$dir = rtrim($dir, '\\/');
		X_Debug::i('Checking dir '.$dir);
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
