<?php 
// scripts/threads.php
 
/**
* Script for managing php threads
*/
 
// Initialize the application path and autoloading
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../core/application'));
set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/../library',
    get_include_path(),
)));
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->registerNamespace('X_');
 
// Define some CLI options
$getopt = new Zend_Console_Getopt(array(
	'list|l' 		=> 'Show the list of threads',
    'stop|s-s' 		=> 'Send a STOP message to a thread',
    'stopall|k'    	=> 'Send a STOP message to all threads',
	'info|i-s'		=> 'Show last update info about a thread',
	'try|t-s'		=> 'Try to execute a Runnable instance',
	'clear|c'		=> 'Clear old message queues',
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
$list 		= $getopt->getOption('l');
$stop 		= $getopt->getOption('s');
$stopall   	= $getopt->getOption('k');
$info		= $getopt->getOption('i');
$try		= $getopt->getOption('t');
$clear		= $getopt->getOption('c');

defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', 'development' );
 
// Initialize Zend_Application
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);
 
$bootstrap = $application->getBootstrap();

// Initialize and retrieve DB resource
$bootstrap->bootstrap('db');
$dbAdapter = $bootstrap->getResource('db');

try {

// Initialize and retrieve the Threads_Manager
$bootstrap->bootstrap('threads');



if ( $stop ) {
	$thread = X_Threads_Manager::instance()->getMonitor()->getThread($stop);
	if ( $thread->getState() != X_Threads_Thread_Info::STOPPED ) {
		X_Threads_Manager::instance()->stop($thread);
		sleep(2);
		// show the list for confirmation
		$list = true;
	} else {
		echo "Thread ID '{$stop}' is not RUNNING or WAITING".PHP_EOL;
	}
}


if ( $stopall ) {
	$threads = X_Threads_Manager::instance()->getMonitor()->getThreads();
	foreach ($threads as $thread) {
		/* @var $thread X_Threads_Thread_Info */
		if ( $thread->getState() != X_Threads_Thread_Info::STOPPED ) {
			X_Threads_Manager::instance()->stop($thread);
			// show the list for confirmation
			$list = true;
		} else {
			echo "Thread ID '{$stop}' is not RUNNING or WAITING".PHP_EOL;
		}
	}
	sleep(2);
}


if ( $info ) {
	$thread = X_Threads_Manager::instance()->getMonitor()->getThread($info);
	echo sprintf("Thread ID: %s\n\tState: %s\n\tInfo: %s", $thread->getId(), $thread->getState(), print_r($thread->getInfo(), true)).PHP_EOL;
	
	$messages = X_Threads_Manager::instance()->getMessenger()->showQueue($thread);
	echo "---- MESSAGES LIST ----".PHP_EOL;
	foreach ( $messages as $message ) {
		echo "\t" . ((string) $message) . PHP_EOL;
	}
	echo "-----------------------".PHP_EOL; 
}


if ( $list ) {
	$threads = X_Threads_Manager::instance()->getMonitor()->getThreads();
	echo "---- THREADS LIST ----".PHP_EOL;
	foreach ($threads as $thread) {
		/* @var $thread X_Threads_Thread_Info */
		echo sprintf("Thread ID: %s\n\tState: %s\n\tInfo: %s", $thread->getId(), $thread->getState(), print_r($thread->getInfo(), true)).PHP_EOL;
	}
	echo "----------------------".PHP_EOL;
}

if ( $try ) {
	
	$thread = new X_Threads_Thread('test-runnable', X_Threads_Manager::instance());
	if ( class_exists($try, true) ) {
		$ref = new ReflectionClass($try);
		if ( $ref->implementsInterface('X_Threads_Runnable') ) {
			$runnable = new $try();
			$runnable->run(array(
				'url' => "http://127.0.0.1:80/vlc-shares/xml/upnp/MediaServerServiceDesc.xml"
			), $thread);
		}
	}
}

if ( $clear ) {
	
	$threads = X_Threads_Manager::instance()->getMonitor()->getThreads();
	foreach ($threads as $thread) {
		/* @var $thread X_Threads_Thread_Info */
		if ( $thread->getState() == X_Threads_Thread_Info::STOPPED ) {
			X_Threads_Manager::instance()->getMonitor()->removeThread($thread);
			X_Threads_Manager::instance()->getMessenger()->clearQueue($thread);
		}
	}
	
}

return 0;


} catch (Exception $e) {
	echo "Error: {$e->getMessage()}".PHP_EOL;
	return 1;
}