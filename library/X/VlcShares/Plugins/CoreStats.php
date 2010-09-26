<?php

require_once 'X/VlcShares/Plugins/Abstract.php';


/**
 * This is a soc plugin:
 * It's an example of how to add stats in vlc dashboard
 * This plugin only produce stats for the dashboard
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_CoreStats extends X_VlcShares_Plugins_Abstract {
	
	public function __construct() {
		$this->setPriority('getIndexStatistics', 0); // i add it as first in the stack
	}
	
	/**
	 * Retrieve statistic from plugins
	 * @param Zend_Controller_Action $this
	 * @return array The format of the array should be:
	 * 		array(
	 * 			array(
	 * 				'title' => ITEM TITLE,
	 * 				'label' => ITEM LABEL,
	 * 				'stats' => array(INFO, INFO, INFO),
	 * 				'provider' => array('controller', 'index', array()) // if provider is setted, stats key is ignored 
	 * 			), ...
	 * 		)
	 */
	public function getIndexStatistics(Zend_Controller_Action $controller) {
		
		$plugins = count(X_VlcShares_Plugins::broker()->getPlugins());
		$helpers = count(X_VlcShares_Plugins::helpers()->getHelpers());
		
		$pluginsList = '<div class="scrollable" style="max-height: 75px;"><ol>';
		foreach (X_VlcShares_Plugins::broker()->getPlugins() as $pluginName => $pluginObj) {
			$formattedPluginClass = array_pop(explode('_',get_class($pluginObj)));
			$pluginsList .= "<li style=\"font-weight: normal;\">$formattedPluginClass</li>\n";
		}
		$pluginsList .= "</ol></div>";

		$helpersList = '<div class="scrollable" style="max-height: 75px;"><ol>';
		foreach (X_VlcShares_Plugins::helpers()->getHelpers() as $pluginName => $pluginObj) {
			$formattedPluginClass = array_pop(explode('_',get_class($pluginObj)));
			$helpersList .= "<li style=\"font-weight: normal;\">$formattedPluginClass</li>\n";
		}
		$helpersList .= "</ol></div>";
		
		$vlc = (X_Vlc::getLastInstance()->isRunning() ? X_Env::_('p_corestats_vlcrunning_yes') : X_Env::_('p_corestats_vlcrunning_no'));
		
		return array(
			array(
				'title'	=> X_Env::_('p_corestats_statstitle'),
				'label'	=> X_Env::_('p_corestats_statstitle'),
				'stats'	=>	array(
					X_Env::_('p_corestats_vlcrunning').": $vlc",
					X_Env::_('p_corestats_pluginnumber').": $plugins",
					X_Env::_('p_corestats_helpernumber').": $helpers",
					X_Env::_('p_corestats_pluginslist').": $pluginsList",
					X_Env::_('p_corestats_helperlist').": $helpersList",
				)
			)
		);
		
	}
	
	
}
