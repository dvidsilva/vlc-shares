<?php

require_once ('X/VlcShares/Plugins/Abstract.php');

class X_VlcShares_Plugins_Broker /*extends X_VlcShares_Plugins_Abstract*/ {

	/**
	 * List of plugins registered
	 * @var array of X_VlcShares_Plugins_Abstract
	 */
	private $plugins = array(); 
	
	private $backlistedFunctions = array(
		'getId', 'helpers', 'setConfigs', 'setPriorities'
	);
	
	public function registerPlugin($pluginId, X_VlcShares_Plugins_Abstract  $pluginObj) {
		$this->plugins[$pluginId] = $pluginObj;
		X_Debug::i("Plugin registered: $pluginId");
	}
	
	/**
	 * Unregister all plugin of class $pluginClass
	 * @param string $pluginClass
	 */
	public function unregisterPluginClass($pluginClass) {
		foreach ($this->plugins as $pluginId => $pluginObj ) {
			if ( $pluginObj instanceof $pluginClass ) {
				// unset the plugins entry if pluginClass = class of pluginObj
				unset($this->plugins[$pluginId]);
				X_Debug::i("Unregistered plugin by class: $pluginId => $pluginClass");
			}
		}
	}
	
	/**
	 * Unregister plugin with id $pluginId
	 * @param string $pluginId
	 */
	public function unregisterPluginId($pluginId) {
		if ( array_key_exists($pluginId, $this->plugins)) {
			unset($this->plugins[$pluginId]);
			X_Debug::i("Unregistered plugin by id: $pluginId");
		}
	}

	/**
	 * Unregister all plugins
	 */
	public function unregisterAll() {
		$this->plugins = array();
		X_Debug::i("Unregistered all plugins");
	}
	
	public function getPlugins() {
		return $this->plugins;
	}
	
	function __call($funcName, $funcParams) {
		if ( method_exists('X_VlcShares_Plugins_Abstract', $funcName) && !in_array($funcName, $this->backlistedFunctions) ) {
			$toBeCalled = array();
			foreach ($this->plugins as $pluginId => $pluginObj) {
				/* @var $pluginObj X_VlcShares_Plugins_Abstract */
				$priority = $pluginObj->getPriority($funcName);
				if ( $priority !== -1 ) {
					$toBeCalled[$priority][$pluginId] = $pluginObj;
				}
			}
			$returnedVal = array();
			ksort($toBeCalled);
			foreach ( $toBeCalled as $priorityStack ) {
				foreach ( $priorityStack as $pluginId => $pluginObj ) {
					/* @var $pluginObj X_VlcShares_Plugins_Abstract */
					$return = call_user_func_array(array($pluginObj, $funcName), $funcParams);
					if ( $return !== null ) {
						//$returnedVal[$pluginId] = $return;
						if ( is_array($return) ) {
							// TODO
							// check for key relocation
							$returnedVal = array_merge($returnedVal, $return);
						} else {
							$returnedVal[] = $return;
						}
					}
				}
			}
			return $returnedVal;
		} else {
			X_Debug::f("Invalid trigger: $funcName");
			throw new Exception('Invalid trigger');
		}
	}
}

?>