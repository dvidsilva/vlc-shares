<?php 

require_once 'X/VlcShares.php';
require_once 'X/Env.php';
require_once 'Zend/Config.php';

final class X_VlcShares_Plugins {
	
	
	static private $_plugins = array();
	
	static public function init($options) {
		
		if ( !($options instanceof Zend_Config) ) {
			if ( !is_array($options) ) {
				$options = array();
			}
			$options = new Zend_Config($options);
		}

		foreach ($options as $o_k => $o_v ) {
			$pValue = $o_v->toArray();
			$pKey = $o_k;
			
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
				new $className(new Zend_Config($pValue));
			}
		}
		
		X_Debug::i("Plugin system enabled");
		
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
	
	
	
}


