# Introduction #

This tutorial show how to configure VLCShares and the Android Phone for the use of VLCShares

# Notes #

In this tutorial will be used as placeholder the following strings:
  1. IP\_ADDRESS: is the lan ip address of the server where you installed vlc-shares

# Instruction for VLCShares 0.5 ONLY #

### From 0.5.1 you don't need to do anything ###

Android browse interface is provided _temporarily_ by the WiimcPlxRenderer. In next versions this features will be provided by a dedicated plugin with a optimized interface. So, until next version will be ready the plugin WiimcPlxRenderer is required for Android support

## Details ##

  1. Open you browser at vlc-shares dashboard (http://localhost/vlc-shares/public/)
  1. Check in the plugin management section if the plugin _WiiMC Support_ is enabled
    1. If the plugin isn't enabled click on **VLCShares - configure**
    1. Search in the right column the plugin _WiimcPlxRenderer_ and enabled it
  1. Click on **WiiMC support**
  1. Set:
    * Force rendering = Yes
    * Fancy template = Yes
    * Show raw = No
  1. Save changes
  1. Open the Android Phone's browser to http:://IP\_ADDRESS/vlc-shares/public
  1. Click on Browse button

# Instructions for VLCShares 0.5.1 and later #

VLCShares 0.5.1 introduces a MobileRenderer plugin that handle request from mobile devices. The plugin is enabled by default and everything is ready to be used.

# Known issues #

  * Some apps in Android Market allow to switch the Android Phone's browser user agent to something different from a default Android OS signature. VLCShares routine for Android OS recognition check for default signature, so if you change your user-agent VLC could identify your device wrongly.
  * Some HTC Desire 2.2 expose by default a wrong browser signature. Please, be sure to change it back to Android Platform default.