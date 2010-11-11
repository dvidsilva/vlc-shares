<?php 
// scripts/load.sqlite.php
 
/**
* Script for update VLCShares
* This script must be placed inside the public/ directory and executed directly
*/

// First I have to check for required files
$requiredFiles = array(
	'update.zip',
	'update.sqlite.sql',
	'pclzip.php'
);

$unlinkFiles = array(
	'update.zip',
	'update.sqlite.sql',
	'pclzip.php',
	'update.php'
);

foreach ($requiredFiles as $file) {
	file_exists(dirname(__FILE)."/$file") or die("Required file '$file' not found!!!");
}

require_once(realpath(dirname(__FILE).'/pclzip.php'));

// Initialize the application path and autoloading
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../application'));
set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/../library',
    get_include_path(),
)));
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();
 
// Initialize Zend_Application
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);


$pclzip = new PclZip(realpath(dirname(__FILE).'/update.zip'));
$pclzip->extract(PCLZIP_OPT_PATH, APPLICATION_PATH . '/../', PCLZIP_OPT_REPLACE_NEWER);

echo 'VLCShares files updated<br/>';


// Initialize and retrieve DB resource
$bootstrap = $application->getBootstrap();
$bootstrap->bootstrap('db');
$dbAdapter = $bootstrap->getResource('db');
 
try {
	$dataSql = file_get_contents(dirname(__FILE__) . '/update.sqlite.sql');
	if ( trim($dataSql) != '' ) {
		// use the connection directly to load sql in batches
		$dbAdapter->getConnection()->exec($dataSql);
		echo 'Database updated</br>';
	}
} catch (Exception $e) {
    echo 'AN ERROR HAS OCCURED:' . $e->getMessage() . '<br/>';
}

// time to unlink all files
foreach ($unlinkFiles as $file) {
	if ( !@unlink(dirname(__FILE)."/$file") ) echo "File not deleted: <b>'$file'</b>. Please, delete it manually!!!";
}

