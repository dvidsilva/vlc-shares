<?php 

// test file, no autoload
require_once 'library/X/FLVInfo.php';
require_once 'library/X/AMF0Parser.php';

function d($msg = "") {
	echo $msg.PHP_EOL;
}

$opt = "i:s:";
$argv = getopt($opt);
if ( @$argv['i'] == false ) {
	d('Usage: test.php -i "INPUT" [-s "SAMPLESIZE"]');
	d("\t INPUT: file path or http/https url");
	d("\t SAMPLESIZE: number of bytes for the sample dump");
	return;
}

$INPUT = $argv['i'];
$BUFFER = $argv['s'];
if ( preg_match('/^https?:\/\//i', $INPUT) || $BUFFER ) {
	d("Dumping source...");
	if ( $BUFFER ) {
		$BUFFER = intval($BUFFER, 10);
		d("Custom sample size used: {$BUFFER}");
	} else {
		$BUFFER = 9000;
	}
	
	// dump 9kb of the file to analyze it
	define('TMP_FILE', tempnam(sys_get_temp_dir(), 'fia'));
	if ( $BUFFER ) {
		define('HEADER_LENGTH', $BUFFER);
	}
	
	d(sprintf("Downloading %s bytes of the stream in temp file: %s", HEADER_LENGTH, TMP_FILE ));
	
	$src = fopen($INPUT, 'r');
	$dest = fopen(TMP_FILE, 'w');
	stream_copy_to_stream($src, $dest, HEADER_LENGTH);
	
	fclose($src);
	fclose($dest);
	
	$INPUT = TMP_FILE;
}

d(sprintf("Analyzing: %s", $INPUT));

try {
	$flvinfo = new X_FLVInfo();
	$info = $flvinfo->getInfo($INPUT, true, true);
} catch (Exception $e) {
	d(sprintf("Parser error: %s", $e->getMessage()));
	d('----------------');
	d($e->getTraceAsString());
	d();
}

//d(var_export($info, true));

if ( defined('TMP_FILE') ) {
	d(sprintf("Unlinking temp file: %s", TMP_FILE));
	@unlink(TMP_FILE);
}

d("All done");
return 1;