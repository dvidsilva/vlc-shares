<?php 
// scripts/load.sqlite.php
 
/**
* Script for update VLCShares
* This script must be placed inside the public/ directory and executed directly
*/

/**
 * Check if a directory content is writable
 * (.svn directory is ignored)
 */
function _checkWritable($dir) {
		
	$dir = rtrim($dir, '\\/');
	if (is_dir($dir)) { 
		$objects = scandir($dir); 
		foreach ($objects as $object) { 
			if ($object != "." && $object != ".." && $object != '.svn') {
				if ( !is_writable($dir."/".$object) ) {
					throw new Exception("$dir/$object not writable");
				}
				if (filetype($dir."/".$object) == "dir") {
					_checkWritable($dir."/".$object);
				}
			} 
		}
   		reset($objects); 
	}
}



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
	file_exists(dirname(__FILE__)."/$file") or die("Required file '$file' not found!!!");
}

require_once(realpath(dirname(__FILE__).'/pclzip.php'));

// Initialize the application path and autoloading
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/../library',
    get_include_path(),
)));
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', 'production');

 
// Initialize Zend_Application
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

if ( !is_writable(APPLICATION_PATH . '/../') ) die('VLCShares directory must be writable');
try {
	_checkWritable(APPLICATION_PATH . '/../');
} catch ( Exception $e) {
	die($e->getMessage());
}

$pclzip = new PclZip(realpath(dirname(__FILE__).'/update.zip'));
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
	if ( !@unlink(dirname(__FILE__)."/$file") ) {
		echo "File not deleted: <b>'$file'</b>. Please, delete it manually!!!";
	} else { 
		echo "File <b>'$file'</b> deleted<br/>";
	}
}


