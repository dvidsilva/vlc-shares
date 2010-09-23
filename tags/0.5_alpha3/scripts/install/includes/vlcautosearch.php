<?php 

$autosearch_LINUX = array(
	'/usr/bin/vlc',
	'/bin/vlc',
	'/usr/local/bin/vlc',
);


$autosearch_WINDOWS = array(
	'C:/Programmi/VideoLan/Vlc/vlc.exe',
	'C:/Programmi/Vlc/vlc.exe',
	'C:/Programmi/VideoLan/vlc.exe',
	'C:/Program files/VideoLan/Vlc/vlc.exe',
	'C:/Program files/Vlc/vlc.exe',
	'C:/Program files/VideoLan/vlc.exe',
	'C:/Program files (x86)/VideoLan/Vlc/vlc.exe',
	'C:/Program files (x86)/Vlc/vlc.exe',
	'C:/Program files (x86)/VideoLan/vlc.exe',
);

@include 'Zend/Version.php';
if ( !class_exists('Zend_Version') ) {
	set_include_path(implode(PATH_SEPARATOR, array(
	    realpath(PATH_LIBRARY),
	    get_include_path(),
	)));
}
// attivo gli autoloader per lo Zend
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();


$searchPath = _is_windows() ? $autosearch_WINDOWS : $autosearch_LINUX;
$found = false;

foreach ($searchPath as $path) {
	if ( file_exists($path) ) {
		$found = $path;
		break;
	}
}

if ( $found !== false ) {
	$found = array('path' => $found, 'error' => false);
} else {
	$found = array('path' => '', 'error' => true);
}

header('Content-Type: application/json');
echo Zend_Json::encode($found);

function _is_windows() {
	return ( array_key_exists('WINDIR', $_SERVER) || array_key_exists('windir', $_SERVER));
}
