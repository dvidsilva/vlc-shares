<?php

require_once 'X/Controller/Action.php';

class CacheController extends X_Controller_Action
{

	/**
	 * @var X_VlcShares_Plugins_Cache
	 */
	protected $plugin = null;
	
	function init() {
		// call parent init, always
		parent::init();
		if ( !X_VlcShares_Plugins::broker()->isRegistered('cache') ) {
			/*
			$this->_helper->flashMessenger(X_Env::_('err_pluginnotregistered') . ": youtube");
			$this->_helper->redirector('index', 'manage');
			*/
			throw new Exception(X_Env::_('err_pluginnotregistered') . ": cache");
		} else {
			$this->plugin = X_VlcShares_Plugins::broker()->getPlugins('cache');
		}
	}
	
	function clearallAction() {
		$this->plugin->clearCache();
		$this->_helper->flashMessenger(array('type' => 'success', 'text' => X_Env::_('p_cache_clearalldone')));
		$this->_helper->redirector('index', 'manage');
	}
	
	function clearoldAction() {
		$this->plugin->clearInvalid();
		$this->_helper->flashMessenger(array('type' => 'success', 'text' => X_Env::_('p_cache_clearolddone')));
		$this->_helper->redirector('index', 'manage');
	}
	
	
}

