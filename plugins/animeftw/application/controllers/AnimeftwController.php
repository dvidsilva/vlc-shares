<?php

require_once 'X/Controller/Action.php';

class AnimeftwController extends X_Controller_Action
{

	/**
	 * @var X_VlcShares_Plugins_AnimeFTW
	 */
	protected $plugin = null;
	
	function init() {
		// call parent init, always
		parent::init();
		if ( !X_VlcShares_Plugins::broker()->isRegistered('animeftw') ) {
			/*
			$this->_helper->flashMessenger(X_Env::_('err_pluginnotregistered') . ": youtube");
			$this->_helper->redirector('index', 'manage');
			*/
			throw new Exception(X_Env::_('err_pluginnotregistered') . ": animeftw");
		} else {
			$this->plugin = X_VlcShares_Plugins::broker()->getPlugins('animeftw');
		}
	}

	function proxy2Action() {
		
		// time to get params from get
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		
		if ( !$this->plugin->config('proxy.enabled', true) ) {
			throw new Exception(X_Env::_('p_animeftw_err_proxydisabled'));
		}

		// this action is so special.... no layout or viewRenderer
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
		
		$id = $request->getParam('id', false); // video file url
		
		if ( $id === false ) {
			throw new Exception(X_Env::_('p_animeftw_err_invalidrequest'));
			return;
		}
		$id = X_Env::decode($id);
		
		
		ignore_user_abort(false);
		
		// $href is the video id
		
		/* @var $helper X_VlcShares_Plugins_Helper_AnimeFTW */
		$helper = X_VlcShares_Plugins::helpers()->helper('animeftw');
		$episode = $helper->getEpisode($id);
	
		$videoUrl = $episode['url'];
		
		
		while ( ob_get_level() != 0 )
			ob_end_clean();
		
		$userAgent = 'User-Agent: vlc-shares/'.X_VlcShares::VERSION.' animeftw/'.X_VlcShares_Plugins_AnimeFTW::VERSION; 
		
		$opts = array('http' =>
			array(
				'header'  => array(
					"User-Agent: $userAgent",
				)
			)
		);

		$context  = stream_context_create($opts);
		// readfile open a file and send it directly to output buffer
		readfile($videoUrl, false, $context);
					
	}
	
	
	function proxyAction() {
		
		// time to get params from get
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		
		if ( !$this->plugin->config('proxy.enabled', true) ) {
			throw new Exception(X_Env::_('p_animeftw_err_proxydisabled'));
		}

		// this action is so special.... no layout or viewRenderer
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
		
		$videoUrl = $request->getParam('v', false); // video file url
		$refererUrl = $request->getParam('r', false); // referer page needed
		
		if ( $videoUrl === false || $refererUrl === false ) {
			// invalid request
			throw new Exception(X_Env::_('p_animeftw_err_invalidrequest'));
			return;
		}
		
		$videoUrl = X_Env::decode($videoUrl);
		$refererUrl = X_Env::decode($refererUrl);
		
		// if user abort request (vlc/wii stop playing, this process ends
		ignore_user_abort(false);
		
		// close and clean the output buffer, everything will be read and send to device
		ob_end_clean();
		
		//$userAgent = $this->plugin->config('hide.useragent', true) ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1';
		$userAgent = 'User-Agent: vlc-shares/'.X_VlcShares::VERSION.' animeftw/'.X_VlcShares_Plugins_AnimeFTW::VERSION; 
		
		$opts = array('http' =>
			array(
				'header'  => array(
					"Referer: $refererUrl",
					"User-Agent: $userAgent",
				)
			)
		);

		$context  = stream_context_create($opts);
		// readfile open a file and send it directly to output buffer
		readfile($videoUrl, false, $context);
		
	}
}

