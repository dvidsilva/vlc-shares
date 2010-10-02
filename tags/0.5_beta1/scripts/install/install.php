<?php


define('PATH_BASE', dirname(__FILE__));
define('PATH_INCLUDES', PATH_BASE.'/includes');
define('PATH_LIBRARY', PATH_BASE.'/library');

define('CONF_ZENDMINIMUM', '1.10.0');
define('CONF_INSTALLPACKAGE', PATH_BASE.'/vlc-shares.install.zip');
define('CONF_FORMFILE', PATH_INCLUDES.'/form.ini');

// Libreria necessaria per l'estrazione
require_once(PATH_INCLUDES . '/pclzip.php');
// Vediamo se c'e' lo ZendFramework
@include 'Zend/Version.php';
if ( !class_exists('Zend_Version') || $comparate = (Zend_Version::compareVersion(CONF_ZENDMINIMUM) == 1 ) ) {
	// Esegue uno script che scarica e decomprime l'archivio di Zend Framework
	//require_once(PATH_LIBRARY.'/fetch.library.php');
	set_include_path(implode(PATH_SEPARATOR, array(
	    realpath(PATH_LIBRARY),
	    get_include_path(),
	)));
	if ( @!is_null($comparate) ) {
		die('<a href="?refresh=1">Please, refresh the page to continue installation</a>');
	}
}
// attivo gli autoloader per lo Zend
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

//$view = new Install_Renderer();

$messages = array();
$view = new Zend_View();
$view->setScriptPath(PATH_INCLUDES);


// prerequisiti
if (!is_writable(PATH_BASE)) $messages[] = array('Path is not writeable', 'error');
if (!is_readable(CONF_INSTALLPACKAGE)) $messages[] = array('VLCShares package not found (or not readable)', 'error'); 
//if (!is_readable(CONF_FORMFILE)) $messages[] = array('Form config file is not readable', 'error');

$view->general_languages = array(
	array(
		'label' 	=>	'English',
		'value' 	=>	base64_encode('APPLICATION_PATH "/../languages/en_GB.ini"'),
		'selected'	=>	'selected="selected"'
	),
	array(
		'label' 	=>	'Italiano',
		'value' 	=>	base64_encode('APPLICATION_PATH "/../languages/it_IT.ini"'),
	),
);

//=== CONFIGURAZIONI GENERALI
$view->general_debug_path = sys_get_temp_dir() . '/vlcshares-debug.log';
$view->general_apache_altPort = $_SERVER['SERVER_PORT'];

//=== CONFIGURAZIONI VLC
$view->vlc_path = _is_windows() ? 'C:\Programs Files\VideoLan\Vlc\vlc.exe' : '/usr/bin/vlc' ;
$view->vlc_args = htmlentities("--play-and-exit {%source%} --sout=\"#{%profile%}\" --sout-keep {%subtitles%} {%audio%} {%filters%}");
$view->vlc_stream = 'http://'.$_SERVER['SERVER_ADDR'].':8081';
$view->vlc_adapters = array(
	array(
		'label' 	=>	'for Windows',
		'value' 	=>	'Windows',
		'selected'	=>	_is_windows() ? 'selected="selected"' : ''
	),
	array(
		'label' 	=>	'for Linux',
		'value' 	=>	'Linux',
		'selected'	=>	!_is_windows() ? 'selected="selected"' : ''
	),
);
$view->vlc_adapter_pidFile = sys_get_temp_dir() . '/vlcLock.pid';
$view->vlc_commanders = array(
	array(
		'label' 	=>	'Http',
		'value' 	=>	'X_Vlc_Commander_Http',
		'selected'	=>	'selected="selected"',
		'path'		=>	base64_encode('APPLICATION_PATH "/../library/X/Vlc/Commander/Http.php"'),
		'options'	=>	array(
			array(
				'name'		=>	'http_command',
				'label'		=>	'HTTP Command string',
				'value'		=>	'http://{%host%}:{%port%}/requests/status.xml{%command%}',
			),
			array(
				'name'		=>	'http_host',
				'label'		=>	'VLC HTTP Host',
				'value'		=>	'127.0.0.1',
			),
			array(
				'name'		=>	'http_port',
				'label'		=>	'VLC HTTP Port',
				'value'		=>	'4212',
			),
			array(
				'name'		=>	'http_timeout',
				'label'		=>	'Response timeout',
				'value'		=>	'1',
			)
		)
	),
	array(
		'label' 	=>	'Rc',
		'value' 	=>	'X_Vlc_Commander_Rc',
		'path'		=>	base64_encode('APPLICATION_PATH "/../library/X/Vlc/Commander/Rc.php"'),
		'options'	=>	array(
			array(
				'name'		=>	'nc_command',
				'label'		=>	'Telnet Command string',
				'value'		=>	'echo {%command%} | nc {%host%} {%port%}' . (_is_windows() ? ' -w 1' : '') ,
			),
			array(
				'name'		=>	'nc_host',
				'label'		=>	'VLC HTTP Host',
				'value'		=>	'127.0.0.1',
			),
			array(
				'name'		=>	'nc_port',
				'label'		=>	'VLC HTTP Port',
				'value'		=>	'4212',
			)
		)
	),
);


$view->shares = array();


$view->messages = $messages;

echo $view->render('install.phtml');


function _is_windows() {
	return ( array_key_exists('WINDIR', $_SERVER) || array_key_exists('windir', $_SERVER));
}
