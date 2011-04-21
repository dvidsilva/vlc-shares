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
);

$neededLinks = array(
);

// Include files
$includeFiles = array(
);

// add your view scripts path
$viewScriptsPath = array(
	//$basePath.'/application/views/scripts/
);

// include path for Plugins
$pluginsIncludes = array(
	$basePath.'/library/X/VlcShares/Plugins/Sample.php',
);

// include path for Helpers
$helpersIncludes = array(
	//$basePath.'/library/X/VlcShares/Plugins/Helper/Sample.php'
);

$pluginsInstances = array(
	'sample' => array(
		'class' => 'X_VlcShares_Plugins_Sample',
		'configs' => array(
		)
	)
);

$dbInits = array(
	/*
	$basePath.'/install.sql' => (array_search('sampletable', $dbAdapter->listTables()) === false),
	*/
);

