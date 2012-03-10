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

// Define some CLI options
$getopt = new Zend_Console_Getopt(array(
		'class|c-s' 	=> 'Set permission class required by resources',
		'store|s-s'		=> 'Store to file',
		'append|a-s'	=> 'Append to file (-l required)',
		'limit|l-s'		=> 'Limit check to a single class only',
		'help|h'     	=> 'Help -- usage message',
));
try {
	$getopt->parse();
} catch (Zend_Console_Getopt_Exception $e) {
	// Bad options passed: report usage
	echo $e->getUsageMessage();
	return false;
}

// Initialize values based on presence or absence of CLI options
$defClass		= $getopt->getOption('c');
$file			= $getopt->getOption('s');
$append			= $getopt->getOption('a');
$limit			= $getopt->getOption('l');


// If help requested, report usage message
if (!$defClass || $getopt->getOption('h')) {
	echo $getopt->getUsageMessage();
	return true;
}



$buffer = '';

$template = <<<TMPL

INSERT INTO plg_acl_resources
	("key", "class", "generator") VALUES
	('default/%s/%s', '%s', '');

TMPL;

// get controllers

$dir = new DirectoryIterator(APPLICATION_PATH . '/controllers/');

foreach ( $dir as $entry ) {
	/* @var $entry DirectoryIterator */
	if ( !$entry->isFile() ) continue;
	
	$filename = $entry->getFilename();
	$match = array();
	if ( !preg_match('/^(?P<controller>[A-Z][a-zA-Z]+?)Controller.php$/', $filename, $match) ) continue;
	
	if ( $limit && $limit != $match['controller'] ) continue;
	
	require_once $entry->getRealPath();
	
	$controller = $match['controller'];
	$r = new Zend_Reflection_File($entry->getRealPath());
	
	$classes = $r->getClasses();
	
	if ( !count($classes) ) continue;
	
	/* @var $class Zend_Reflection_Class */
	$class = $classes[0];
	$methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
	
	if ( !count($methods) ) continue;
	
	foreach ($methods as $method) {
		/* @var $method Zend_Reflection_Method */
		$match = array();
		if ( !preg_match('/^(?P<action>[a-z][A-Za-z]+?)Action$/', $method->getName(), $match) ) continue;
		
		$action = $match['action'];
		
		// got controller, got action
		$buffer .= sprintf($template, strtolower($controller), strtolower($action), $defClass);
		
	}
	
}

//echo $buffer . "\n";

if ( $file  && !$limit ) {
	file_put_contents(dirname(__FILE__).'/'.$file, $buffer);
} elseif ( $limit && $append ) {
	file_put_contents(dirname(__FILE__).'/'.$append, $buffer, FILE_APPEND);
} else {
	echo $buffer . "\n";	
}
