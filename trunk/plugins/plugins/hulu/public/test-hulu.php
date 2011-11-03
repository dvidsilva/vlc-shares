<?php

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

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
$fetched = $huluHelper->setLocation('263463/the-colbert-report-thu-jul-28-2011')->_fetch();
echo '<pre>'.print_r($fetched, true).'</pre>';

echo '<b>RTMPDump: </b><textarea>' . ((string) X_RtmpDump::getInstance()->parseUri($fetched['url']) ) . '</textarea>';

echo "</body></html>";
