<?php

// Define path to application directory
if ( !defined('APPLICATION_PATH') ) {
	if ( realpath(dirname(__FILE__) . '/../application') === false ) {
		// in dev
		define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../../../core/application'));
	} else {
    	define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
	}
}

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    get_include_path(),
)));

/** Zend_Application */
((bool) @include_once('Zend/Application.php')) or die('VLCShares needs Zend Framework 1.10.6+. Please, install it in library/ folder');

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
$application->bootstrap();
            //->run();
            

$huluHelper = new X_VlcShares_Plugins_Helper_Hulu();

echo "<html><body>";
try {
	$fetched = $huluHelper->setLocation('324620/the-daily-show-with-jon-stewart-mon-jan-30-2012')->fetch();
	echo '<pre>'.print_r($fetched, true).'</pre>';

	echo '<b>RTMPDump: </b><br /><pre>' . ((string) X_RtmpDump::getInstance()->parseUri($fetched['url']) ) . '</pre>';
} catch (Exception $e) {
	echo "<h1>Exception:</h1><h2>{$e->getMessage()}</h2>";
	echo "<pre>{$e->getTraceAsString()}</pre>";
}

echo "</body></html>";
