<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/Plx.php';
require_once 'X/Plx/Item.php';
require_once 'Zend/Config.php';
require_once 'Zend/Controller/Request/Abstract.php';

class X_VlcShares_Plugins_Subs extends X_VlcShares_Plugins_Abstract {

	/**
	 * 
	 * @var Zend_Config
	 */
	private $options;
	private $subs;
	
	private $_registeredEvents = array(
		X_VlcShares::TRG_MODE_ADDITIONALS => 'getSubs',
		X_VlcShares::TRG_VLC_ARGS_SUBTITUTE => 'getSubstitution'
	);
	
	public function __construct(Zend_Config $options) {
		$this->options = $options;
		$this->id = $this->options->id;
		$this->subs = explode('|', $this->options->list);
		$this->registerEvents($this->_registeredEvents);
	}	
	
	public function getSubs($args = array()) {
		$filePath = $args[0];
		$dirPath = $args[1];
		$filename = $args[2];
		
		$dir = new DirectoryIterator($dirPath);
		$subsFound = array();
		foreach ($dir as $entry) {
			if ( $entry->isFile() ) {
				// se e' un file sub valido
				if ( array_search(pathinfo($entry->getFilename(), PATHINFO_EXTENSION), $this->subs ) !== false ) {
					// stessa parte iniziale
					if ( substr($entry->getFilename(),0,strlen($filename)) == $filename ) {
						$subName = substr($entry->getFilename(), strlen($filename));
						$subsFound['Sub: '.$subName] = array(
							'plg:'.$this->getId() => base64_encode($subName)
						);
					}
				}
			}
		}
		return $subsFound;
		
	}
	
	public function getSubstitution($argv = array()) {
		
		/**
		 * @var Zend_Controller_Request_Abstract
		 */
		$request = $argv[0];
		$filePath = $argv[1];
		$dirPath = $argv[2];
		$filename = $argv[3];
		
		$subid = base64_decode($request->getParam("plg:{$this->getId()}"));
		if ( $subid !== false && $subid != '') {
			$subfilename = $filename.$subid;
			return array('subtitles' => "--sub-file=\"$dirPath/$subfilename\"");
		} else {
			return array();
		}
	}
}
