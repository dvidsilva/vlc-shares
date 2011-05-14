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
	$basePath.'/public/images/jdownloader/' => APPLICATION_PATH.'/../public/images/jdownloader',
	$basePath.'/languages/X_VlcShares_Plugins_JDownloader.en_GB.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_JDownloader.en_GB.ini',
	$basePath.'/languages/X_VlcShares_Plugins_JDownloader.it_IT.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_JDownloader.it_IT.ini',
);

// Include files
$includeFiles = array(
);

// add your view scripts path
$viewScriptsPath = array(
);

// include path for Plugins
$pluginsIncludes = array(
	$basePath.'/application/models/JDownloaderFile.php',
	$basePath.'/application/models/JDownloaderPackage.php',
	$basePath.'/library/X/VlcShares/Plugins/JDownloader.php',
);

// include path for Helpers
$helpersIncludes = array(
	$basePath.'/library/X/VlcShares/Plugins/Helper/JDownloader.php',
);

/* @var $runtimeConfigs Zend_Config */
$runtimeConfigs = $this->getResource('configs');
// check here for configs from runtime
if ( isset($runtimeConfigs->plugins->jdownloader ) ) {
	$pluginsInstances = array(
		'jdownloader' => array(
			'class' => 'X_VlcShares_Plugins_JDownloader',
			'configs' => array(
				'remoteapi.ip' => $runtimeConfigs->plugins->jdownloader->remoteapi->ip,
				'remoteapi.port' => $runtimeConfigs->plugins->jdownloader->remoteapi->port,
				'request.timeout' => $runtimeConfigs->plugins->jdownloader->request->timeout,
				'download.enabled' => $runtimeConfigs->plugins->jdownloader->download->enabled,
				'statistics.enabled' => $runtimeConfigs->plugins->jdownloader->statistics->enabled,
				'version.isnightly' => $runtimeConfigs->plugins->jdownloader->version->isnightly,
			)
		)
	);
	unset($runtimeConfigs);
} else {
	$pluginsInstances = array(
		'jdownloader' => array(
			'class' => 'X_VlcShares_Plugins_JDownloader',
			'configs' => array(
				// special configs aren't required, so i use defaults hardcoded
			)
		)
	);
}

$dbInits = array(
	$basePath.'/install.sql' => (file_exists(APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_JDownloader.en_GB.ini') == false),
);

