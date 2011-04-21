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
	//APPLICATION_PATH.'/../data/fsthumbs/'
);

$neededLinks = array(
	$basePath.'/public/images/fsthumbs/' => APPLICATION_PATH.'/../public/images/fsthumbs',
	//$basePath.'/public/js/fsthumbs/' => APPLICATION_PATH.'/../public/js/fsthumbs',
	$basePath.'/public/css/fsthumbs/' => APPLICATION_PATH.'/../public/css/fsthumbs',
	$basePath.'/languages/X_VlcShares_Plugins_FsThumbs.en_GB.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_FsThumbs.en_GB.ini',
	$basePath.'/languages/X_VlcShares_Plugins_FsThumbs.it_IT.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_FsThumbs.it_IT.ini',
);

// Include files
$includeFiles = array(
	// dbtables
	$basePath.'/application/models/DbTable/FsThumbs.php',

	// models
	$basePath.'/application/models/FsThumb.php',
	$basePath.'/application/models/FsThumbsMapper.php',

	$basePath.'/application/controllers/FsthumbsController.php'
);

// add your view scripts path
$viewScriptsPath = array(
	$basePath.'/application/views/scripts/'
);

// include path for Plugins
$pluginsIncludes = array(
	$basePath.'/library/X/VlcShares/Plugins/FsThumbs.php',
);

// include path for Helpers
$helpersIncludes = array(
);

/* @var $runtimeConfigs Zend_Config */
//$runtimeConfigs = $this->getResource('configs');
// check here for configs from runtime
$pluginsInstances = array(
	'fsthumbs' => array(
		'class' => 'X_VlcShares_Plugins_FsThumbs',
		'configs' => array(
			// special configs aren't required, so i use defaults hardcoded
		)
	)
);


$dbInits = array(
	$basePath.'/install.sql' => (array_search('plg_fsthumbs', $dbAdapter->listTables()) === false),
);

