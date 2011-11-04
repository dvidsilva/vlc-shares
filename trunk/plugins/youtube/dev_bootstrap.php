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
	$basePath.'/languages/X_VlcShares_Plugins_Youtube.en_GB.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_Youtube.en_GB.ini',
	$basePath.'/languages/X_VlcShares_Plugins_Youtube.it_IT.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_Youtube.it_IT.ini',
	$basePath.'/public/images/icons/hosters/youtube.png' => APPLICATION_PATH.'/../public/images/icons/hosters/youtube.png',
	$basePath.'/public/images/youtube/' => APPLICATION_PATH.'/../public/images/youtube',
	$basePath.'/public/css/youtube/' => APPLICATION_PATH.'/../public/css/youtube',
	$basePath.'/public/js/youtube/' => APPLICATION_PATH.'/../public/js/youtube',
);

// Include files
$includeFiles = array(
	// dbtables
	$basePath.'/application/models/DbTable/YoutubeAccounts.php',
	$basePath.'/application/models/DbTable/YoutubeCategories.php',
	$basePath.'/application/models/DbTable/YoutubeVideos.php',

	// models
	$basePath.'/application/models/YoutubeAccount.php',
	$basePath.'/application/models/YoutubeAccountsMapper.php',
	$basePath.'/application/models/YoutubeCategory.php',
	$basePath.'/application/models/YoutubeCategoriesMapper.php',
	$basePath.'/application/models/YoutubeVideo.php',
	$basePath.'/application/models/YoutubeVideosMapper.php',
	
	// forms
	$basePath.'/application/forms/YoutubeAccount.php',
	$basePath.'/application/forms/YoutubeCategory.php',
	$basePath.'/application/forms/YoutubeVideo.php',
	
	// controllers
	$basePath.'/application/controllers/YoutubeController.php',
);

// add your view scripts path
$viewScriptsPath = array(
	$basePath.'/application/views/scripts/'
);

// include path for Plugins
$pluginsIncludes = array(
	$basePath.'/library/X/VlcShares/Plugins/Youtube.php',
);

// include path for Helpers
$helpersIncludes = array(
	$basePath.'/library/X/VlcShares/Plugins/Helper/Hoster/Youtube.php',
	$basePath.'/library/X/VlcShares/Plugins/Helper/Youtube.php'
);

$pluginsInstances = array(
	'youtube' => array(
		'class' => 'X_VlcShares_Plugins_Youtube',
		'configs' => array(
			// special configs aren't required, so i use defaults hardcoded
		)
	)
);

$dbInits = array(
	$basePath.'/install.sql' => (array_search('plg_youtube_accounts', $dbAdapter->listTables()) === false),
);


