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
	//APPLICATION_PATH.'/../data/spainradio/'
);

$neededLinks = array(
	$basePath.'/public/images/spainradio/' => APPLICATION_PATH.'/../public/images/spainradio',
	$basePath.'/languages/X_VlcShares_Plugins_Spainradio.en_GB.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_Spainradio.en_GB.ini',
	$basePath.'/languages/X_VlcShares_Plugins_Spainradio.it_IT.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_Spainradio.it_IT.ini',
	$basePath.'/languages/X_VlcShares_Plugins_Spainradio.es_ES.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_Spainradio.es_ES.ini',
);

// Include files
$includeFiles = array(
	$basePath.'/application/controllers/SpainradioController.php'
);

// add your view scripts path
$viewScriptsPath = array(
);

// include path for Plugins
$pluginsIncludes = array(
	$basePath.'/library/X/VlcShares/Plugins/Spainradio.php',
);

// include path for Helpers
$helpersIncludes = array(
);

/* @var $runtimeConfigs Zend_Config */
$runtimeConfigs = $this->getResource('configs');
// check here for configs from runtime
$pluginsInstances = array(
	'spainradio' => array(
		'class' => 'X_VlcShares_Plugins_Spainradio',
		'configs' => array(
			// special configs aren't required, so i use defaults hardcoded
		)
	)
);


$dbInits = array(
	//$basePath.'/install.sql' => (file_exists(APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_Spainradio.en_GB.ini') == false),
);

