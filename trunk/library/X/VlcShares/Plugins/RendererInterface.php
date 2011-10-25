<?php

/**
 * Plugins which provide gui functions implements
 * this interface
 * @author ximarx
 *
 */
interface X_VlcShares_Plugins_RendererInterface {
	
	const FEATURES_AJAX = 'ajax';
	const FEATURES_JS = 'js';
	const FEATURES_HTML = 'html';
	const FEATURES_PLX = 'plx';
	const FEATURES_RSS = 'rss';
	const FEATURES_WEBKIT = 'webkit';
	const FEATURES_HTML5 = 'html5';
	const FEATURES_SMALLSCREEN = 'smallscreen';
	const FEATURES_BIGSCREEN = 'bigscreen';
	const FEATURES_UPNP = 'upnp';
	const FEATURES_DNLA = 'dnla';
	const FEATURES_IMAGES = 'images';
	const FEATURES_ADWORDS = 'adwords';
	const FEATURES_STANDALONEPLAYER = 'standaloneplayer';
	 
	
	/**
	 * Show renderer's human-readable name 
	 * @return string
	 */
	function getName();
	/**
	 * Show renderer's human-readable description
	 * @return string
	 */
	function getDescription();
	/**
	 * Give an array of features required by devices
	 * @return array[string]
	 */
	function getRequiredFeatures();
	
	/**
	 * Flag the renderer for default
	 */
	function setDefaultRenderer($force = true);
	
	/**
	 * Check if the object will be used as default renderer
	 * @return bool
	 */
	function isDefaultRenderer();
	
}

