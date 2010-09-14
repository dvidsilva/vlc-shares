<?php

require_once 'Zend/Config.php';
require_once 'Zend/Controller/Front.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/VlcShares/Plugins.php';

class X_Env {
	
	const EXECUTE_OUT_NONE = 0;
	const EXECUTE_OUT_LASTLINE = 1;
	const EXECUTE_OUT_ARRAY = 2;
	const EXECUTE_OUT_IMPLODED = 3;
	const EXECUTE_PS_WAIT = 0;
	const EXECUTE_PS_BACKGROUND = 1;
	
	private static $_isWindows = null;
	private static $_psExec = null;
	private static $_debug = null;
	private static $_pluginsEvents = array();
	private static $_translator = null;
	private static $_forcedPort = '80';
	
	static public function initForcedPort($port) {
		self::$_forcedPort = $port;
		X_Debug::i("Apache port is: $port");
	}
	
	static public function isWindows() {
		if ( is_null(self::$_isWindows) ) {
			self::$_isWindows = ( array_key_exists('WINDIR', $_SERVER) || array_key_exists('windir', $_SERVER));
		}
		return self::$_isWindows;
	}
	
	
	static public function execute($command, $outputType = self::EXECUTE_OUT_LASTLINE, $spawnType = self::EXECUTE_PS_WAIT) {
		
		$output = array();
		$lastLine = '';
		if ( $spawnType == self::EXECUTE_PS_BACKGROUND ) {
			if ( self::isWindows() ) {
				// su windows leggo quanto tornato perche puo servire per il pid
				$command = self::_psExec().$command;
			} else {
				// su linux ignoro semplicemente l'output
				$command = trim($command).' > /dev/null 2>&1';
			}
		}
		self::debug(__METHOD__.": executing $command");
		$lastLine = exec($command, $output);
		
		switch ($outputType) {
			case self::EXECUTE_OUT_NONE: 
				return;
			
			case self::EXECUTE_OUT_LASTLINE:
				return $lastLine;
				
			case self::EXECUTE_OUT_IMPLODED:
				return implode('', $output);
				
			case self::EXECUTE_OUT_ARRAY:
			default:
				return $output;
		}
	}
	
	static private function _psExec() {
		if ( is_null(self::$_psExec) ) {
			self::$_psExec = '"'.dirname(__FILE__).'/PsExec.exe" -d ';
		}
		return self::$_psExec;
	}
	
	/**
	 * Init debug system
	 * @deprecated
	 * @see X_Debug::init()
	 * @param string $path
	 */
	static public function initDebug($path) {
		X_Debug::init($path);
	}

	/**
	 * Add a new message in debug log
	 * @deprecated
	 * @see X_Debug::i()
	 * @param string $message
	 */
	static public function debug($message) {
		X_Debug::i($message, 2);
	}
	

	/**
	 * @deprecated
	 * @param Zend_Config $options
	 */
	static public function initPlugins(Zend_Config $options) {
		return X_VlcShares_Plugins::init($options);
	}
	
	/**
	 * @deprecated
	 * @param unknown_type $eventName
	 * @param X_VlcShares_Plugins_Abstract $plugin
	 * @param unknown_type $methodName
	 */
	static public function registerPlugin($eventName, X_VlcShares_Plugins_Abstract $plugin, $methodName) {
		return X_VlcShares_Plugins::register($eventName, $plugin, $methodName);
	}
	
	/**
	 * @deprecated
	 * @param unknown_type $eventName
	 * @param unknown_type $pluginId
	 */
	static public function unregisterPlugin($eventName, $pluginId) {
		return X_VlcShares_Plugins::unregisterPlugin($eventName, $pluginId);
	}
	
	/**
	 * @deprecated
	 * @param unknown_type $eventName
	 */
	static public function unregisterAll($eventName = null) {
		return X_VlcShares_Plugins::unregisterAll($eventName);
	}

	/**
	 * @deprecated
	 * @param unknown_type $eventName
	 * @param unknown_type $args
	 */
	static public function triggerEvent($eventName, &$args = array()) {
		return X_VlcShares_Plugins::trigger($eventName, &$args);
	}
	
	static public function routeLink($controller = 'index', $action = 'index', $params = array()) {
		$link = 'http://';
		$link .= $_SERVER['HTTP_HOST'];
		if ( self::$_forcedPort != '80' || $_SERVER['SERVER_PORT'] != '80'  ) {
			if ( strpos($_SERVER["HTTP_HOST"], ':') === false ) {
				$link .= ':'.($_SERVER["SERVER_PORT"] == '80' ? self::$_forcedPort : $_SERVER["SERVER_PORT"]);
			}
		}
		//$link .= '/'.;
		$link .= Zend_Controller_Front::getInstance()->getBaseUrl() . "/$controller/$action";
		foreach ($params as $key => $value) {
			$link.= "/$key/$value";
		}
		return $link;
	}
	
	
	static public function initTranslator(Zend_Translate $translator) {
		if ( is_null(self::$_translator) ) {
			self::$_translator = $translator;
			X_Debug::i("Translator enabled");
		}
	}
	
	static public function _($message) {
		if ( !is_null(self::$_translator) ) {
			return self::$_translator->_($message);
		}
	}
	
	static public function formatTime($seconds) {
		return floor($seconds/3600) . ':' . str_pad((floor($seconds/60) % 60), 2, "0", STR_PAD_LEFT) . ":" . str_pad($seconds % 60, 2, "0", STR_PAD_LEFT);
	} 
	
}
