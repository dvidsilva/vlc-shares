<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/Plx.php';
require_once 'X/Plx/Item.php';
require_once 'X/Env.php';
require_once 'Zend/Config.php';

class X_VlcShares_Plugins_CustomCommand extends X_VlcShares_Plugins_Abstract {

	/**
	 * 
	 * @var Zend_Config
	 */
	private $options;
	
	private $_registeredEvents = array(
		X_VlcShares::TRG_CONTROLS_MENU_PRE => 'getPreCommands',
		X_VlcShares::TRG_CONTROLS_MENU_POST => 'getPostCommands'
	);
	
	public function __construct(Zend_Config $options) {
		$this->options = $options;
		$this->id = $this->options->id;
		$this->registerEvents($this->_registeredEvents);
	}	
	
	public function getPreCommands($args = array()) {
		$links = $this->options->get('pre', null);
		if ( !is_null($links)) {
			return $this->generateLinks($links->toArray());
		} else {
			return array();
		}
	}
	 
	public function getPostCommands($args = array()) {
		$links = $this->options->get('post', null);
		if ( !is_null($links)) {
			return $this->generateLinks($links->toArray());
		} else {
			return array();
		}
	}
	
	
	private function generateLinks($optionsArray) {
		
		$links = array();
		
		foreach ($optionsArray as $id => $params) {
			if ( !@$params['label'] )
				continue;
			if ( !(@$params['link'] || @$params['route']) )
				continue;
				
			$name = X_Env::_($params['label']);
			if ( @$params['route'] ) {
				$link = X_Env::routeLink(@$params['route']['controller'], @$params['route']['action'], (@$params['route']['args'] ? @$params['route']['args'] : array()));
			} else {
				$link = $params['link'];
			}
			
			$links[] = new X_Plx_Item($name, $link);
		}
		
		return $links;
	}
}
