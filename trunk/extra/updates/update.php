<?php 
// scripts/load.sqlite.php
 
/**
* Script for update VLCShares
* This script must be placed inside the public/ directory and executed directly
*/

define('UPDATESCRIPT_VERSION', '1');

/**
 * Check if a directory content is writable
 * (.svn directory is ignored)
 */
function _checkWritable($dir) {
		
	$dir = rtrim($dir, '\\/');
	if (is_dir($dir)) { 
		$objects = scandir($dir); 
		foreach ($objects as $object) { 
			if ($object != "." && $object != ".." && $object != '.svn') {
				if ( !is_writable($dir."/".$object) ) {
					throw new Exception("$dir/$object not writable");
				}
				if (filetype($dir."/".$object) == "dir") {
					_checkWritable($dir."/".$object);
				}
			} 
		}
   		reset($objects); 
	}
}

$requiredFiles = array();
$unlinkFiles = array();
$errors = array();
$validVersionUpdate = array();

// this config file must fill $requiredFiles and $unlinkFiles
require_once dirname(__FILE__).'/update.config.php';

$requiredFiles[] = 'update.php';
$requiredFiles[] = 'update.config.php';
$requiredFiles[] = 'update.zip';
$requiredFiles[] = 'pclzip.php';

$unlinkFiles[] = 'update.zip';
$unlinkFiles[] = 'pclzip.php';
$unlinkFiles[] = 'update.php';
$unlinkFiles[] = 'update.config.php';


// Initialize the application path and autoloading
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));
set_include_path(implode(PATH_SEPARATOR, array(
    APPLICATION_PATH . '/../library',
    get_include_path(),
)));
require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance();

defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', 'production');

 
// Initialize Zend_Application
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

if ( !is_writable(APPLICATION_PATH . '/../') ) $errors[] = 'VLCShares main directory must be writable';
try {
	_checkWritable(APPLICATION_PATH . '/../');
} catch ( Exception $e) {
	$errors[] = $e->getMessage();
}

// I have to search for current vlc-shares version before change it
// so, i include the X_VlcShares class to check for the version string
$currentVersion = false;
if ( file_exists(APPLICATION_PATH . '/../library/X/VlcShares.php') ) {
	@include (APPLICATION_PATH . '/../library/X/VlcShares.php');
	if ( class_exists('X_VlcShares') ) {
		$currentVersion = X_VlcShares::VERSION;
	} else {
		$errors[] = "Current version not found: X_VlcShares class missing";
	}
} else {
	$errors[] = "Current version not found: X/VlcShares.php missing";
}

if ( array_search($currentVersion, $validVersionUpdate) === false ) {
	// invalid update for the current version
	$errors[] = "This update is not applicable to the current version";
}

if ( $currentVersion !== false ) {
	$specificFile = "update.$currentVersion.sqlite.sql";
	if ( file_exists(dirname(__FILE__)."/$specificFile") ) {
		$specificFile = dirname(__FILE__)."/$specificFile";
	} else {
		$specificFile = false;
	}
}

foreach ($requiredFiles as $file) {
	if ( !file_exists(dirname(__FILE__)."/$file") ) {
		$errors[] = "Required file '$file' not found!!!";
	}
}


// If there is no update action
// i render the main view
// and the ?action=update link
$action = @$_GET['action'];

switch ($action) {
	case 'update':
		if ( function_exists('__update_pre') && !__update_pre($currentVersion) ) {
			// fail fail fail, stop everything
			break;
		}
		
		require_once(realpath(dirname(__FILE__).'/pclzip.php'));
		
		$pclzip = new PclZip(realpath(dirname(__FILE__).'/update.zip'));
		$pclzip->extract(PCLZIP_OPT_PATH, APPLICATION_PATH . '/../', PCLZIP_OPT_REPLACE_NEWER);
		
		echo 'VLCShares files updated<br/>';
		
		// Initialize and retrieve DB resource
		$bootstrap = $application->getBootstrap();
		$bootstrap->bootstrap('db');
		$dbAdapter = $bootstrap->getResource('db');
		 
		try {
			if ( $specificFile !== false ) {
				$dataSql = file_get_contents($specificFile);
				if ( trim($dataSql) != '' ) {
					// use the connection directly to load sql in batches
					$dbAdapter->getConnection()->exec($dataSql);
					echo "Database updated from version $currentVersion</br>";
				}
			}
		} catch (Exception $e) {
		    echo 'AN ERROR HAS OCCURED:' . $e->getMessage() . '<br/>';
		}
		if ( function_exists('__update_post') && !__update_post($currentVersion) ) {
			// fail fail fail, stop unlink
			break;
		}
		// NO BREAK, after update I have to delete file
		
	case 'delete':
		// time to unlink all files
		foreach ($unlinkFiles as $file) {
			if ( !@unlink(dirname(__FILE__)."/$file") ) {
				echo "File not deleted: <b>'$file'</b>. Please, delete it manually!!!";
			} else { 
				echo "File <b>'$file'</b> deleted<br/>";
			}
		}
		break;
	default:
		view_Main($currentVersion, $specificFile, UPDATESCRIPT_VERSION, $validVersionUpdate ,$errors);
		break;
	
}


/**
 * Show the main menu
 * @param boolean|string $currentVersion current version or false
 * @param boolean|string $updateSqlFile sql update file or false
 * @param string $scriptVersion
 * @param array $validVersionUpdate array of valid start version for this update
 * @param array $errors array of 
 */
function view_Main($currentVersion, $updateSqlFile, $scriptVersion, $validVersionUpdate, $errors) {
?>	
<html>
<head>
	<title>VLCShares update script</title>
	<style>
	#container {
		padding: 30px;
		margin: 50px;
		border: 1px solid darkgray;
		background-color: lightgray;
	}
	h1 {
		font-shadow: 0 1px 1px rgb(0,0,0,0.9);
	}
	dt {
		font-weight: bold;
	}
	#errors {
		margin-left: 30px;
		border: 1px solid darkgray;
		padding: 10px;
		background-color: gray;
	}
	#errors p {
		font-weight: bold;
	}
	#errors .error {
		padding: 5px;
		background-color: #900;
		color: white;
		font-weight: bold;
		margin-bottom: 3px;
	}
	#links ul {
		list-style-type: square;
	}
	#links li {
		display: inline-block;
		padding: 15px;
		border: 1px solid black;
	}
	#links li a {
		color: white;
		font-weight: bold;
	}
	#links li.red {
		background-color: #900;
	}
	#links li.blue {
		background-color: blue;
	}
	#links li.yellow {
		background-color: brown;
	}
	</style>
</head>
<body>
	<div id="container">
		<h1>VLCShares update script</h1>
		<div id="content">
			<dl>
				<dt>Update script version:</dt>
				<dd><?php echo $scriptVersion ?></dd>
				<dt>This update is applicable to versions:</dt>
				<dd><?php echo implode(', ', $validVersionUpdate); ?></dd>
				<dt>Current version found:</dt>
				<dd><?php echo $currentVersion !== false ? $currentVersion : '<span class="warning">Version not found</span>'; ?></dd>
				<?php if ( $updateSqlFile !== false ) : ?>
					<dt>DB Update script selected:</dt>
					<dd><?php echo $updateSqlFile; ?></dd>
				<?php endif; ?>
			</dl>
		</div>
		<?php if ( count($errors) > 0 ): ?>
			<div id="errors">
				<p>The script has found some error. You have to fix these errors if you want to update</p>
				<?php foreach ($errors as $error): ?>
					<div class="error"><?php echo $error; ?></div>
				<?php endforeach; ?>
			</div>
			<div id="links">
				<ul>
					<li class="yellow">
						<a href="update.php">Re-check</a>
					</li>
				</ul>
			</div>
		<?php else: ?>
			<div id="links">
				<h3>This is your last chance. After this, there is no turning back.</h3>
				<ul>
					<li class="blue">
						<a href="update.php?action=delete">You take the blue pill: delete all update files without update</a>
					</li>
					<li class="red">
						<a href="update.php?action=delete">You take the red pill: update vlc-shares</a>
					</li>
				</ul>
			</div>
		<?php endif;?>
	</div>
</body>
</html>
<?php 
}

