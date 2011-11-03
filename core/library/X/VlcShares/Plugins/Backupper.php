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
			->setPriority('getIndexMessages')
			->setPriority('getIndexManageLinks');
		
	}	

	/**
	 * Add the link "backup all systems" actionLinks
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ActionLink
	 */
	public function getIndexActionLinks(Zend_Controller_Action $controller) {
		
		$link = new X_Page_Item_ActionLink($this->getId(), X_Env::_('p_backupper_actionbackupall'));
		$link->setIcon('/images/backupper/backupall.png')
			->setLink(array(
					'controller'	=>	'backupper',
					'action'		=>	'backup',
					'a'				=>	'all'
			), 'default', true);
		return new X_Page_ItemList_ActionLink(array($link));
		
	}
		
	/**
	 * Add the link for -manage-backupper-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_backupper_mlink'));
		$link->setTitle(X_Env::_('p_backupper_managetitle'))
			->setIcon('/images/backupper/logo.png')
			->setLink(array(
					'controller'	=>	'backupper',
					'action'		=>	'index',
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
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
			$return['configs'][$model->getKey()] = array(
				'key'			=> $model->getKey(),
				'value'			=> $model->getValue(),
				'section'		=> $model->getSection(),
			);
		}
		
		$plugins = Application_Model_PluginsMapper::i()->fetchAll();
		
		foreach ($plugins as $model) {
			/* @var $model Application_Model_Plugin */
			$return['plugins'][$model->getKey()] = array(
				'key'			=> $model->getKey(),
				'enabled'		=> ($model->isEnabled() ? 1 : 0)
			);
		}
		
		return $return;
	}
	
	/**
	 * Restore core configs and plugins list in db 
	 * This is not a trigger of plugin API. It's called by Backupper plugin
	 * @param array $items Array format: 
	 * 		array(
	 * 			'plugins' => array(
	 * 				array(
	 * 					'key' => PLUGIN_KEY
	 * 					'enabled' => 1|0
	 *				),...
	 *			),
	 *			'configs' => array(
	 *				array(
	 *					'key' => CONFIG_KEY
	 *					'value' => CONFIG_VALUE
	 *					'section' => CONFIG_SECTION
	 *				)
	 *			)
	 *		)
	 */
	function restoreItems($items) {
		
		
		$pluginsStatusChanged = 0;
		$pluginsStatusErrors = 0;
		$configsStatusChanged = 0;
		$configsStatusErrors = 0;

		// as first thing i restore plugins status
		$plugins = Application_Model_PluginsMapper::i()->fetchAll();
		foreach ($plugins as $plugin) {
			/* @var $plugin Application_Model_Plugin */
			if ( array_key_exists($plugin->getKey(), $items['plugins'])) {
				if ( $plugin->isEnabled() != ((bool) @$items['plugins'][$plugin->getKey()]['enabled'] ) ) {
					$plugin->setEnabled((bool) @$items['plugins'][$plugin->getKey()]['enabled']);
					// i need to commit change
					try {
						Application_Model_PluginsMapper::i()->save($plugin);
						$pluginsStatusChanged++;
					} catch ( Exception $e) {
						X_Debug::e("Failed to update plugin {$plugin->getKey()} status to {$items['plugins'][$plugin->getKey()]['enabled']}");
						$pluginsStatusErrors++;
					}
				}
			}
		}
		
		// then i restore configs status
		$configs = Application_Model_ConfigsMapper::i()->fetchAll();
		foreach ($configs as $config) {
			/* @var $config Application_Model_Config */
			if ( array_key_exists($config->getKey(), $items['configs'])) {
				if ( $config->getSection() == @$items['configs'][$config->getKey()]['section'] && $config->getValue() != $items['configs'][$config->getKey()]['value']  ) {
					$config->setValue( @$items['configs'][$config->getKey()]['value']);
					// i need to commit change
					try {
						Application_Model_ConfigsMapper::i()->save($config);
						$configsStatusChanged++;
					} catch ( Exception $e) {
						X_Debug::e("Failed to update plugin {$config->getKey()} status to {$items['configs'][$config->getKey()]['value']}");
						$configsStatusErrors++;
					}
				}
			}
		}
		
		
		// return a custom message with restore results
		return X_Env::_('p_backupper_restorereport_main').'<br/><dl style="margin: 1em 3em;">'
			.'<dt>'.X_Env::_('p_backupper_restorereport_configchanged').'</dt>'
			.'<dd>'.$configsStatusChanged.'</dd>'
			.'<dt>'.X_Env::_('p_backupper_restorereport_configerrors').'</dt>'
			.'<dd>'.$configsStatusErrors.'</dd>'
			.'<dt>'.X_Env::_('p_backupper_restorereport_pluginchanged').'</dt>'
			.'<dd>'.$pluginsStatusChanged.'</dd>'
			.'<dt>'.X_Env::_('p_backupper_restorereport_pluginerrors').'</dt>'
			.'<dd>'.$pluginsStatusErrors.'</dd>'
		.'</dl>';
		
	}
	
	/**
	 * Retrieve statistic from plugins
	 * @param Zend_Controller_Action $this
	 * @return array The format of the array should be:
	 * 		array(
	 * 			array(
	 * 				'title' => ITEM TITLE,
	 * 				'label' => ITEM LABEL,
	 * 				'stats' => array(INFO, INFO, INFO),
	 * 				'provider' => array('controller', 'index', array()) // if provider is setted, stats key is ignored 
	 * 			), ...
	 * 		)
	 */
	public function getIndexMessages(Zend_Controller_Action $controller) {

		X_Debug::i('Plugin triggered');
		
		
		$type = 'warning';
		$showError = true;
    	try {
    		$backupDir = new DirectoryIterator(APPLICATION_PATH . "/../data/backupper/");
    		
    		foreach ($backupDir as $entry) {
    			if ( $entry->isFile() && pathinfo($entry->getFilename(), PATHINFO_EXTENSION) == 'xml' && X_Env::startWith($entry->getFilename(), 'backup_') ) {
    				$showError = false;
    				break;
    			}
    		}
    		
    	} catch ( Exception $e) {
    		X_Debug::e("Error while parsing backupper data directory: {$e->getMessage()}");
    	}
		
    	$showError = $showError && $this->config('alert.enabled', true);
    	
		if ( $showError ) {
			
			$urlHelper = $controller->getHelper('url');
			/* @var $urlHelper Zend_Controller_Action_Helper_Url */
			
			$removeAlertLink = $urlHelper->url(array('controller'=>'backupper', 'action' => 'alert', 'status' => 'off'));
			
			$mess = new X_Page_Item_Message(
				$this->getId(),
				X_Env::_('p_backupper_warningmessage_nobackup') . " <a href=\"$removeAlertLink\">".X_Env::_('p_backupper_warningmessage_nobackupremove').'</a>'
			);
			$mess->setType($type);
			return new X_Page_ItemList_Message(array($mess));
			
		}
	}
	
}
