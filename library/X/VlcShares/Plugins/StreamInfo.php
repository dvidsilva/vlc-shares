<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/Vlc.php';
require_once 'X/Plx.php';
require_once 'X/Plx/Item.php';
require_once 'Zend/Config.php';

class X_VlcShares_Plugins_StreamInfo extends X_VlcShares_Plugins_Abstract {

	/**
	 * 
	 * @var Zend_Config
	 */
	private $options;
	
	private $_registeredEvents = array(
		X_VlcShares::TRG_CONTROLS_MENU_PRE => 'getPreInfo',
	);
	
	public function __construct(Zend_Config $options) {
		$this->options = $options;
		$this->id = $this->options->id;
		$this->registerEvents($this->_registeredEvents);
	}	
	
	public function getPreInfo(X_Vlc $vlc ) {
		$showTitle = $this->options->get('showTitle', false);
		$showCurrentTime = $this->options->get('showCurrentTime', false);
		$showTotalTime = $this->options->get('showTotalTime', false);
		
		$items = array();
		
		if ( $showTitle ) {
			$name = $vlc->getCurrentName();
			$items[] = new X_Plx_Item(X_Env::_('on_air').": $name", X_Env::routeLink('controls', 'control'));
		}
		if ( $showCurrentTime && $showTotalTime ) {
			$currentTime = X_Env::formatTime($vlc->getCurrentTime());
			$totalTime = X_Env::formatTime($vlc->getTotalTime());
			$items[] = new X_Plx_Item("$currentTime/$totalTime", X_Env::routeLink('controls', 'control'));
		} else {
			if ( $showCurrentTime ) {
				$currentTime = X_Env::formatTime($vlc->getCurrentTime());
				$items[] = new X_Plx_Item(X_Env::_('current_time').": $currentTime", X_Env::routeLink('controls', 'control'));
			}
			if ( $showTotalTime ) {
				$totalTime = X_Env::formatTime($vlc->getTotalTime());
				$items[] = new X_Plx_Item(X_Env::_('total_length').": $totalTime", X_Env::routeLink('controls', 'control'));
			}
		}
		return $items;
	}
	 
}
