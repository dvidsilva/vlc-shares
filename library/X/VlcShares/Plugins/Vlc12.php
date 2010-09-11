<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/Plx.php';
require_once 'X/Plx/Item.php';
require_once 'X/Vlc.php';
require_once 'Zend/Config.php';


class X_VlcShares_Plugins_Vlc12 extends X_VlcShares_Plugins_Abstract {

	/**
	 * 
	 * @var Zend_Config
	 */
	private $options;
	
	private $_registeredEvents = array(
		X_VlcShares::TRG_VLC_SPAWN_PRE	=>	'fixPaths',
	);
	
	public function __construct(Zend_Config $options) {
		$this->options = $options;
		$this->id = $this->options->id;
		$this->registerEvents($this->_registeredEvents);
		
	}

	/**
	 * 
	 * @param X_Vlc $vlc
	 */
	public function fixPaths($vlc) {
	
		$source = $vlc->getArg('source');
		
		if ( X_Env::isWindows() && substr($source,0,7) != 'http://' ) {
			$source = realpath(trim($source, '"'));
			$vlc->registerArg('source', "\"$source\"");
		}
		
		
	}
	
}
