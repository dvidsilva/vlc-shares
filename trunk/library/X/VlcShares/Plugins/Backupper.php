<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'Zend/Config.php';
require_once 'Zend/Controller/Request/Abstract.php';
require_once 'X/VlcShares/Plugins/BackuppableInterface.php';

class X_VlcShares_Plugins_Backupper extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_BackuppableInterface {
	
	public function __construct() {
		
		$this
			->setPriority('getIndexActionLinks')
			->setPriority('getIndexManageLinks');
		
	}	

	/**
	 * Add the link "backup all systems" actionLinks
	 * @param Zend_Controller_Action $this
	 * @return array The format of the array should be:
	 * 		array(
	 * 			array(
	 * 				'label' => ITEM LABEL,
	 * 				'link'	=> HREF,
	 * 				'highlight'	=> true|false,
	 * 				'icon'	=> ICON_HREF
	 * 			), ...
	 * 		)
	 */
	public function getIndexActionLinks(Zend_Controller_Action $controller) {
		
		$urlHelper = $controller->getHelper('url');
		
		return array(
			array(
				'label'		=>	X_Env::_('p_backupper_actionbackupall'),
				'link'		=>	$urlHelper->url(array(
					'controller'	=>	'backupper',
					'action'		=>	'backup',
					'a'				=>	'all'
				)),
				'icon'		=>	'/images/backupper/backupall.png'
			)
		);
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
				'title'		=>	X_Env::_('p_backupper_managetitle'),
				'label'		=>	X_Env::_('p_backupper_mlink'),
				'link'		=>	$urlHelper->url(array(
					'controller'	=>	'backupper',
					'action'		=>	'index',
				)),
				'icon'		=>	'/images/backupper/logo.png',
				'subinfos'	=> array()
			),
		);
	}
	
	
	/**
	 * Backup core configs and plugins list
	 * This is not a trigger of plugin API. It's called by Backupper plugin
	 */
	function getBackupItems() {
		
		$return = array('configs' => array(), 'plugins' => array());
		$configs = Application_Model_ConfigsMapper::i()->fetchAll();
		
		foreach ($configs as $model) {
			/* @var $model Application_Model_Config */
			$return['configs'][] = array(
				'id'			=> $model->getId(), 
				'key'			=> $model->getKey(),
				'value'			=> $model->getValue(),
				'default'		=> $model->getDefault(),
				'section'		=> $model->getSection(),
				'label'			=> $model->getLabel(),
				'description'	=> $model->getDescription(),
				'type'			=> $model->getType(),
				'class'			=> $model->getClass()
			);
		}
		
		$plugins = Application_Model_PluginsMapper::i()->fetchAll();
		
		foreach ($plugins as $model) {
			/* @var $model Application_Model_Plugin */
			$return['plugins'][] = array(
				'id'			=> $model->getId(), 
				'key'			=> $model->getKey(),
				'class'			=> $model->getClass(),
				'file'			=> $model->getFile(),
				'label'			=> $model->getLabel(),
				'description'	=> $model->getDescription(),
				'type'			=> $model->getType(),
				'enabled'		=> ($model->isEnabled() ? 1 : 0)
			);
		}
		
		return $return;
	}
	
	/**
	 * Restore core configs and plugins list in db 
	 * This is not a trigger of plugin API. It's called by Backupper plugin
	 */
	function restoreItems($items) {
		
	}
	
}
