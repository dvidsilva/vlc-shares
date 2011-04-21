<?php 
// context of this file is /scripts/cleanup.php

defined('APPLICATION_PATH') or die("This script can't be called directly. Please, use scripts/cleanup.php");

// dev cleanup file
// this file have to remove all things created by dev_bootstrap.php
$basePath = dirname(__FILE__);

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
