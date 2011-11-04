<?php 


// ============================================ //
// If you don't know what you are doing,
// don't touch this file
// ============================================ //

// check if db needs manipulation
foreach ($dbInits as $dbInit => $enabled) {
	if ( $enabled ) {
	    $schemaSql = file_get_contents($dbInit);
	    // use the connection directly to load sql in batches
	    $dbAdapter->getConnection()->exec($schemaSql);
	}
}

foreach ($neededDirectories as $dir) {
	if ( !file_exists($dir) ) {
		mkdir($dir, 0777, true);
	}
}

foreach ($neededLinks as $linkFrom => $linkTo) {
	if ( X_Env::isWindows() ) {
		// symlink doesn't work:
		// i need to copy the file everytime
		// and the old file will be overwritten
		copy($linkFrom, $linkTo);
	} else {
		symlink($linkFrom, $linkTo);
	}
}

// let's add models classes
foreach ($includeFiles as $file) {
	@include_once $file;
}

// let's add views path in view
foreach ($viewScriptsPath as $viewScriptPath) {
	$view->addScriptPath($viewScriptPath);
}

// include plugins files
foreach ($pluginsIncludes as $pluginInclude) {
	@include_once $pluginInclude;
}

// include helpers files
foreach ($helpersIncludes as $helperInclude) {
	@include_once $helperInclude;
}


// create plugins instance
foreach ($pluginsInstances as $pluginKey => $pluginInstance) {
	if ( !array_key_exists('class', $pluginInstance ) ) {
		continue;
	}
	$pluginClass = $pluginInstance['class'];

	$configA = array($pluginKey => array('id' => $pluginKey, 'class' => $pluginClass));
	if ( array_key_exists('configs', $pluginInstance ) ) {
		foreach ($pluginInstance['configs'] as $configKey => $configValue) {
			// uses the same code of normal bootstrap code 
			$key = $configKey;
			$_array = $configValue;
			$exploded = explode('.', $key);
			$_first = true;
			for ( $i = count($exploded) - 1; $i >= 0; $i--) {
				$_array = array($exploded[$i] => $_array);
			}
			$_array = array($pluginKey => $_array);
			$configA = array_merge_recursive($configA, $_array);
		}
	}
	$configs = new Zend_Config($configA);
	/* @var $pluginObj X_VlcShares_Plugins_Abstract */
	$pluginObj = new $pluginClass();
	
	$pluginObj->setConfigs(
		$configs->get($pluginKey, new Zend_Config(array()))
	);
	
	X_VlcShares_Plugins::broker()->registerPlugin($pluginKey, $pluginObj);
	
}

// unset variables to avoid collisions with others plugin
unset($basePath);
unset($dbAdapter);
unset($view);
unset($frontController);
unset($includeFiles);
unset($neededDirectories);
unset($neededLinks);
unset($viewScriptsPath);
unset($dbInits);
unset($pluginsInstances);
unset($helpersIncludes);
