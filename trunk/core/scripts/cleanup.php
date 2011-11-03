<?php 
// scripts/cleanup.php
 
/**
* Script for env cleanup
*/
 
/**
 * Remove a directory (even if not empty)
 */
function rm_recursive($dir) {
	$dir = rtrim($dir, '\\/');
	if (is_dir($dir)) { 
		$objects = scandir($dir); 
		foreach ($objects as $object) { 
			if ($object != "." && $object != "..") { 
				if (filetype($dir."/".$object) == "dir") rm_recursive($dir."/".$object); else unlink($dir."/".$object); 
			} 
		} 
		reset($objects); 
		rmdir($dir); 
	} 	
}


// Initialize the application path and autoloading
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/../library',
    get_include_path(),
)));
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();


// check for extra plugins path
$extraPluginsPath = APPLICATION_PATH . '/../extra/plugins/';
if ( !file_exists($extraPluginsPath) ) {
	echo "Extra plugins path not found".PHP_EOL;
	return false;
}

// scan /extra/plugins/$directory/ for dev_cleanup.php file
// and execute it.
$directory = new DirectoryIterator($extraPluginsPath);
foreach ($directory as $entry) {
	/* @var $entry DirectoryIterator */
	// it doesn't allow dotted directories (. and ..) and file/symlink
	if ( $entry->isDot() || !$entry->isDir() ) {
		continue;
	}
	
	$bootstrapFile = $entry->getRealPath() . '/dev_cleanup.php';
	
	if ( file_exists($bootstrapFile) ) {
		echo "Cleanup file found: $bootstrapFile".PHP_EOL;
		{
			
			$neededDirectories = array();
			$neededLinks = array();
			$basePath = '';
			
			// delegate everything to dev_cleanup.php
			@include_once $bootstrapFile;

			foreach ($neededDirectories as $dir) {
				if ( file_exists($dir) ) {
					echo "   '---- Removing directory $dir".PHP_EOL;
					rm_recursive($dir);
				}
			}
			
			foreach ($neededLinks as $linkFrom => $linkTo) {
				if ( file_exists($linkTo)) {
					echo "   '---- Unlinking $linkTo".PHP_EOL;
					unlink($linkTo);
				}
			}
		
		}		
		echo PHP_EOL;
	} 
}

echo "All done".PHP_EOL;

// generally speaking, this script will be run from the command line
return true; 