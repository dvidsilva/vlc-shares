<?php


// list of update-specific files

// list of files that must be
// there before the install
// path is relative to vlc-shares/public/
$requiredFiles = array(
	'update.0.5.sqlite.sql',
	'update.0.5.1_beta4.sqlite.sql'
);

// list of files that must be
// removed after the installation
// path is relative to vlc-shares/public/
$unlinkFiles = array(
	'update.0.5.sqlite.sql',
	'update.0.5.1_beta4.sqlite.sql'
);

// list of valid start version for this update 
$validVersionUpdate = array(
	'0.5', '0.5.1_beta4'
);

/**
 * Triggered before everything
 * @param string $version Current version found 
 * @return boolean
 */
function __update_pre($version) {
	return true;
}

/**
 * Triggered after update script, before unlink
 * @param string $version Current version found 
 * @return boolean
 */
function __update_post($version) {
	return true;
}