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
	APPLICATION_PATH.'/../data/animeftw/'
);

$neededLinks = array(
	$basePath.'/public/images/animeftw/' => APPLICATION_PATH.'/../public/images/animeftw',
	$basePath.'/languages/X_VlcShares_Plugins_AnimeFTW.en_GB.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_AnimeFTW.en_GB.ini',
	$basePath.'/languages/X_VlcShares_Plugins_AnimeFTW.it_IT.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_AnimeFTW.it_IT.ini',
);

// Include files
$includeFiles = array(
	$basePath.'/application/controllers/AnimeftwController.php'
);

// add your view scripts path
$viewScriptsPath = array(
);

// include path for Plugins
$pluginsIncludes = array(
	$basePath.'/library/X/VlcShares/Plugins/AnimeFTW.php',
);

// include path for Helpers
$helpersIncludes = array(
	$basePath.'/library/X/VlcShares/Plugins/Helper/AnimeFTW.php',
);

/* @var $runtimeConfigs Zend_Config */
$runtimeConfigs = $this->getResource('configs');
// check here for configs from runtime
if ( isset($runtimeConfigs->plugins->animeftw ) ) {
	$pluginsInstances = array(
		'animeftw' => array(
			'class' => 'X_VlcShares_Plugins_AnimeFTW',
			'configs' => array(
				'auth.username' => $runtimeConfigs->plugins->animeftw->auth->username,
				'auth.password' => $runtimeConfigs->plugins->animeftw->auth->password,
				'sitescraper.enabled' => $runtimeConfigs->plugins->animeftw->sitescraber->enabled,
				'proxy.enabled' => $runtimeConfigs->plugins->animeftw->proxy->enabled,
			)
		)
	);
	unset($runtimeConfigs);
} else {
	$pluginsInstances = array(
		'animeftw' => array(
			'class' => 'X_VlcShares_Plugins_AnimeFTW',
			'configs' => array(
				// special configs aren't required, so i use defaults hardcoded
			)
		)
	);
}


$dbInits = array(
	$basePath.'/install.sql' => (file_exists(APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_AnimeFTW.en_GB.ini') == false),
);

