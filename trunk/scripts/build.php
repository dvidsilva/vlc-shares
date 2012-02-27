<?php 

function ignoreVcsCB($p_event, &$p_header) {
	if ( strpos($p_header['filename'], '/.svn/') !== false || ($p_header['folder'] && substr($p_header['filename'],-5) == '/.svn' ) ) {
		//echo "\t{$p_header['filename']} skipped".PHP_EOL;
		return 0;
	} else {
		return 1;
	}
}

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

function copy_recursive($src, $dst) {
	//if (file_exists($dst)) rrmdir($dst);
	if (is_dir($src)) {
		if ( !file_exists($dst) ) mkdir($dst);
		//mkdir($dst);
		$files = scandir($src);
		foreach ($files as $file) {
			// ignore .svn directories
			if ($file != "." && $file != ".." && $file != '.svn') {
				copy_recursive("$src/$file", "$dst/$file");
			}
		}
	} else if (file_exists($src)) {
		copy($src, $dst);
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
	APPLICATION_PATH.'/../library',
	APPLICATION_PATH.'/../bin',
);

$pluginsDir		= APPLICATION_PATH.'/../../plugins';
$distDir 		= '/../../dist';
$buildDir 		= APPLICATION_PATH.'/../../build';
$debianStub		= APPLICATION_PATH.'/../../scripts/debian/';

// Define some CLI options
$getopt = new Zend_Console_Getopt(array(
	'all|a' 		=> 'Create all packages',
	'core|c' 		=> 'Create a package for vlc-shares core',
    'plugins|p-s' 	=> 'Create packages for a list of plugins (divided by comma)',
	'deb|d' 		=> 'Prepare build dire for deb file creation (include -c)',
	'iss|i'			=> 'Build an inno setup installer starting from easyphp directory (preclude -c)',
	'iss-winec|W-s' => 'Path to wine\'s drive_c folder (Default: "~/.wine/drive_c")',
    'help|h'     	=> 'Help -- usage message',
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
$createCore		= $getopt->getOption('c');
$pluginsList	= $getopt->getOption('p');
$createDeb		= $getopt->getOption('d');
$createIss		= $getopt->getOption('i');
$wineCPath		= $getopt->getOption('W');

// if no other args specified createall = true
if ( !$createDeb && !$createIss && !$createCore && !$pluginsList ) {
	$createAll = true;
}

if ( ($createDeb || $createIss ) && !$createCore ) {
	$createCore = true;
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

if ( $createAll || $createCore ) {
	
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


if ( $createAll || $createDeb ) {

	$coreVersion = X_VlcShares::VERSION;

	$coreFilename = APPLICATION_PATH.$distDir."/vlc-shares_$coreVersion.zip";

	if ( !file_exists($coreFilename) ) {
		echo "[EEE] Core package $coreFilename not found and required for DEB installer creation".PHP_EOL;
		return false;
	}

	if ( file_exists("{$buildDir}/debian/") ) {
		echo "Cleaning up {$buildDir}".PHP_EOL;
	}
	
	rm_recursive("{$buildDir}/debian/");
	mkdir("{$buildDir}/debian/opt/", 0777, true);
	
	// copy DEBIAN folder inside build dir
	copy_recursive($debianStub, "{$buildDir}/debian/");
	
	$coreZip = new PclZip($coreFilename);
	$coreZip->extract(PCLZIP_OPT_PATH, "{$buildDir}/debian/opt/");
	
	// fix permissions for postinst and postrm files
	chmod("{$buildDir}/debian/DEBIAN/postinst", 0775);
	chmod("{$buildDir}/debian/DEBIAN/postrm", 0775);
	
	//echo "All ready, go and type \"dpkg --build debian\"".PHP_EOL;
	
	passthru("fakeroot dpkg --build {$buildDir}/debian/");
	// debian.deb created from dpkg
	
	if ( !file_exists("{$buildDir}/debian.deb") ) {
		echo "[EEE] Looks like DPKG failed to create the deb".PHP_EOL;
		return false;
	}
	
	$debFilename = APPLICATION_PATH.$distDir."/vlc-shares_$coreVersion-1_all.deb";
	
	if ( !rename("{$buildDir}/debian.deb", $debFilename) ) {
		echo "[EEE] DEB renaming failed".PHP_EOL;
		return false;
	}
	
	// cleaning up?
	rm_recursive("{$buildDir}/debian/");
	echo "DEB package created: vlc-shares_$coreVersion-1_all.deb".PHP_EOL;
}

if ( $createAll || $createIss ) {
	
	$homeDir = getenv("HOME");
	echo "Base dir: {$homeDir}/.wine/drive_c".PHP_EOL;
	
	if ( !$wineCPath ) {
		$wineCPath = "{$homeDir}/.wine/drive_c";
	} else {
		$wineCPath = rtrim($wineCPath, '/\\');
	}
	
	if ( !file_exists("{$wineCPath}/EasyPHP-5.3.3/") ) {
		echo "[EEE] Invalid EasyPHP folder: '{$wineCPath}/EasyPHP-5.3.3/'".PHP_EOL;
		return false;
	}
	
	$EPHome = "{$wineCPath}/EasyPHP-5.3.3";
	
	$coreVersion = X_VlcShares::VERSION;
	$coreFilename = APPLICATION_PATH.$distDir."/vlc-shares_$coreVersion.zip";
	if ( !file_exists($coreFilename) ) {
		echo "[EEE] Core package $coreFilename not found and required for IS installer creation".PHP_EOL;
		return false;
	}
	
	if ( file_exists("{$EPHome}/vlc-shares/") ) {
		echo "Cleaning up {$EPHome}/vlc-shares/".PHP_EOL;
		rm_recursive("{$EPHome}/vlc-shares/");
	}
	
	if ( file_exists("{$EPHome}/conf_files/httpd.conf") ) {
		// clean httpd config file
		echo "Cleaning up [APACHE CONF] {$EPHome}/conf_files/httpd.conf".PHP_EOL;
		unlink("{$EPHome}/conf_files/httpd.conf");
	}
	//copy(dirname(__FILE__)."/iss/httpd.conf", "{$EPHome}/conf_files/httpd.conf");
	
	// read the vlc-shares.conf content
	$vlcsharesModuleConf = file_get_contents(dirname(__FILE__)."/iss/vlc-shares.conf");
	// read the httpd.conf content
	$httpdConf = file_get_contents(dirname(__FILE__)."/iss/httpd.conf");
	
	// fix \r\n from \n only
	$vlcsharesModuleConf = preg_replace("/(^\r)\n/", "\r\n", $vlcsharesModuleConf);
	
	// try to replace the VLCSHARESCONF entry point inside the HTTPD.conf
	// with the real conf file
	$num = 0;
	$httpdConf = str_replace('#{{{VLC-SHARES-CONF-ENTRYPOINT}}}#', $vlcsharesModuleConf, $httpdConf, $num);
	if ( $num == 0 ) {
		echo "[EEE] Entry point for vlc-shares.conf inside httpd.conf not found. Build will fail".PHP_EOL;
		return; 
	}
	
	// subsitution found and done
	// store the compiled httpd.conf
	if ( file_put_contents("{$EPHome}/conf_files/httpd.conf", $httpdConf) === false ) {
		echo "[EEE] Httpd.conf creation failed".PHP_EOL;
		return;
	}
	
	
	if ( file_exists("{$EPHome}/conf_files/php.ini") ) {
		// clean httpd config file
		echo "Cleaning up [PHP CONF] {$EPHome}/conf_files/php.ini";
		unlink("{$EPHome}/conf_files/php.ini");
	}
	if ( !copy(dirname(__FILE__)."/iss/php.ini", "{$EPHome}/conf_files/php.ini") ) {
		echo "[EEE] php.ini copy failed".PHP_EOL;
		return;
	}
	
	
	if ( file_exists(dirname(__FILE__)."/iss/Output/") ) {
		echo "Cleaning up ".dirname(__FILE__)."/iss/Output/".PHP_EOL;
		rm_recursive(dirname(__FILE__)."/iss/Output/");
	}
	
	$coreZip = new PclZip($coreFilename);
	$coreZip->extract(PCLZIP_OPT_PATH, "{$EPHome}/");
	
	$ISCC = '/bin/iscc';
	$ISScript = dirname(__FILE__)."/iss/easyphp-5.3.3-unified-installer.iss";
	
	//iscc - < ./iss/easyphp-5.3.3-unified-installer.iss
	passthru("{$ISCC} {$ISScript}");

	echo PHP_EOL;
	
	$issInstaller = dirname(__FILE__)."/iss/Output/vlc-shares.exe";
	
	if ( !file_exists($issInstaller) ) {
		echo "[EEE] Looks like IS failed to create the installer in '{$issInstaller}'".PHP_EOL;
		return false;
	}
	
	$installerFilename = APPLICATION_PATH.$distDir."/vlc-shares_{$coreVersion}_installer.exe";
	
	if ( !rename($issInstaller, $installerFilename) ) {
		echo "[EEE] Installer renaming failed".PHP_EOL;
		return false;
	}
	
	// cleaning up?
	rm_recursive(dirname(__FILE__)."/iss/Output/");
	echo "Installer created: vlc-shares_{$coreVersion}_installer.exe".PHP_EOL;
	
	
}
