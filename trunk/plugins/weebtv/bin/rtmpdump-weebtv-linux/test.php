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
$http->setParameterPost('cid', '3');
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

$string = "./rtmpdump-weebtv --rtmp \"{$results['rtmp']}/{$results['playPath']}\" --swfUrl \"http://static2.weeb.tv/player.swf\" --weeb=\"{$results['ticket']}\" --live -q -o - ";
//$string = 'wget -O test.mp4 "http://localhost:8081/?r='.urlencode($results['rtmp'].'/'.$results['playPath'])."&s=".urlencode("http://static2.weeb.tv/player.swf")."&J=".urlencode($results['ticket'])."&v=1".'"';

//vlc
$string .= ' | vlc - --play-and-exit --sout="#std{access=http{mime=video/x-flv},mux=ffmpeg{mux=flv},dst=0.0.0.0:8081/stream}"';

echo $string."\n";

echo "Sleeping 5 seconds...";

sleep(5);

exec( $string );
