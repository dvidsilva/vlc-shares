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
	APPLICATION_PATH.'/../data/animedb/'
);

$neededLinks = array(
	$basePath.'/public/images/animedb/' => APPLICATION_PATH.'/../public/images/animedb',
	$basePath.'/languages/X_VlcShares_Plugins_AnimeDb.en_GB.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_AnimeDb.en_GB.ini',
	$basePath.'/languages/X_VlcShares_Plugins_AnimeDb.it_IT.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_AnimeDb.it_IT.ini',
);

// Include files
$includeFiles = array(
);

// add your view scripts path
$viewScriptsPath = array(
);

// include path for Plugins
$pluginsIncludes = array(
	$basePath.'/library/X/VlcShares/Plugins/AnimeDb.php',
);

// include path for Helpers
$helpersIncludes = array(
);

/* @var $runtimeConfigs Zend_Config */
$runtimeConfigs = $this->getResource('configs');
// check here for configs from runtime
if ( isset($runtimeConfigs->plugins->animedb ) ) {
	$pluginsInstances = array(
		'animedb' => array(
			'class' => 'X_VlcShares_Plugins_AnimeDb',
			'configs' => array(
				'auth.username' => $runtimeConfigs->plugins->animedb->auth->username,
				'auth.password' => $runtimeConfigs->plugins->animedb->auth->password,
				'scraper.alternative.enabled' => $runtimeConfigs->plugins->animedb->scaper->alternative->enabled,
			)
		)
	);
	unset($runtimeConfigs);
} else {
	$pluginsInstances = array(
		'animedb' => array(
			'class' => 'X_VlcShares_Plugins_AnimeDb',
			'configs' => array(
				// special configs aren't required, so i use defaults hardcoded
			)
		)
	);
}

$dbInits = array(
	$basePath.'/install.sql' => (file_exists(APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_AnimeDb.en_GB.ini') == false),
);

