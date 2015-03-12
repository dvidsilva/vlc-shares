# Introduction #

This is a reference guide/tutorial for plugins creation


## What's needed ##

  * a little of PHP knowledge (if you know what Zend Framework is... it's better)
  * a text editor or a PHP ide (Notepad++, Eclipse PDT, Zend Studio, Gedit,.... everything except Windows Notepad or Wordpad)
  * the plugin sample template for your target version (you can download it from the download section)

# Version 0.5.1 #

As first thing, you have to download the plugin\_sample\_0.5.1.zip from the download version.
This small archive is a real plugin (and works), but it's useless. You should use it as a base for your plugins.

Let's see what's inside the archive:

  * manifest.xml
  * install.sql
  * uninstall.sql
  * library/X/VlcShares/Plugins/Sample.php

## Manifest.xml ##

The file `manifest.xml` is a manifest file for the plugin. The plugin installer will use the informations inside this file to install the plugin inside VLCShares.

This is the manifest.xml content:

```
<?xml version="1.0" encoding="UTF-8"?>
<vs-manifest type="plugin">
	<metadata>
		<label>Sample plugin</label>
		<description>This plugin is an example to show how to create installable plugins for VLCShares 0.5</description>
		<version>0.1</version>
		<status>stable</status>
		<key>sample</key>
		<file>X/VlcShares/Plugins/Sample.php</file>
		<class>X_VlcShares_Plugins_Sample</class>
		<compatibility>
			<from>0.5</from>
			<to>0.6</to>
		</compatibility>
	</metadata>
	<files>
		<library>
			<X>
				<VlcShares>
					<Plugins>
						<file>Sample.php</file>
					</Plugins>
				</VlcShares>
			</X>
		</library>
	</files>
	<database>
		<install>install.sql</install>
		<uninstall>uninstall.sql</uninstall> 
	</database>
</vs-manifest>
```

There are 3 main areas: metadata, files and database.

### Metadata ###

```
	<metadata>
		<label>Sample plugin</label>
		<description>This plugin is an example to show how to create installable plugins for VLCShares 0.5</description>
		<version>0.1</version>
		<status>stable</status>
		<key>sample</key>
		<file>X/VlcShares/Plugins/Sample.php</file>
		<class>X_VlcShares_Plugins_Sample</class>
		<compatibility>
			<from>0.5</from>
			<to>0.6</to>
		</compatibility>
	</metadata>
```

The metadata tag contains all informations about the plugin:
  * _**label**_: this plugin name. You can insert here a normal text or a translation string (for example `p_sample_pluginnamestring`)
  * _**description**_: this is the plugin description. As for the `label` tag, you can write here a normale text or a translation string
  * _**version**_: the version of this plugin. Usually I use `MAINVERSION.MINORVERSION[_DEVVERSION]` notation for plugin version, but if you like you can use everything you like (for example `superrelease` or `orange` :P)
  * _**key**_: this is a very important tag. Each plugin must have a unique key value. Can be at least one plugin for each key installed in each vlc-shares. So, when you choose your plugin key give a look at the official plugins list. Anyway I suggest to use alphabetic string only.
  * _**file**_: path and filename of the main plugin file after a complete installation in a vlc-shares system. The path have to be relative to `vlc-shares/library/` folder. You can put the file where you like. Anyway, for coherency it's better if you put it inside the `X/VlcShares/Plugins/` folder.
  * _**class**_: the main plugin classname. Nothing more, nothing less. You have only to remember that this class must extends `X_VlcShares_Plugins_Abstract`. We will explain this better later.
  * _**compatibility/from**_: the version number of the first vlc-shares version that is compatible with this plugin. Remember that the comparison is executed again the vlc-shares CLEAN version (so, no _beta,_alpha or any other [_DEVVERSION]).
  *_**compatibility/to**_: this param is optional. Can be used to give an upper bound to the version compatibility. VLCShares version has a notation like `EPICCHANGE.MAINVERSION.MINORVERSION[_DEVVERSION]`. The vlc-shares plugin compatibility will be broken only on `EPICCHANGE` or `MAINVERSION` changes. Should be safe to use 0.6 as upper bound._

### Files ###

Inside the `files` tag you will find all folders and files that have to be copied inside the VLCShares folder

```
	<files>
		<library>
			<X>
				<VlcShares>
					<Plugins>
						<file>Sample.php</file>
					</Plugins>
				</VlcShares>
			</X>
		</library>
	</files>
```

You have to think about `files` tag as a root folder. The plugin installer will use the`vlc-shares/` folder as basepath. All tags inside the `files` tag will be evaluated as folder except the `file` tag, that is used to declare filename. All folders, if they don't exist, will be created only if there is at least a file inside them. So, if you have to create a empty folder you have to insert a dummy file inside it.

Remember that folder and file names are case-sensitive. It means that `VlcShares` isn't the same of `VLCSHARES` or `vlcshares`.

Just one more thing: the `file` tag is used to highlight filename. You can't use a folder named `file` inside the `files` tag or the plugin installer will recognize it as e `file` tag.

In this example the plugin installer will create a folder `vlc-shares/library/X/VlcShares/Plugins/` and the file `Sample.php` will be copied inside it.

You can place your files everywhere inside the `vlc-shares/` folder. I don't care. Anyway those are some conventions:

  * Main plugin class is better if placed in the `/library/X/VlcShares/Plugins/` folder
  * Helpers classes are better if placed in the `/library/X/VlcShares/Plugins/Helper/` folder
  * Controllers classes are better if placed in the `/application/controllers/` folder
  * Models classes are better if placed in the `/application/models/` folder
  * DbTable classes are better if placed in the `/application/models/DbTable` folder
  * Forms classes are better if placed in the `/application/forms/` folder
  * View scripts are better if placed in the `/application/views/scripts/` folder
  * Layout scripts are better if placed in the `/application/layouts/scripts` folder
  * images/js/css MUST be placed in the `/public/` folder
  * 3rd party libraries are better if placed in the `/library/` folder
  * Plugins datas are better if placed in the `/data/` folder

I'm near to forgot a thing: if you try to overwrite any existent file, the plugin installer will raise an error and nothing will be done. If you really have the needs of patch or replace a file you have to write you own logic and execute it after the installation. Anyway, it's better to avoid.

### Database ###

The database tag contains only two things: the name of in install sql script (in the `install` tag) and the name of the uninstall sql script (in the `uninstall` tag).

```
	<database>
		<install>install.sql</install>
		<uninstall>uninstall.sql</uninstall> 
	</database>
```

Those files have to be placed inside the root of the archive, just like the `manifest.xml` file.

The `install.sql` in Sample plugin does just one thing: enable the sample plugin when plugins installer has finished.

This is the content of `install.sql`
```
UPDATE plugins SET enabled=1 WHERE key = "sample";
```

The `uninstall.sql` instead is empty because there's nothing to do. Remember that plugins installer will raise an error if the `uninstall.sql` isn't empty but no sql are performed.

Other plugins can `for example` create new table, add row, plugin configs... If you want to have more ideas on how to use `install.sql`/`uninstall.sql` give a look inside the YouTube plugin. It does a lot of things.

Remember that is a good practice to remove all created data, tables, configs in the uninstaller script: there is no need to leave datas inside the database. If the user wants to store somethings, it can use the backupper to save/restore datas.

## The Plugin Class: X\_VlcShares\_Plugins\_Sample ##

The main class of the plugin is `X_VlcShares_Plugins_Sample` in `/library/X/VlcShares/Plugins/Sample.php`.

I don't paste here the complete file content, you can read it from the file directly. I will highlights only some slice.

```

<?php

require_once 'X/VlcShares/Plugins/Abstract.php';

/**
 * This is a soc plugin:
 * It's an example of how to add warning messages in dashboard
 * This plugin only produce info message for the dashboard.
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Sample extends X_VlcShares_Plugins_Abstract {

[---- OMISSIS ----]
	
}

```

First thing: all VLCShares plugins have to extends the `X_VlcShare_Plugins_Abstract` class. This abstract class does only few things: have getter/setter methods for configs, have setter for the plugin priority system and has all the Api triggers declared.

[Maybe it's better if you give a look at the API.](PluginsAPI#System_Architecture.md)

Now you should know that the `Abstract` class if full of empty function. Each of them is called by the plugin broker when needed, but only if the plugin has register itself in the priority system for the trigger.

The best place to do this (maybe it's the only one) it in the class costructor.
The sample plugin has to add a Warning message in the dashboard. For this reason in the costructor we have to set the priority for the `getIndexMessages` trigger.


```

<?php

require_once 'X/VlcShares/Plugins/Abstract.php';

/**
 * This is a soc plugin:
 * It's an example of how to add warning messages in dashboard
 * This plugin only produce info message for the dashboard.
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Sample extends X_VlcShares_Plugins_Abstract {

	public function __construct() {
		$this->setPriority('getIndexMessages', 1); // i add it near the top of the queue
	}

[---- OMISSIS ----]
	
}

```

The `getIndexMessages` trigger is called in the `ManageController`, which is the the controller that handle the dashboard. Now, the Sample plugin is registered for the trigger. I set the priority value to `1` to be ...almost... sure that the Sample message will be displayed as first message in the queue. I don't use the `0` value because it should be used only by system plugins.

Now the priority system is ready, but how can you print the message?

You have to implement the `getIndexMessages` function. The better way to do this is to copy and paste inside the class the function taken from the `Abstract` class.

```

<?php

require_once 'X/VlcShares/Plugins/Abstract.php';

/**
 * This is a soc plugin:
 * It's an example of how to add warning messages in dashboard
 * This plugin only produce info message for the dashboard.
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Sample extends X_VlcShares_Plugins_Abstract {

	public function __construct() {
		$this->setPriority('getIndexMessages', 1); // i add it near the top of the queue
	}

	/**
	 * Return the HELLO WORLD message
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Message
	 */
	public function getIndexMessages(Zend_Controller_Action $controller) {
		$message = new X_Page_Item_Message($this->getId(), 'HELLO WORLD');
		$message->setType(X_Page_Item_Message::TYPE_WARNING);
		return new X_Page_ItemList_Message(array($message));
	}
	
}

```

The `getIndexMessages` has to return to the broker a `X_Page_ItemList_Message` object. This object is nothing more than a list of `X_Page_Item_Message object`. As first thing we create the `X_Page_Item_Message object`. Message id is the same of the plugin (the plugin id is the key) and the message content is 'HELLO WORLD'. Then, we set the message type to Warning and as last thing we create and return the List (with one item only)

## The package ##

After everything is ready, it's time to create a plugin package. WinZip, WinRar.... use what you prefer. You have to create a ZIP archive with all the files and folders of the plugin.

The manifest, the installer/uninstaller script and all the folders have to be place inside the root of the archive.

Everything is done. It's time to install the plugin through the Plugins Installer.
[This tutorial should be usefull.](PluginsList#Plugin_Installer.md)

## Note ##

There are a lot of triggers that can be used. The best place where find more info is inside the `Abstract` class and reading the source of other plugins. One of the best made is the one for YouTube.

Give a look at the images below and you will see the truth :P

![http://vlc-shares.googlecode.com/svn/wiki/images/api_dashboard.png](http://vlc-shares.googlecode.com/svn/wiki/images/api_dashboard.png)

![http://vlc-shares.googlecode.com/svn/wiki/images/api_collections.png](http://vlc-shares.googlecode.com/svn/wiki/images/api_collections.png)




