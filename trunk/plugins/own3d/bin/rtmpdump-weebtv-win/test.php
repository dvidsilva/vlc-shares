<?php

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath('/usr/share/php/libzend-framework-php/'),
    get_include_path(),
)));

include_once('Zend/Http/Client.php');

$url = "http://weeb.tv/setPlayer";

$http = new Zend_Http_Client($url, array(
	'headers' => array(
		'User-Agent' => 'Mozilla/5.0 (X11; Linux i686) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.56 Safari/535.11',
	)
));

$username = '';
$password = '';

$http->setParameterPost('firstConnect', '1');
$http->setParameterPost('watchTime', '0');
$http->setParameterPost('cid', '21');
$http->setParameterPost('ip', 'NaN');

$str = $http->request('POST')->getBody();

$params = array();
parse_str($str, $params);

$results = array();

$check = array(
	'ticket' => 73,
	'rtmp' => 10,
	'time' => 16,
	'playPath' => 11
);

foreach ($check as $label => $key) {
	if ( isset($params[$key]) ) {
		$results[$label] = $params[$key];
	}
}

echo print_r($results, true) . "\n";


echo "rtmpdump-weebtv -r \"{$results['rtmp']}/{$results['playPath']}\" -s \"http://static2.weeb.tv/player.swf\" --weeb=\"{$results['ticket']};{$username};{$password}\" --live"."\n";

exec( "wine rtmpdump-weebtv.exe -r \"{$results['rtmp']}/{$results['playPath']}\" -s \"http://static2.weeb.tv/player.swf\" --weeb=\"{$results['ticket']}\" --live");
