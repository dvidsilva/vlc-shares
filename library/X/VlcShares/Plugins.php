<?php 

require_once 'X/VlcShares.php';
require_once 'X/Env.php';
require_once 'Zend/Config.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/VlcShares/Plugins/Helper/Broker.php';

final class X_VlcShares_Plugins {
	
	static private $_plugins = array();
	
	/**
	 * 
	 * @var X_VlcShares_Plugins_Helper_Broker
	 */
	static private $_helperBroker = null;
	
	/**
	 * 
	 * @var X_VlcShares_Plugins_Broker
	 */
	static private $_pluginBroker = null;
	
	static public function init($options, $helpersOptions = array()) {
		
		// plugins are registered in plugin broker
		self::$_pluginBroker = new X_VlcShares_Plugins_Broker();
		
		if ( !($options instanceof Zend_Config) ) {
			if ( !is_array($options) ) {
				$options = array();
			}
			$options = new Zend_Config($options);
		}

		if ( !($helpersOptions instanceof Zend_Config) ) {
			if ( !is_array($helpersOptions) ) {
				$helpersOptions = array();
			}
			$helpersOptions = new Zend_Config($helpersOptions);
		}
		
		
		$plugins = Application_Model_PluginsMapper::i()->fetchAll();
		
		//foreach ($options as $o_k => $o_v ) {
		// 	$pValue = $o_v->toArray();
		//	$pKey = $o_k;
				
		foreach ($plugins as $plugin ) {
			/* @var $plugin Application_Model_Plugin */
			
			if ( !$plugin->isEnabled() && $plugin->getType() != Application_Model_Plugin::SYSTEM) continue;
			
			$pKey = $plugin->getKey(); 
			
			try {
				if ( $options->$pKey ) {
					$pValue = $options->$pKey->toArray();
				}
			} catch (Exception $e ) { 
				// no configs
				$pValue = array();
			}
			$pValue['class'] = $plugin->getClass();
			if ( $plugin->getFile() != null ) {
				$pValue['path'] = APPLICATION_PATH . "/../library/".$plugin->getFile();
			}
			
			$className = $pValue['class'];
			$path = @$pValue['path'];
			// se class non e' settato, il plugin nn e' valido
			if ( !$className ) {
				continue;
			}
			if ( $path && substr($path, -4) == '.php' && file_exists($path) ) {
				require_once $path;
			}
			if ( class_exists($className) && is_subclass_of($className, 'X_VlcShares_Plugins_Abstract')) {
				$pValue['id'] = $pKey;
				// si auto referenzia 
				//new $className(new Zend_Config($pValue));
				// plugins system from
				//	event-based -> function-based
				$plugin = new $className();
				$plugin->setConfigs(new Zend_Config($pValue));
				self::$_pluginBroker->registerPlugin($pKey, $plugin );
			}
		}
		
		self::$_helperBroker = new X_VlcShares_Plugins_Helper_Broker($helpersOptions);
		X_Debug::i("Plugin system enabled");
		
		self::$_pluginBroker->gen_afterPluginsInitialized(self::$_pluginBroker);
		
	}
	
	
	static public function register($eventName, X_VlcShares_Plugins_Abstract $plugin, $methodName) {
		self::$_plugins[$eventName][$plugin->getId()] = array('obj'=>$plugin, 'method'=>$methodName);
	}
	
	static public function unregisterPlugin($eventName, $pluginId) {
		unset(self::$_plugins[$eventName][$pluginId]);
	}
	
	static public function unregisterAll($eventName = null) {
		if ( is_null($eventName) ) {
			self::$_plugins = array();
		} else {
			unset(self::$_plugins[$eventName]);
		}
	}

	static public function trigger($eventName, &$args = array()) {
		$return = array();
		if ( array_key_exists($eventName, self::$_plugins) ) {
			foreach (self::$_plugins[$eventName] as $id => $plugin) {
				$return[] = $plugin['obj']->$plugin['method']($args);
			}
		}
		return $return;
	}
	
	
	static public function loadStatic($pluginClass, $includePath = '') {
		if ( !class_exists($pluginClass) ) {
			if ( $includePath != '' ) {
				@include_once $includePath;
			} else {
				// try my personal autoload
				$path = str_replace('_','/', $pluginClass);
				@include_once $path;
			}
		}
		return class_exists($pluginClass);
	}
	
	/**
	 * Get the helper broker or an helper
	 * @param string $helperName
	 * @return X_VlcShares_Plugins_Helper_Broker|X_VlcShares_Plugins_Helper_Abstract
	 * @throws Exception if $helperName is specified and helper is not registred
	 */
	static public function helpers($helperName = null) {
		if ( $helperName == null) {
			return self::$_helperBroker;
		} else {
			return self::$_helperBroker->helper($helperName);
		}
	}
	
	/**
	 * Get the plugin broker 
	 * @return X_VlcShares_Plugins_Broker
	 */
	static public function broker() {
		return self::$_pluginBroker;
	}
}


