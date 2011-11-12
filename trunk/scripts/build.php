<?php 

function ignoreVcsCB($p_event, &$p_header) {
	if ( strpos($p_header['filename'], '/.svn/') !== false || ($p_header['folder'] && substr($p_header['filename'],-5) == '/.svn' ) ) {
		//echo "\t{$p_header['filename']} skipped".PHP_EOL;
		return 0;
	} else {
		return 1;
	}
}


// Initialize the application path and autoloading
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../core/application'));
set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/../library',
    get_include_path(),
)));
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->registerNamespace('X_');

require_once 'pclzip.php';

$coreInclude = array(
	APPLICATION_PATH.'/../robots.txt',
	APPLICATION_PATH.'/../README-ENG.txt',
	APPLICATION_PATH.'/../application',
	APPLICATION_PATH.'/../languages',
	APPLICATION_PATH.'/../data',
	APPLICATION_PATH.'/../public',
	APPLICATION_PATH.'/../library'
);

$pluginsDir = APPLICATION_PATH.'/../../plugins';

$distDir = '/../../dist';



// Define some CLI options
$getopt = new Zend_Console_Getopt(array(
	'all|a' => 'Create all packages',
	'core|c' => 'Create a package for vlc-shares core only',
    'plugins|p-s' => 'Create packages for a list of plugins (divided by comma)',
    'help|h'     => 'Help -- usage message',
));
try {
    $getopt->parse();
} catch (Zend_Console_Getopt_Exception $e) {
    // Bad options passed: report usage
    echo $e->getUsageMessage();
    return false;
}

// If help requested, report usage message
if ($getopt->getOption('h')) {
    echo $getopt->getUsageMessage();
    return true;
}
 
// Initialize values based on presence or absence of CLI options
$createAll		= $getopt->getOption('a');
$coreOnly		= $getopt->getOption('c');
$pluginsList	= $getopt->getOption('p');

// if no other args specified createall = true
if ( !$coreOnly && !$pluginsList ) {
	$createAll = true;
}


if ( !is_writable(APPLICATION_PATH.'/../') ) {
	echo '[EEE] Project patch is not writable'.PHP_EOL;
	return false;
}

if ( !file_exists(APPLICATION_PATH.$distDir) ) {
	if ( !mkdir(APPLICATION_PATH.$distDir) ) {
		echo "[EEE] Cannot create DIST directory in ".APPLICATION_PATH.$distDir.PHP_EOL;
		return false;
	}
}

if ( !is_writable(APPLICATION_PATH.$distDir) ) {
	echo "[EEE] Dist dir is not writable".PHP_EOL;
}

if ( $createAll || $coreOnly ) {
	
	// package the core
	// get the current vlc-shares version from X_VlcShares::VERSION
	
	$coreVersion = X_VlcShares::VERSION;
	
	$coreFilename = APPLICATION_PATH.$distDir."/vlc-shares_$coreVersion.zip";
	
	if ( file_exists($coreFilename) ) {
		if ( !unlink($coreFilename) ) {
			echo "[EEE] A core package $coreFilename already exists and i can remove it".PHP_EOL;
			return false;
		}
	}
	
	$coreZip = new PclZip($coreFilename);
	
	$response = $coreZip->create(
		implode(',', $coreInclude), // all directories and file to include
		PCLZIP_OPT_REMOVE_PATH, realpath(APPLICATION_PATH.'/..'), // remove the local path
		PCLZIP_OPT_ADD_PATH, 'vlc-shares', // insert all element inside a /vlc-shares/ folder,
		PCLZIP_CB_PRE_ADD, 'ignoreVcsCB'
	);
	
	if ( $response == 0 ) {
		echo "[EEE] Error creating core package: {$coreZip->errorInfo(true)}".PHP_EOL;
		return false;
	} else {
		echo "Core package created".PHP_EOL;
	}
	
	unset($coreZip);
	
	if ( $coreOnly ) {
		return true;
	}
	
}

if ( $createAll || $pluginsList ) {
	
	if ( is_string($pluginsList) && trim($pluginsList) != '' ) {
		$pluginsList = explode(',', $pluginsList);
		$createAll = false;
	} else {
		$createAll = true;
	}
	
	$dir = new DirectoryIterator($pluginsDir);
	
	foreach ($dir as $entry) {
		/* @var $entry DirectoryIterator */
		
		try {
			
			// it doesn't allow dotted directories (. and ..) and file/symlink
			if ( $entry->isDot() || !$entry->isDir() ) {
				continue;
			}
			
			if ( !$createAll && array_search($entry->getFilename(), $pluginsList ) === false ) {
				continue;
			}
			
			// plugin manifest file. I need it to get the version
			$manifestFile = $entry->getRealPath() . '/manifest.xml';
			
			if ( !file_exists($manifestFile) ) {
				echo "[WWW] {$entry->getFilename()} isn't a plugin folder. Skipped".PHP_EOL;
				continue;
			}
			
			$dom = new Zend_Dom_Query(file_get_contents($manifestFile));
			
			$result = $dom->queryXpath('//metadata/version');
			$pluginVersion = trim($result->current()->nodeValue);
			
			$result = $dom->queryXpath('//metadata/status');
			$pluginState = trim($result->current()->nodeValue);
			
			if ( $pluginState == 'stable' ) {
				$pluginState = '';
			}
			
			$pluginFilename = APPLICATION_PATH.$distDir."/plugin_{$entry->getFilename()}_$pluginVersion$pluginState.zip";
			
			$pluginZip = new PclZip($pluginFilename);
			
			$response = $pluginZip->create(
				$entry->getRealPath(), // add all the dir content
				PCLZIP_OPT_REMOVE_PATH, $entry->getRealPath(), // remove local path
				PCLZIP_CB_PRE_ADD, 'ignoreVcsCB' // skip .svn folders
			); 
			
			
			if ( $response == 0 ) {
				echo "[EEE] Error creating {$entry->getFilename()} package: {$pluginZip->errorInfo(true)}".PHP_EOL;
			} else {
				echo "Plugin package {{$entry->getFilename()}} created".PHP_EOL;
			}
			
			unset($pluginZip);
			
		} catch (Exception $e) {
			echo "[EEE] Error: {$e->getMessage()}".PHP_EOL;
		}
	}
	
}





