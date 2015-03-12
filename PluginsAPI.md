# Introduction #

This document talks about the plugin system of VLCShares. This infos are aimed for developer.

# System Architecture #

VLCShares is built upon [ZendFramework](http://framework.zend.com/). Understanding of the functioning of the framework is a recommended prerequisite to better understand how VLCShares works.

VLCShares follows the MVC Pattern and integrates an independent Plugins System. Main controllers follow the baselines of ZendFramework (so, they are placed inside _application/controllers/_ folder) and follow ZendFramework name rules.

Standard main controllers are:
  * **IndexController**: index controller. Redirect the application flow to Management interface or Browse interface and provides collections index page.
  * **ManageController**: management page controller. Dashboard and configs panels are provided by this controller.
  * **BrowseController**: manage the browse/stream functionality. The main work of this plugin is to reassemble resource returned by plugins.
  * **ControlsController**: provides functionality for controls vlc's streams.
  * **TestController**: execute system test and provides results.
  * **ErrorController**: display error information
  * **ConfigController**: provide a way to manage lazy plugins configurations without the need of create a plugin management interface for each plugin.
  * **InstallerController**: execute initial configuration of VLCShares

The following scheme reproduce the transactions between controlles and pages while in browse and reproduction mode

| ![http://vlc-shares.googlecode.com/svn/wiki/scheme-browse-reproduction.png](http://vlc-shares.googlecode.com/svn/wiki/scheme-browse-reproduction.png) |
|:------------------------------------------------------------------------------------------------------------------------------------------------------|

## Plugins System ##

The core part of plugins system is the Plugin Broker (`X_VlcShares_Plugins_Broker` class) object. It acts as a container for plugin references and forwards trigger calls to each plugin that is registered for the trigger. This object is bootstrapped by another class (`X_VlcShares_Plugins`) that has the assignment of create plugin broker, register installed (and active) plugins into the broker, initialize plugins configs and initialize active plugin helpers.
The plugin broker also provides services for register/unregister new plugins on the fly or for plugin retrieval by id or class.
Usage example (can be executed anywhere inside the application):
```
$pluginBroker = X_VlcShares_Plugins::broker();
$myPlugin = new My_Plugin_That_Does_Lots_Stuff();

// register on the fly a new plugin
$pluginBroker->register('myPluginId', $myPlugin);

// check if plugin is registered
if ( $pluginBroker->isRegistered('myPluginId') ) {
   // ...and unregister it
   $pluginBroker->unregisterPluginId('myPluginId');
} else {
   // ...or unregister all plugin of class...
   $pluginBroker->unregisterPluginClass('My_Plugin_That_Does_Lots_Stuff');
}

// let's see what is registered now
var_dump($pluginBroker->getPlugins());

// i want a specific plugin by ID
try {
   $anotherPlugin = $pluginBroker->getPlugins('anotherPluginId');
   $anotherPlugin->specificFunctionCall();
} catch (Exception $e) {
   // there isn't the plugin
}

// let's call a trigger function:
// even if there is no gen_afterPluginsInitialized in the class
// the magic function __call trap the call and forward it
// to all plugin registered for that trigger
$pluginBroker->gen_afterPluginsInitialized();

// unregister all plugins
$pluginBroker->unregisterAll();

```

The controllers use the plugin broker to trigger functions of registered plugins.

A valid plugin for VLCShares 0.5 must extends the abstract class `X_VlcShares_Plugins_Abstract`. This abstract class provides basic services for configuration store and retrieval, plugins priorities and a list of implemented and neutral (no interaction or return values) valid trigger functions.
While creating a new plugin you must override one or more triggers and register the plugin for those triggers using the `setPriority` function.

### Priority System ###
The priority system is used by the plugin broker to sort plugin calls. If a plugin want to register itself for a trigger, it must use the `setPriority` function:

```
/**
 * Set priority for the trigger 
 * (if priority for a trigger is not setted,
 * the trigger callback will be ignored)
 * @param string $triggerName
 * @param int $priority
 * @return X_VlcShares_Plugins_Abstract
 */
public function setPriority($triggerName, $priority = 50);
```

Valid values for `$priority` are positive integers. _0_ indicates top priority. If two or more plugins register their self for the same trigger with the same priority, they will be sorted by registration order.

### Standard Interfaces ###

VLCShares provides some standard interface for plugins.
  * `X_VlcShares_Plugins_ResolverInterface`: data provider plugins should implement this interface for allow others plugin to resolve a decoded and absolute resource location.
  * `X_VlcShares_Plugins_ResolverDisplayableInterface`: this interface extends `X_VlcShares_Plugins_ResolverInterface` and ensures that the returned value of `resolveLocation` function can be displayed by the interface. This interface has been added in VLCShares 0.5.1

### Inputs and Outputs ###

Input and Outputs of trigger functions are specified in the function comment. However, most of the trigger functions of apis require `X_Page_ItemList` object as return.

Function of Level 1 Api expect:

  * `X_Page_ItemList_PItem` for functions like `preGetXXX`,`getXXX`,`postGetXXX`
  * `boolean` for functions like `filterXXX`
  * `array of X_Page_Item_PItem` for function like `orderXXX`

Read function triggers comments inside the class `X_VlcShares_Plugins_Abstract` for more informations

### Plugin Preferences ###

Each plugin can have its own preferences or options. Those options are stored inside the database at plugin installation time and will be fetched and inserted in the plugin every time the plugin systems is initialized automatically. Plugins created and registered on the fly through the plugin broker need to be manually initialized.

Inside the plugin scope, options are available through the `$this->options` class property as a `Zend_Config` object or through the function:
```
/**
 * Return a config key value or the default specified as parameter
 * if the key isn't registered. This function allow
 * keys in dotted notation (key.subkey.subsubkey)
 * @param string $key
 * @param midex $default
 */
public function config($key, $default = null);
```

Options available in the plugin scope are read-only. To change config values must be used a proper interface provided by the plugin or the way offered by the ConfigController for lazy plugins.

## Plugins Helpers ##

VLCShares provide a set of plugin helpers for plugins and controllers. Each helper has its own interface and for more information you should read the helper documentation or the inline comments.
The purpose of plugin helpers is to provide common services for all plugins. Each plugin helper is registered agains the plugin helper broker (`X_VlcShares_Plugins_Helper_Broker` class). A reference to helper broker is available through the `X_VlcShares_Plugins::helpers()` function or, in the plugin class scope, through the `X_VlcShares_Plugins_Abstract::helpers()` method. The helper broker allow to register/unregister new helpers on the fly. For more information read the inline comments.

VLCShares has a set of core helpers:

| **Helper name** | Purpose |
|:----------------|:--------|
| Devices | Provide services for identify the device who made a page request through the user-agent |
| FFMpeg | Allow to gain information about a stream, both remote than local |
| MediaInfo | Allow to gain information about a local stream only |
| Stream | Allow to gain information about a stream, both remote than local, through FFMpeg or MediaInfo. Using this helper you will get the infos and the system will choose the best adapter (FFMpeg or MediaInfo) for the stream |
| Language | Allow to load external translation files for the current selected VLCShares language |

Some plugins add new helpers:

| **Helper name** | Provided by plugin: | Purpose |
|:----------------|:--------------------|:--------|
| Youtube | youtube | is a wrapper for Zend\_GData\_YouTube object and give some features not included in official YouTube API (as closed captions, more video qualities) |
| Megavideo | megavideo | allow to fetch information about a megavideo video by video ID or video URL |


# How to create a new Plugin #

[This short tutorial show how to create a new plugin for VLCShares 0.5.1 and later](HowToNewPlugin#Version_0.5.1.md)