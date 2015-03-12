# Introduction #

This guide help you for the post-install configuration of VLCShares 0.5.4



# Required tasks #

## First run setup ##

Just before the installation, we have to move inside the first run setup.

Open your browser (VLCShares is compatible with `Chrome 6+`, `Firefox 2+` e `Internet Explorer 9+`) to **http://localhost/vlc-shares**

You will see an interface just like the one in the picture:

[![](https://lh6.googleusercontent.com/_U6HIkh_ODAo/Ta6oEqfFFYI/AAAAAAAAAIU/47FBy46Gh-E/s400/installation.png)](https://picasaweb.google.com/lh/photo/yR7FDSu1uLAYcUYZDCT_dZQcgAM_hV8aasQj0QleA50?feat=directlink)

Fill all fields:

  * choosing your preferred language
  * choosing to enable or not user authentication features _(if you don't really need user authentication my advice is to left them disabled)_
  * choosing your username and password for the main user _(even if you turn off authentication, you have to choose your login credentials here)_
  * choosing if and which optional plugins you want to be installed automatically in the first run setup _(you can choose not install plugins now and do it through Plugin Installer later)_. A working network connection is required for this features.

Click on "Start installation". The task could take few seconds if you selected lots plugins (they have to be downloaded first). So, be patient.

On operation completed, the configuration page will be displayed.

## Paths configurations ##

Now it's the time to configure paths for VLC, FFMPEG, SOPCAST and RTMPDUMP.

[![](https://lh5.googleusercontent.com/_U6HIkh_ODAo/Ta6tfK0iwyI/AAAAAAAAAIg/HTY0Qg5l8Wc/s400/configs_basic.png)](https://picasaweb.google.com/lh/photo/qY6XTT8zXjW7gUQA6w9yJ5QcgAM_hV8aasQj0QleA50?feat=directlink)

**Note**: _if you are an Ubuntu user, you can just skip this steps because all paths are already valid. No changes are needed._


### VLC Path ###

Click on the button `Browse` on the right side of the `VLC Path` field. Browse in your directories until you find the VLC executable file.

**Warning**: you have to click on the "`[Select]`" link on the right side of the row to choose a file.

If you are Windows user, usually the default VLC path is:

```
C:\Program Files\VideoLan\Vlc\vlc.exe
```

Alternatively, you can write the VLC Path directly without browsing in the field. Please, be careful to add the filename also, not only the dirname.

This is a common error, so i repeat this: **be careful to select the right filename (not the directory path only)**

| **Valid** example | `C:\Program Files\VideoLan\Vlc\vlc.exe` |
|:------------------|:----------------------------------------|
| **NOT valid** example | `C:\Program Files\VideoLan\Vlc\` |


### FFMPEG Path ###

Click on the button `Browse` on the right side of the `FFMPEG Path` field. Browse in your directories until you find the FFMpeg executable file.

**Warning**: you have to click on the "`[Select]`" link on the right side of the row to choose a file.

If you are Windows user, usually the FFMpeg executable is inside the `\bin\` folder. If you followed the installation guide the FFMpeg path could be:

```
C:\Program Files\FFMpeg\bin\ffmpeg.exe
```

Alternatively, you can write the FFMpeg Path directly without browsing in the field. Please, be careful to add the filename also, not only the dirname.

This is a common error, so i repeat this: **be careful to select the right filename (not the directory path only)**

| **Valid** example | `C:\Program Files\FFMpeg\bin\ffmpeg.exe` |
|:------------------|:-----------------------------------------|
| **NOT Valid** example | `C:\Program Files\FFMpeg\bin\` |


### Percorso di RTMPDUMP ###

Click on the button `Browse` on the right side of the `RTMPDump rtmpgw Path` field. Browse in your directories until you find the `rtmpgw` executable file bundled with RTMPDump.

**Warning**: you have to click on the "`[Select]`" link on the right side of the row to choose a file.

**Warning**: the file needed by VLCShares is `rtmpgw` (`rtmpgw.exe` for Windows users), bundled in the RTMPDump archive.

If you followed the installation guide the `rtmpgw` path could be:

```
C:\Program Files\RTMPDump\rtmpgw.exe
```

Alternatively, you can write the RTMPDump's `rtmpgw` Path directly without browsing in the field. Please, be careful to add the filename also, not only the dirname.

This is a common error, so i repeat this: **be careful to select the right filename (not the directory path only)**

| **Valid** example | `C:\Program Files\RTMPDump\rtmpgw.exe` |
|:------------------|:---------------------------------------|
| **NOT valid** example | `C:\Program Files\RTMPDump\` |


### SOPCAST Path ###

Click on the button `Browse` on the right side of the `SopCast Path` field. Browse in your directories until you find the SopCast executable file.

**Warning**: you have to click on the "`[Select]`" link on the right side of the row to choose a file.

If you are Windows user, usually the default `SopCast.exe` path is:

```
C:\Program Files\SopCast\SopCast.exe
```

If you are Ubuntu (or linux) users, usually the default path for the `SopCast` executable is:

```
/usr/bin/sp-sc
```

Alternatively, you can write the `SopCast Path` directly in the field without browsing. Please, be careful to add the filename also, not only the dirname.

This is a common error, so i repeat this: **be careful to select the right filename (not the directory path only)**

| **Valid** example | `C:\Program Files\SopCast\SopCast.exe` |
|:------------------|:---------------------------------------|
| **NOT valid** example | `C:\Program Files\SopCast\` |


# Optional tasks #

## Plugin installation ##

[![](https://lh5.googleusercontent.com/_U6HIkh_ODAo/Ta63WoPs1PI/AAAAAAAAAIk/0ngjpldnX1A/s400/plugin_installer.png)](https://picasaweb.google.com/lh/photo/J3oN3UfaBs8IJwq-oSG4OJQcgAM_hV8aasQj0QleA50?feat=directlink)

### Automatic (online) installation ###

Starting from the 0.5.4 version, the Plugin Installer allow to install official (or mirrored) plugins directly, downloading it from the VLCShares website. To use this feature, you have to go in the `Plugin Installer` interface (usually **http://localhost/vlc-shares/plugin**) and choose the plugin you want to install clicking on `[+] Install`.
After that, follow on-screen instructions.

### Manual installation ###

The old way to install plugins is always there, unchanged.

If you want to install a new plugin, you have to do the upload of the plugin file using the form in the right part of the `Plugin Installer` interface.

Available plugins list, with a short description, is [in the page](PluginsList#Plugins_List.md).

## User authentication ##

User authentication features are introduced in VLCShares 0.5.4 to help users that want to install VLCShares on a shared PC or inside a PC directly accessible from Internet (without routers, firewalls or NATs).

For any other scenarios, user authentication could be turned of.

You can turn on or off user authentication clicking on the  `Enabled/Disabled` button near `User Auth` on the right part of the VLCShares configs page (usually **http://localhost/vlc-shares/configs**).

![https://lh3.googleusercontent.com/_U6HIkh_ODAo/Ta6tes_uhyI/AAAAAAAAAIY/TqQFxhH_FhQ/s800/configs_authoff.png](https://lh3.googleusercontent.com/_U6HIkh_ODAo/Ta6tes_uhyI/AAAAAAAAAIY/TqQFxhH_FhQ/s800/configs_authoff.png)

User authentication works in this way: all VLCShares services require that the user perform a Login. He can do it with Username and Password from any Web-Browser enabled devices.

For others type of devices, as WIIMC for example, the **`Direct Login URL`** must be used.

Each user can find his `Direct Login URL` going into the User Accounts interface.

[![](https://lh3.googleusercontent.com/_U6HIkh_ODAo/Ta8B6cEmE_I/AAAAAAAAAIo/t9GJzOHyg_A/s400/user_accounts.png)](https://picasaweb.google.com/lh/photo/a8eppb3amyNJ4e0weOeIRpQcgAM_hV8aasQj0QleA50?feat=directlink)

To perform a login with the `Direct Login URL`, you only have to browse the `Direct Login URL` with your device. VLCShares keep track of the IP address and the User-Agent string used from the device and allow the access for 24 hours from the authentication moment. After this period, a new authentication must be performed throught the `Direct Login URL`.

The `Direct Login URL` changes on each password change for the relative user.

**Warning**: the `Direct Login URL` feature is a security hole that can allow devices with same IP address and User-Agent of an authenticated device to use VLCShares. This feature have to the used only in scenarios when a normal login with username and password cannot be used (with WiiMC for example).

## How to enable Debug log ##

This procedure show how to enable the Debug log with the max detail level.

Go into the VLCShares configuration page (http://localhost/vlc-shares/configs)

Click on `Show advanced`.

Set:
  * **`Debug log enabled`** to **`Yes`**
  * **`Debug level`** to `*ALL*`.

Click on `Save`.

[![](https://lh5.googleusercontent.com/_U6HIkh_ODAo/Ta6te03jLqI/AAAAAAAAAIc/XHQ6Q8nDjYg/s400/configs_debugon.png)](https://picasaweb.google.com/lh/photo/VyZXLyI-CU_00DEbT6o2IJQcgAM_hV8aasQj0QleA50?feat=directlink)
