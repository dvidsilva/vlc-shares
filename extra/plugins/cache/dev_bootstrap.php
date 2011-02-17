<?php 

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

$neededDirectories = array(
	//APPLICATION_PATH.'/../public/images/youtube/uploads/'
);

$neededLinks = array(
	//$basePath.'/public/images/jigoku/' => APPLICATION_PATH.'/../public/images/jigoku',
	$basePath.'/languages/X_VlcShares_Plugins_Cache.en_GB.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_Cache.en_GB.ini',
	$basePath.'/languages/X_VlcShares_Plugins_Cache.it_IT.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_Cache.it_IT.ini',
);

// Include files
$includeFiles = array(
	// dbtables
	$basePath.'/application/models/DbTable/Cache.php',

	// models
	$basePath.'/application/models/Cache.php',
	$basePath.'/application/models/CacheMapper.php',
	
	// controllers
	$basePath.'/application/controllers/CacheController.php',
);

// add your view scripts path
$viewScriptsPath = array(
);

// include path for Plugins
$pluginsIncludes = array(
	$basePath.'/library/X/VlcShares/Plugins/Cache.php',
);

// include path for Helpers
$helpersIncludes = array(
);

$pluginsInstances = array(
	'cache' => array(
		'class' => 'X_VlcShares_Plugins_Cache',
		'configs' => array(
			// special configs aren't required, so i use defaults hardcoded
		)
	)
);

$dbInits = array(
	$basePath.'/install.sql' => (file_exists(APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_Cache.en_GB.ini') == false),
);

