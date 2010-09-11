<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/Plx.php';
require_once 'X/Plx/Item.php';
require_once 'Zend/Config.php';

class X_VlcShares_Plugins_PlxToHtml extends X_VlcShares_Plugins_Abstract {

	/**
	 * 
	 * @var Zend_Config
	 */
	private $options;
	
	private $_registeredEvents = array(
		X_VlcShares::TRG_ENDPAGES_OUTPUT_FILTER_PLX => 'getHtml',
		X_VlcShares::TRG_MANAGE_PLUGINS_LINKS => 'getActionLink'
	);
	
	public function __construct(Zend_Config $options) {
		$this->options = $options;
		$this->id = $this->options->id;
		$this->registerEvents($this->_registeredEvents);
	}	
	
	public function getActionLink() {
		return array(
			'actionName' => X_Env::_('plxtohtml_action_name'),
			'actionDesc' => X_Env::_('plxtohtml_action_desc'),
			'actionLink' => X_Env::routeLink('index','collections') 
		);
	}
	public function getHtml(X_Plx $plx) {
		
		if ( $this->options->get('notWiimcOnly', true) ) {
			if ( strpos($_SERVER['HTTP_USER_AGENT'], 'WiiMC') !== false ) {
				return;
			}
		}
		$showRaw = $this->options->get('showRaw', false);
		$plxItems = $plx->getItems();
		$output = include(dirname(__FILE__).'/PlxToHtml.phtml');
		
		return $output;
		
	}
}
