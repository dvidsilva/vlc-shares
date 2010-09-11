<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/Plx.php';
require_once 'X/Plx/Item.php';
require_once 'Zend/Config.php';

class X_VlcShares_Plugins_StaticLinks extends X_VlcShares_Plugins_Abstract {

	/**
	 * 
	 * @var Zend_Config
	 */
	private $options;
	
	private $_registeredEvents = array(
		X_VlcShares::TRG_PROFILES_TRAVERSAL_PRE => 'getPreProfilesLinks',
		X_VlcShares::TRG_PROFILES_TRAVERSAL_POST => 'getPostProfilesLinks',
		X_VlcShares::TRG_DIR_TRAVERSAL_PRE	=>	'getPreLinks',
		X_VlcShares::TRG_DIR_TRAVERSAL_POST		=>	'getPostLinks'
	);
	
	public function __construct(Zend_Config $options) {
		$this->options = $options;
		$this->id = $this->options->id;
		$this->registerEvents($this->_registeredEvents);
	}	
	
	public function getPreLinks($args = array()) {
		$links = $this->options->get('pre', null);
		if ( !is_null($links)) {
			return $this->generateLinks($links->toArray());
		} else {
			return array();
		}
	}
	 
	public function getPostLinks($args = array()) {
		$links = $this->options->get('post', null);
		if ( !is_null($links)) {
			return $this->generateLinks($links->toArray());
		} else {
			return array();
		}
	}
	
	public function getPreProfilesLinks($args = array()) {
		$links = $this->options->get('preProfiles', null);
		if ( !is_null($links)) {
			return $this->generateLinks($links->toArray());
		} else {
			return array();
		}
	}
	 
	public function getPostProfilesLinks($args = array()) {
		$links = $this->options->get('postProfiles', null);
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
