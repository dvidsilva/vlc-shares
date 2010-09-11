<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/Plx.php';
require_once 'X/Plx/Item.php';
require_once 'Zend/Config.php';

class X_VlcShares_Plugins_HideHidden extends X_VlcShares_Plugins_Abstract {

	/**
	 * 
	 * @var Zend_Config
	 */
	private $options;
	
	private $_registeredEvents = array(
		X_VlcShares::TRG_DIR_TRAVERSAL	=>	'checkEntry',
	);
	
	public function __construct(Zend_Config $options) {
		$this->options = $options;
		$this->id = $this->options->id;
		$this->registerEvents($this->_registeredEvents);
	}	
	
	/**
	 * 
	 * @param DirectoryIterator $entry
	 */
	public function checkEntry($entry) {
		if ( X_Env::isWindows() ) {
			return !$this->isHiddenOnWindows($entry->getRealPath());
		} else {
			if ( substr($entry->getFilename(),0,1) == '.') {
				return false;
			}
		}
		return true;
	}
	
	private function isHiddenOnWindows($fileName) {
	    $attr = trim(exec('FOR %A IN ("'.$fileName.'") DO @ECHO %~aA'));
	
	    if($attr[3] === 'h')
	        return true;
	
	    return false;
	}
	
}
