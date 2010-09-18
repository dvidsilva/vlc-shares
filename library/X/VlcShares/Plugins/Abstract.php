<?php

require_once 'X/Env.php';
require_once 'Zend/Config.php';
require_once 'X/VlcShares/Plugins/Helper/Interface.php';
require_once 'X/VlcShares/Plugins/Helper/Abstract.php';


abstract class X_VlcShares_Plugins_Abstract {

	protected $id = '';
	
	/**
	 * Plugin config
	 * @var Zend_Config
	 */
	protected $configs;
	
	protected $priorities = array();
	
	public function getId() {
		return $this->id;
	}
	
	/**
	 * Store plugin config and set priorities if in configs
	 * @param array|Zend_Config $configs
	 */
	public function setConfigs($configs) {
		if ( $configs instanceof Zend_Config ) {
			$this->configs = $configs;
		} elseif ( is_array($configs) ) {
			$this->configs = new Zend_Config($configs);
		} else {
			X_Debug::e('Unknown plugin configs: '+ var_export($configs, true) );
			throw new Exception('Unknown configs');
		}
		if ( $this->configs->priorities ) {
			foreach( $this->configs->priorities->toArray() as $triggerName => $priority ) {
				$this->setPriority($triggerName, $priority);
			}
		} 
		if ( $this->configs->id ) {
			$this->id = $this->configs->id;
		}
	}
	
	public function config($key, $default = null) {
		$splitted = explode('.', $key);
		$configs = $this->configs;
		foreach ($splitted as $subkey) {
			$configs = $configs->get($subkey, null);
			if ( $configs === null ) {
				return $default;
			}
		}
		return $configs;
	}
	
	/**
	 * Shortcut for X_VlcShares_Plugins::helpers()
	 * @see X_VlcShares_Plugins::helpers()
	 * @return X_VlcShares_Plugins_Helper_Broker
	 */
	public function helpers($helperName = null) {
		return X_VlcShares_Plugins::helpers($helperName);
	}
	
	public function getPriority($triggerName) {
		if ( array_key_exists($triggerName, $this->priorities)) {
			return $this->priorities[$triggerName];
		} else {
			return -1;
		}
	}
	
	/**
	 * Set priority for the trigger 
	 * (if priority for a trigger is not setted,
	 * the trigger callback will be ignored)
	 * @param string $triggerName
	 * @param int $priority
	 * @return X_VlcShares_Plugins_Abstract
	 */
	public function setPriority($triggerName, $priority = 50) {
		$this->priorities[$triggerName] = $priority;
		return $this;
	}

	//=== GENERAL TRIGGER ===//
	
	/**
	 * Triggered after plugins system has been initialized
	 * Plugins that offer plugin-blacklisting services should hook here
	 * This trigger allow to unregister plugins
	 * @param X_VlcShares_Plugins_Broker $broker
	 */
	public function gen_afterPluginsInitialized(X_VlcShares_Plugins_Broker $broker) {}
	
	/**
	 * Triggered before page is generated (and action is called)
	 * All plugin for request filtering should hook here
	 * This trigger allow plugin to decided if request should be executed
	 * or redirected (altering router options). Example:
	 * if user not autenticated: --> redirect to login
	 * if planets are aligned: --> redirect to url/page
	 */
	public function gen_beforePageBuild(Zend_Controller_Action $controller ) {}
	
	/**
	 * Triggered after all links are generated
	 * All page renderer plugin should hook here
	 * This trigger allow plugin to decide how render
	 * the page.
	 * The rendering should be device based. For example
	 * after sniffing user agent
	 * if wiimc: --> output should be plx format
	 * if android: --> output should be html mobile format (low res screen)
	 * if pc: --> full size output
	 * 
	 * @param array $items items in the page
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 */
	public function gen_afterPageBuild($items, Zend_Controller_Action $controller) {}

	/**
	 * Triggered at the top of X_Controller_Action::init(),
	 * before controller properties init
	 * All resource initializer plugin should hook here
	 * @param Zend_Controller_Action $controller
	 */
	public function gen_beforeInit(Zend_Controller_Action $controller) {}
	
	/**
	 * Triggered at the end of X_Controller_Action::init(),
	 * after controller properties init
	 * Plugins who check resource initialization should hook here
	 * @param Zend_Controller_Action $controller
	 */
	public function gen_afterInit(Zend_Controller_Action $controller) {}
	
	/**
	 * Triggered before provider check and selection
	 * Allow plugin to change provider and/or params
	 * This should be usefull for blacklisting plugin
	 * @param $controller
	 */
	public function gen_preProviderSelection(Zend_Controller_Action $controller) {}
	
	
	//=== END OF GENERAL TRIGGER ===//

	//=== Triggered in Index:collections ===//
	
	/**
	 * Return items that should be added at the beginning of the list
	 * This hook can also used for redirect application flow
	 * 
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array
	 */
	public function preGetCollectionsItems(Zend_Controller_Action $controller) {}
	
	/**
	 * Return items that should be added in collection list
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array 
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {}
	
	/**
	 * Return items that should be added at the end of the list
	 * This hook can also used for redirect application flow
	 * 
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array
	 */
	public function postGetCollectionsItems(Zend_Controller_Action $controller) {}
	
	/**
	 * Check the item in the collection should be filtered out
	 * If return is false, the item will be discarded at 100%
	 * If return is true, isn't sure that the item will be added
	 * 'cause another plugin can prevent this
	 * 
	 * Plugins who check per-item acl or blacklist should hook here
	 * 
	 * @param mixed $item
	 * @param Zend_Controller_Action $controller
	 * @return boolean true if item is ok, false if item should be discarded
	 */
	public function filterCollectionsItems($item, Zend_Controller_Action $controller) {}

	
	//=== END OF Index:collections ===//

	//=== Triggered in Browse:share ===//
	
	/**
	 * Return items that should be added at the beginning of the list
	 * This hook can also used for redirect application flow
	 * 
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to share
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array
	 */
	public function preGetShareItems($provider, $location, Zend_Controller_Action $controller) {}
	
	/**
	 * Return items that should be added in collection list
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to share
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array 
	 */
	public function getShareItems($provider, $location, Zend_Controller_Action $controller) {}
	
	/**
	 * Return items that should be added at the end of the list
	 * This hook can also used for redirect application flow
	 * 
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to share
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array
	 */
	public function postGetShareItems($provider, $location, Zend_Controller_Action $controller) {}
	
	/**
	 * Check the item in the collection should be filtered out
	 * If return is false, the item will be discarded at 100%
	 * If return is true, isn't sure that the item will be added
	 * 'cause another plugin can prevent this
	 * 
	 * Plugins who check per-item acl or blacklist should hook here
	 * 
	 * @param mixed $item
	 * @param string $provider
	 * @param Zend_Controller_Action $controller
	 * @return boolean true if item is ok, false if item should be discarded
	 */
	public function filterShareItems($item, $provider, Zend_Controller_Action $controller) {}
	
	/**
	 * Allow plugin to shuffle/order items
	 * Plugin should use $provider to get location real location
	 * @param array &$items list of items
	 * @param string $provider id of the plugin the handle the request
	 * @param Zend_Controller_Action $controller
	 */
	public function orderShareItems(&$items, $provider, Zend_Controller_Action $controller) {}

	
	//=== END OF Browse:share ===//

	//=== Triggered in Browse:mode ===//
	
	/**
	 * Return items that should be added at the beginning of the list
	 * This hook can also used for redirect application flow
	 * 
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to stream
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array
	 */
	public function preGetModeItems($provider, $location, Zend_Controller_Action $controller) {}
	
	/**
	 * Return items that should be added in collection list
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to stream
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array 
	 */
	public function getModeItems($provider, $location, Zend_Controller_Action $controller) {}
	
	/**
	 * Return items that should be added at the end of the list
	 * This hook can also used for redirect application flow
	 * 
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to stream
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array
	 */
	public function postGetModeItems($provider, $location, Zend_Controller_Action $controller) {}
	
	/**
	 * Check the item in the collection should be filtered out
	 * If return is false, the item will be discarded at 100%
	 * If return is true, isn't sure that the item will be added
	 * 'cause another plugin can prevent this
	 * 
	 * Plugins who check per-item acl or blacklist should hook here
	 * 
	 * @param mixed $item
	 * @param string $provider
	 * @param Zend_Controller_Action $controller
	 * @return boolean true if item is ok, false if item should be discarded
	 */
	public function filterModeItems($item, $provider, Zend_Controller_Action $controller) {}
	
	/**
	 * Allow plugin to shuffle/order items
	 * Plugin should use $provider to get location real location
	 * @param array &$items list of items
	 * @param string $provider id of the plugin the handle the request
	 * @param Zend_Controller_Action $controller
	 */
	public function orderModeItems(&$items, $provider, Zend_Controller_Action $controller) {}
	

	//=== END OF Browse:mode ===//

	//=== Triggered in Browse:selection ===//
	
	/**
	 * Return items that should be added at the beginning of the list
	 * This hook can also used for redirect application flow
	 * 
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to stream
	 * @param string $pid pluginId who serves preferences selection options
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array
	 */
	public function preGetSelectionItems($provider, $location, $pid, Zend_Controller_Action $controller) {}
	
	/**
	 * Return items that should be added in collection list
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to stream
	 * @param string $pid pluginId who serves preferences selection options
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array 
	 */
	public function getSelectionItems($provider, $location, $pid, Zend_Controller_Action $controller) {}
	
	/**
	 * Return items that should be added at the end of the list
	 * This hook can also used for redirect application flow
	 * 
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to stream
	 * @param string $pid pluginId who serves preferences selection options
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array
	 */
	public function postGetSelectionItems($provider, $location, $pid, Zend_Controller_Action $controller) {}
	
	/**
	 * Check the item in the collection should be filtered out
	 * If return is false, the item will be discarded at 100%
	 * If return is true, isn't sure that the item will be added
	 * 'cause another plugin can prevent this
	 * 
	 * Plugins who check per-item acl or blacklist should hook here
	 * 
	 * @param mixed $item
	 * @param string $provider
	 * @param string $pid pluginId who serves preferences selection options
	 * @param Zend_Controller_Action $controller
	 * @return boolean true if item is ok, false if item should be discarded
	 */
	public function filterSelectionItems($item, $provider, $pid, Zend_Controller_Action $controller) {}
	
	/**
	 * Allow plugin to shuffle/order items
	 * Plugin should use $provider to get location real location
	 * @param array &$items list of items
	 * @param string $provider id of the plugin the handle the request
	 * @param string $pid pluginId who serves preferences selection options
	 * @param Zend_Controller_Action $controller
	 */
	public function orderSelectionItems(&$items, $provider, $pid, Zend_Controller_Action $controller) {}
	
	
	
}