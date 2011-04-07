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
	APPLICATION_PATH.'/../data/megavideo/'
);

$neededLinks = array(
	$basePath.'/public/images/icons/hosters/megavideo.png' => APPLICATION_PATH.'/../public/images/icons/hosters/megavideo.png',
	$basePath.'/public/images/icons/hosters/megaupload.png' => APPLICATION_PATH.'/../public/images/icons/hosters/megaupload.png',
	$basePath.'/languages/X_VlcShares_Plugins_Megavideo.en_GB.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_Megavideo.en_GB.ini',
	$basePath.'/languages/X_VlcShares_Plugins_Megavideo.it_IT.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_Megavideo.it_IT.ini',
	$basePath.'/public/images/megavideo/' => APPLICATION_PATH.'/../public/images/megavideo',
	$basePath.'/public/css/megavideo/' => APPLICATION_PATH.'/../public/css/megavideo',
	$basePath.'/public/js/megavideo/' => APPLICATION_PATH.'/../public/js/megavideo',
);

// Include files
$includeFiles = array(
	$basePath.'/library/X/Megavideo.php',
	// dbtables
	$basePath.'/application/models/DbTable/Megavideo.php',

	// models
	$basePath.'/application/models/Megavideo.php',
	$basePath.'/application/models/MegavideoMapper.php',
	
	// forms
	$basePath.'/application/forms/Megavideo.php',
	
	// controllers
	$basePath.'/application/controllers/MegavideoController.php',
);

// add your view scripts path
$viewScriptsPath = array(
	$basePath.'/application/views/scripts/'
);

// include path for Plugins
$pluginsIncludes = array(
	$basePath.'/library/X/VlcShares/Plugins/Megavideo.php',
);

// include path for Helpers
$helpersIncludes = array(
	$basePath.'/library/X/VlcShares/Plugins/Helper/Hoster/Megavideo.php',
	$basePath.'/library/X/VlcShares/Plugins/Helper/Hoster/Megaupload.php',
	$basePath.'/library/X/VlcShares/Plugins/Helper/Megavideo.php',
	$basePath.'/library/X/VlcShares/Plugins/Helper/Megaupload.php'
);

// check here for configs from runtime
if ( (array_search('plg_megavideo', $dbAdapter->listTables()) !== false) ) {
	/* @var $runtimeConfigs Zend_Config */
	$runtimeConfigs = $this->getResource('configs');
	
	$pluginsInstances = array(
		'megavideo' => array(
			'class' => 'X_VlcShares_Plugins_Megavideo',
			'configs' => array(
				'premium.enabled' => $runtimeConfigs->plugins->megavideo->premium->enabled,
				'premium.username' => $runtimeConfigs->plugins->megavideo->premium->username,
				'premium.password' => $runtimeConfigs->plugins->megavideo->premium->password, 
			)
		)
	);
	unset($runtimeConfigs);
} else {
	$pluginsInstances = array(
		'megavideo' => array(
			'class' => 'X_VlcShares_Plugins_Megavideo',
			'configs' => array(
				// special configs aren't required, so i use defaults hardcoded
			)
		)
	);
}

$dbInits = array(
	$basePath.'/install.sql' => (array_search('plg_megavideo', $dbAdapter->listTables()) === false),
);


