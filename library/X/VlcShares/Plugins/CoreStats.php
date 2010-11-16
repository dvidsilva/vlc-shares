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
	 * Retrieve core statistics
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Statistic
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
		
		$stat = new X_Page_Item_Statistic($this->getId(), X_Env::_('p_corestats_statstitle'));
		$stat->setTitle(X_Env::_('p_corestats_statstitle'))
			->appendStat(X_Env::_('p_corestats_vlcrunning').": $vlc")
			->appendStat(X_Env::_('p_corestats_pluginnumber').": $plugins")
			->appendStat(X_Env::_('p_corestats_helpernumber').": $helpers")
			->appendStat(X_Env::_('p_corestats_pluginslist').": $pluginsList")
			->appendStat(X_Env::_('p_corestats_helperlist').": $helpersList");

		return new X_Page_ItemList_Statistic(array($stat));
	}
	
	
}
