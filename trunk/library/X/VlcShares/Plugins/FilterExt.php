<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/Plx.php';
require_once 'X/Plx/Item.php';
require_once 'Zend/Config.php';

class X_VlcShares_Plugins_FilterExt extends X_VlcShares_Plugins_Abstract {

	/**
	 * 
	 * @var Zend_Config
	 */
	private $options;
	private $extensions;
	
	private $_registeredEvents = array(
		X_VlcShares::TRG_DIR_TRAVERSAL	=>	'checkEntry',
	);
	
	public function __construct(Zend_Config $options) {
		$this->options = $options;
		$this->id = $this->options->id;
		$this->extensions = explode('|', $this->options->list);
		$this->registerEvents($this->_registeredEvents);
	}	
	
	/**
	 * 
	 * @param DirectoryIterator $entry
	 */
	public function checkEntry($entry) {
		if ( $entry->isFile() ) {
			if ( array_search(pathinfo($entry->getFilename(), PATHINFO_EXTENSION), $this->extensions ) !== false ) {
				return true;
			} else {
				return false;
			}
		}
		return true;
	}
}	

