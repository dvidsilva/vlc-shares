<?php

require_once ('X/VlcShares/Plugins/Abstract.php');
require_once 'X/Page/ItemList.php';

class X_VlcShares_Plugins_Broker /*extends X_VlcShares_Plugins_Abstract*/ {

	/**
	 * List of plugins registered
	 * @var array of X_VlcShares_Plugins_Abstract
	 */
	private $plugins = array(); 
	
	private $backlistedFunctions = array(
		'getId', 'helpers', 'setConfigs', 'setPriorities'
	);
	
	public function registerPlugin($pluginId, X_VlcShares_Plugins_Abstract  $pluginObj, $silent = false) {
		$this->plugins[$pluginId] = $pluginObj;
		if ( !$silent )	X_Debug::i("Plugin registered: $pluginId");
	}
	
	public function isRegistered($pluginId) {
		return array_key_exists($pluginId, $this->plugins);
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
	
	/**
	 * Get all registered plugin id $pluginId is null
	 * or a single plugin by $pluginId if registered
	 * @param string|null $pluginId
	 * @return X_VlcShares_Plugins_Abstract|array of X_VlcShares_Plugins_Abstract
	 * @throws Exception if invalid $pluginId submitted
	 */
	public function getPlugins($pluginId = null) {
		if ( $pluginId !== null ) {
			if (!$this->isRegistered($pluginId)) {
				throw new Exception("PluginId $pluginId isn't registered");
			} else {
				return $this->plugins[$pluginId];
			}
		} else {
			return $this->plugins;
		}
	}
	
	/**
	 * Get the class of a pluginId
	 */
	public function getPluginClass($pluginId) {
		if ( !$this->isRegistered($pluginId) ) {
			throw new Exception("PluginId $pluginId isn't registered");
		}
		return get_class($this->plugins[$pluginId]);
	}
	
	/**
	 * Forward this trigger to all registered plugins
	 * I have to manually specify this for reference passing
	 * @param array &$items array of X_Page_Item_PItem
	 * @param string $provider id of the plugin the handle the request
	 * @param Zend_Controller_Action $controller
	 */
	public function orderShareItems(&$items, $provider, Zend_Controller_Action $controller) {
		$funcName = __FUNCTION__;
		$toBeCalled = array();
		foreach ($this->plugins as $pluginId => $pluginObj) {
			/* @var $pluginObj X_VlcShares_Plugins_Abstract */
			$priority = $pluginObj->getPriority($funcName);
			if ( $priority !== -1 ) {
				$toBeCalled[$priority][$pluginId] = $pluginObj;
			}
		}
		$returnedVal = null;
		ksort($toBeCalled);
		foreach ( $toBeCalled as $priorityStack ) {
			foreach ( $priorityStack as $pluginId => $pluginObj ) {
				/* @var $pluginObj X_VlcShares_Plugins_Abstract */
				//X_Debug::i("Calling ".get_class($pluginObj)."::$funcName"); // for problem, uncomment this
				//$return = call_user_func_array(array($pluginObj, $funcName), $funcParams);
				$return = $pluginObj->$funcName($items, $provider, $controller);
				if ( $return !== null ) {
					//$returnedVal[$pluginId] = $return;
					if ( $return instanceof X_Page_ItemList ) {
						if ( $returnedVal == null ) {
							$returnedVal = $return;
						} else {
							$returnedVal->merge($return);
						}
					} else {
						if ( $returnedVal == null) {
							$returnedVal = array($pluginId => $return);
						} elseif ( is_array($returnedVal) ) {
							$returnedVal[$pluginId] = $return;
						}
					}
				}
			}
		}
		return $returnedVal;
	}
	
	//{{{ redirect new stream api to old one (ONLY IF VLC)
	public function preRegisterStreamerArgs(X_Streamer_Engine $engine, $url, $provider, $location, Zend_Controller_Action $controller) {
		if ( $engine instanceof X_Streamer_Engine_Vlc ) {
			return $this->preRegisterVlcArgs(X_Vlc::getLastInstance(), $provider, $location, $controller);
		} else {
			return $this->__call(__FUNCTION__, func_get_args());
		}
	}

	public function registerStreamerArgs(X_Streamer_Engine $engine, $url, $provider, $location, Zend_Controller_Action $controller) {
		if ( $engine instanceof X_Streamer_Engine_Vlc ) {
			return $this->registerVlcArgs(X_Vlc::getLastInstance(), $provider, $location, $controller);
		} else {
			return $this->__call(__FUNCTION__, func_get_args());
		}
	}
	
	public function postStreamerArgs(X_Streamer_Engine $engine, $url, $provider, $location, Zend_Controller_Action $controller) {
		if ( $engine instanceof X_Streamer_Engine_Vlc ) {
			return $this->postRegisterVlcArgs(X_Vlc::getLastInstance(), $provider, $location, $controller);
		} else {
			return $this->__call(__FUNCTION__, func_get_args());
		}
	}
	
	public function preStartStreamer(X_Streamer_Engine $engine, $url, $provider, $location, Zend_Controller_Action $controller) {
		if ( $engine instanceof X_Streamer_Engine_Vlc ) {
			return $this->preSpawnVlc(X_Vlc::getLastInstance(), $provider, $location, $controller);
		} else {
			return $this->__call(__FUNCTION__, func_get_args());
		}
	}	

	public function postStartStreamer($started, X_Streamer_Engine $engine, $url, $provider, $location, Zend_Controller_Action $controller) {
		if ( $engine instanceof X_Streamer_Engine_Vlc ) {
			return $this->postSpawnVlc(X_Vlc::getLastInstance(), $provider, $location, $controller);
		} else {
			return $this->__call(__FUNCTION__, func_get_args());
		}
	}
	//}}}
	
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
			$returnedVal = null;
			ksort($toBeCalled);
			foreach ( $toBeCalled as $priorityStack ) {
				foreach ( $priorityStack as $pluginId => $pluginObj ) {
					/* @var $pluginObj X_VlcShares_Plugins_Abstract */
					//X_Debug::i("Calling ".get_class($pluginObj)."::$funcName"); // for problem, uncomment this
					$return = call_user_func_array(array($pluginObj, $funcName), $funcParams);
					if ( $return !== null ) {
						//$returnedVal[$pluginId] = $return;
						if ( $return instanceof X_Page_ItemList ) {
							if ( $returnedVal == null ) {
								$returnedVal = $return;
							} else {
								$returnedVal->merge($return);
							}
						} else {
							if ( $returnedVal == null) {
								$returnedVal = array($pluginId => $return);
							} elseif ( is_array($returnedVal) ) {
								$returnedVal[$pluginId] = $return;
							}
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

