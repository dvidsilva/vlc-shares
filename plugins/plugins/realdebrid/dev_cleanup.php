<?php 
// === PLEASE, DO NOT CHANGE THOSE LINES ===
// context of this file is /scripts/cleanup.php
defined('APPLICATION_PATH') or die("This script can't be called directly. Please, use scripts/cleanup.php");
// function rm_recursive($path) definited in /scripts/cleanup.php
// dev cleanup file
// this file have to remove all things created by dev_bootstrap.php
$basePath = dirname(__FILE__);
// =========================================

// === YOUR SETTINGS START HERE ============
/**
 * Copy here the same value inside the $neededDirectories
 * of dev_bootstrap.php file
 */
$neededDirectories = array(
	//APPLICATION_PATH.'/../public/my/created/folder/' // <--- THIS IS AN EXAMPLE:
);

/**
 * Copy here the same value inside the $neededLinks
 * of dev_bootstrap.php file
 */
$neededLinks = array(
	//$basePath.'/public/images/myfolder/' => APPLICATION_PATH.'/../public/images/myfolder', // <--- THIS IS AN EXAMPLE FOR FOLDERS
	$basePath.'/languages/X_VlcShares_Plugins_RealDebrid.en_GB.ini' => APPLICATION_PATH.'/../languages/X_VlcShares_Plugins_RealDebrid.en_GB.ini',
);
