<?php 
// context of this file is /scripts/cleanup.php

defined('APPLICATION_PATH') or die("This script can't be called directly. Please, use scripts/cleanup.php");

// dev cleanup file
// this file have to remove all things created by dev_bootstrap.php
$basePath = dirname(__FILE__);


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
