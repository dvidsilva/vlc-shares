<?php 

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


$pluginsDir = APPLICATION_PATH . '/../../plugins/';

$modelPlugin = '__ptemplate';


$directories = array(
	// directory path => feature flag
	'/application/controllers/'				=> 'controllers',
	'/application/models/DbTable/'			=> 'models',
	'/application/forms/'					=> 'forms',
	'/application/layouts/scripts/'			=> 'layouts',
	'/application/views/scripts/%1$s/'		=> 'views',
	'/library/X/VlcShares/Plugins/'			=> 'plugins',
	'/library/X/VlcShares/Plugins/Helper'	=> 'helpers',
	'/languages/'							=> 'languages',
	'/public/images/%1$s/'					=> 'images',
	'/public/css/%1$s/'						=> 'css',
	'/public/js/%1$s/'						=> 'js',	
);


$files = array(
	'/application/controllers/%3$s.php'					=> 'controllers',
	'/application/views/scripts/%1$s/index.phtml'		=> 'views',
	'/library/X/VlcShares/Plugins/%2$s.php'				=> 'plugins',
	'/languages/X_VlcShares_Plugins_%2$s.it_IT.ini'		=> 'languages',
	'/languages/X_VlcShares_Plugins_%2$s.en_GB.ini'		=> 'languages',
	'/manifest.xml'										=> 'plugins',
	'/README.txt'										=> 'plugins',
	'/install.sql'										=> 'plugins',
	'/uninstall.sql'									=> 'plugins',
	'/dev_bootstrap.php'								=> 'plugins',
	'/dev_cleanup.php'									=> 'plugins',
);


// Define some CLI options
$getopt = new Zend_Console_Getopt(array(
	'key|k-s' => 'Plugin key',
	'name|n-s' => 'Plugins name',
	'all|a' => 'Create all elements',
	'ignorekey|i' => 'Ignore error if the key already exists',
    'elements|e-s' => 'Create a list of elements (divided by comma). Supported: plugins, helpers, controllers, models, views, forms, layouts, languages, images, css, js',
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
$pluginKey		= $getopt->getOption('k');
$pluginName		= $getopt->getOption('n');
$createAll		= $getopt->getOption('a');
$ignoreKeyExists = $getopt->getOption('i');
$createElements = $getopt->getOption('e');
$createElements = @explode(',', $createElements);
if ( !is_array($createElements) ) {
	$createElements = array('plugins');
}
if ( array_search('plugins', $createElements) === false ) {
	$createElements[] = 'plugins';
}


if ( empty($pluginKey) || empty($pluginName) ) {
	echo "[EEE] Plugin key and name must be specified".PHP_EOL;
	return false;
}


if ( !$ignoreKeyExists && file_exists($pluginsDir.$pluginKey) ) {
	echo '[EEE] Key already used'.PHP_EOL;
	return false;
}


if ( !file_exists( $pluginsDir.$pluginKey ) && !mkdir($pluginsDir.$pluginKey, 0777, true) ) {
	echo "[EEE] Error creating main plugin folder ($pluginsDir$pluginKey)".PHP_EOL;
	return false;
}


foreach ($directories as $dirPath => $cType) {
	if ( $createAll || array_search($cType, $createElements) !== false ) {
		$dirPath = sprintf($dirPath, $pluginKey, $pluginName, ucfirst($pluginKey) );
		if ( !file_exists($pluginsDir.$pluginKey.$dirPath) && !mkdir($pluginsDir.$pluginKey.$dirPath, 0777, true) ) {
			echo "[WWW] Error creating folder ($pluginsDir$pluginKey$dirPath => $cType)".PHP_EOL;
		}
	}
}

foreach ($files as $filePath => $cType) {
	if ( $createAll || array_search($cType, $createElements) !== false ) {
		$filePath = sprintf($filePath, $pluginKey, $pluginName, ucfirst($pluginKey) );
		// if exists a file with the same name in models, copy it
		if ( file_exists($pluginsDir.$modelPlugin.$filePath) ) {
			if ( !copy($pluginsDir.$modelPlugin.$filePath, $pluginsDir.$pluginKey.$filePath)) {
				echo "[WWW] Error copying (from model) file ($pluginsDir$pluginKey$filePath => $cType)".PHP_EOL;
				if ( !touch($pluginsDir.$pluginKey.$filePath) ) {
					echo "[WWW] Error creating file ($pluginsDir$pluginKey$filePath => $cType)".PHP_EOL;
				}
			}
		} else {
			if ( !touch($pluginsDir.$pluginKey.$filePath) ) {
				echo "[WWW] Error creating file ($pluginsDir$pluginKey$filePath => $cType)".PHP_EOL;
			}
		}
	}
}

// manifest creation?


echo "All done. Bye".PHP_EOL;
return true;
