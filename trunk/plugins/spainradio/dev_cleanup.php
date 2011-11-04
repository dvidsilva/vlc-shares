<?php 
// context of this file is /scripts/cleanup.php

defined('APPLICATION_PATH') or die("This script can't be called directly. Please, use scripts/cleanup.php");
// function rm_recursive($path) definited in /scripts/cleanup.php

// dev cleanup file
// this file have to remove all things created by dev_bootstrap.php
$basePath = dirname(__FILE__);

$neededDirectories = array(
	//APPLICATION_PATH.'/../data/spainradio/'
);

$neededLinks = array(
	$basePath.'/public/images/spainradio/' => APPLICATION_PATH.'/../public/images/spainradio',
	$basePath.'/languages/X_VlcShares_Plugins_Spainradio.en_GB.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_Spainradio.en_GB.ini',
	$basePath.'/languages/X_VlcShares_Plugins_Spainradio.it_IT.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_Spainradio.it_IT.ini',
	$basePath.'/languages/X_VlcShares_Plugins_Spainradio.es_ES.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_Spainradio.es_ES.ini',

);
