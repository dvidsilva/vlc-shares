<?php 
// === PLEASE, DO NOT CHANGE THOSE LINES ===
// context of this file is Bootstrap::_initExtraPlugins()
// dev bootstrap file
// this file have to add paths and resources of extra plugins without have to install it
$basePath = dirname(__FILE__);
/* @var $dbAdapter Zend_Db_Adapter_Abstract */ 
$dbAdapter = $this->getResource('db');
/* @var $frontController Zend_Controller_Front */
$frontController = $this->getResource('frontController');
/* @var $view Zend_View */
$view = $this->getResource('view');
// =========================================

// === YOUR SETTINGS START HERE ============
/**
 * Insert the needed directories here
 * Those directory will be created while initializing
 * 
 * Use
 *  APPLICATION_PATH = /vlc-shares/application
 * or
 *  $basePath = the directory where this file is placed
 *  
 * as basepath
 */
$neededDirectories = array(
	//APPLICATION_PATH.'/../public/my/created/folder/' // <--- THIS IS AN EXAMPLE:
);

/**
 * Insert the needed links
 * Those files or directory will be linked (in linux)
 * or copied (in windows) everytime the application will be executed
 * 
 * Use
 *  APPLICATION_PATH = /vlc-shares/application
 * or
 *  $basePath = the directory where this file is placed
 *  
 * as basepath
 * 
 * Entry format is:
 * 	Real entry path => linked/copied path
 * 
 * Usually language files or image/css/js folders must be setted here
 */
$neededLinks = array(
	$basePath.'/public/images/streamingonline/' => APPLICATION_PATH.'/../public/images/streamingonline', // crea un link della cartella delle immagini
	$basePath.'/languages/X_VlcShares_Plugins_StreamingOnline.en_GB.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_StreamingOnline.en_GB.ini', // link al file di traduzione
	$basePath.'/languages/X_VlcShares_Plugins_StreamingOnline.it_IT.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_StreamingOnline.it_IT.ini', // link al file di traduzione
);

/**
 * Insert here the list of files that must be included before 
 * plugin initialization
 * 
 * Use
 *  APPLICATION_PATH = /vlc-shares/application
 * or
 *  $basePath = the directory where this file is placed
 * as basepath
 * 
 * Usually controllers, models, DbTables, forms must be included here 
 */
$includeFiles = array(
	//$basePath.'/application/controllers/MycontrollerController.php' // <--- THIS IS AN EAMPLE FOR CONTROLLER INCLUSION
);

/**
 * Set here the path to plugin views scripts
 * 
 * Use
 *  $basePath = the directory where this file is placed
 * as basepath
 */
$viewScriptsPath = array(
	//$basePath.'/application/views/' // <---- THIS IS AN EXAMPLE FOR VIEWS INCLUSION
);

/**
 * Plugin class file to include
 * Use
 *  $basePath = the directory where this file is placed
 * as basepath
 */
$pluginsIncludes = array(
	$basePath.'/library/X/VlcShares/Plugins/StreamingOnline.php', // <---- THIS IS AN EXAMPLE
);

/**
 * Helpers class file to include
 * Use
 *  $basePath = the directory where this file is placed
 * as basepath
 */
$helpersIncludes = array(
	//$basePath.'/library/X/VlcShares/Plugins/Helper/MyHelper.php', // <---- THIS IS AN EXAMPLE
);

/**
 * Insert here the pluginKey
 */
$pluginInstance_pluginKey = 'streamingonline';

/**
 * Insert here the plugin class
 */
$pluginInstance_pluginClass = 'X_VlcShares_Plugins_StreamingOnline';

/**
 * Insert here special plugin configs if required
 */
$pluginInstance_pluginConfigs = array(
	// 'my.config' => 'my-config-value', // <---- THIS IS AN EXAMPLE
);

/**
 * Insert here an sql init script that must be executed 
 * for plugin initialization
 * 
 * Use
 *  $basePath = the directory where this file is placed
 * as basepath
 *
 * Entries must be insered in the form:
 * 	SCRIPT_NAME.sql => $GUARD_VALUE
 * 
 * WARNING: the script will be executed everytime
 * 		the $GUARD_VALUE will be true
 * 
 * Use some kind of test to be sure that the script must
 * be executed
 * 
 * SAMPLE TEST:
 * 		- execute the script only if a file doesn't exist
 * 			(file_exists(APPLICATION_PATH.'/../languages/myfile.txt') == false)
 * 		- execute the script only if a table doesn't exist
 * 			(array_search('MY_TABLE_NAME', $dbAdapter->listTables()) === false)
 */
$dbInits = array(
	//$basePath.'/install.sql' => (file_exists(APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_StreamingOnline.en_Gb.ini') == false),
);


// === PLEASE, DO NOT CHANGE THOSE LINES ===
$pluginsInstances = array(
	$pluginInstance_pluginKey => array(
		'class' => $pluginInstance_pluginClass,
		'configs' => $pluginInstance_pluginConfigs
	)
);
// =========================================
