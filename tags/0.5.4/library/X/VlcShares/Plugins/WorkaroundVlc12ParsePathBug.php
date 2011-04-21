<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/Plx.php';
require_once 'X/Plx/Item.php';
require_once 'X/Vlc.php';
require_once 'Zend/Config.php';

/**
 * This plugin workaround a "bug/feature" (???) in vlc 1.2 and vlc 1.1.3+
 * for Windows that prevent the use of mixed format paths for sources.
 * Until version 1.1.3 vlc accepts for source path:
 * 		- C:\This\Is\A\Normal\Windows\Env\Path\To\File <- w/o quotes
 * 		- "C:\This\Is\A\Normal\Windows\Env\Path\To\File" <- with quotes
 * 		- "C:/This/Is/An/Inverse/Path/To/File" <-- with quotes
 * 		- "C:\This\Is\A\Mixed/Path/To/File"	<-- with quotes
 * After version 1.1.3 and 1.2nightly, vlc accepta only:
 * 		- C:\This\Is\A\Normal\Windows\Env\Path\To\File <- w/o quotes
 * 		- "C:\This\Is\A\Normal\Windows\Env\Path\To\File" <- with quotes
 * So i need to change all path to normal with quotes
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_WorkaroundVlc12ParsePathBug extends X_VlcShares_Plugins_Abstract {

	public function __construct() {
		// this should be the last plugin to be executed
		$this->setPriority('preSpawnVlc', 100);
	}

	/**
	 * This hook can be used check vlc status just before
	 * spawn is called
	 * 
	 * @param X_Vlc $vlc vlc wrapper object
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to stream
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 */
	public function preSpawnVlc(X_Vlc $vlc, $provider, $location, Zend_Controller_Action $controller) {

		// TODO port to newer api when ready
		
		// when newer X_Vlc's api will be ready
		// i will need change this to
		// $source = $vlc->getSource();
		// and double quote removal will be automatic
		$source = $vlc->getArg('source');
		
		$provider = X_VlcShares_Plugins::broker()->getPlugins($provider);
		
		//if ( X_Env::isWindows() && !X_Env::startWith($source, 'http://') && !X_Env::startWith($source, 'https://') ) {
		if ( X_Env::isWindows() && is_a($provider, 'X_VlcShares_Plugins_FileSystem') ) {
			// with newer api this will be useless
			$source = realpath(trim($source, '"'));
			// when newer X_Vlc's api will be ready
			// i will need change this to
			// $vlc->setSource($source);
			// and double quotation will be automatic
			$vlc->registerArg('source', "\"$source\"");
			
			
		}
		
	}
	
}
