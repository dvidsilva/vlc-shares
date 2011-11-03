<?php

class SpainradioController extends X_Controller_Action
{

	/**
	 * @var X_VlcShares_Plugins_Spainradio
	 */
	protected $plugin = null;
	
	function init() {
		// call parent init, always
		parent::init();
		if ( !X_VlcShares_Plugins::broker()->isRegistered('spainradio') ) {
			/*
			$this->_helper->flashMessenger(X_Env::_('err_pluginnotregistered') . ": youtube");
			$this->_helper->redirector('index', 'manage');
			*/
			throw new Exception(X_Env::_('err_pluginnotregistered') . ": spainradio");
		} else {
			$this->plugin = X_VlcShares_Plugins::broker()->getPlugins('spainradio');
		}
	}
	
	function proxyAction() {
		
		// time to get params from get
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		
		// this action is so special.... no layout or viewRenderer
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
		
		$videoUrl = $request->getParam('v', false); // video file url
		//$refererUrl = $request->getParam('r', false); // referer page needed
		
		if ( $videoUrl === false /*|| $refererUrl === false */) {
			// invalid request
			throw new Exception(X_Env::_('p_spainradio_err_invalidrequest'));
		}
		
		$videoUrl = X_Env::decode($videoUrl);
		//$refererUrl = X_Env::decode($refererUrl);
		
		if ( !defined("X_VlcShares_Plugins_Spainradio::C_$videoUrl") ) {
			throw new Exception(X_Env::_('p_spainradio_err_invalidchannel'));
		}
		
		$videoUrl = constant("X_VlcShares_Plugins_Spainradio::C_$videoUrl");
		
		
		//$userAgent = $this->plugin->config('hide.useragent', true) ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION.' spainradio/'.X_VlcShares_Plugins_Spainradio::VERSION : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1';
		//$userAgent = 'User-Agent: vlc-shares/'.X_VlcShares::VERSION.' spainradio/'.X_VlcShares_Plugins_Spainradio::VERSION; 
		
		$opts = array('http' =>
			array(
				'header'  => array(
					//"Referer: $refererUrl",
					//"User-Agent: $userAgent",
					//'viaurl: www.rtve.es'
				)
			)
		);

		$context  = stream_context_create($opts);
		/* redirect support in wiimc exists only from 1.1.0
		if ( X_VlcShares_Plugins::helpers()->devices()->isWiimc() && !X_VlcShares_Plugins::helpers()->devices()->isWiimcBeforeVersion('1.0.9') && $this->plugin->config('direct.enabled', true) ) {
			$match = array();
			$xml = file_get_contents($videoUrl, false, $context);
			if (  preg_match('/<REF HREF=\"([^\"]*)\"\/>/', $xml, $match ) ) {
				$this->_helper->redirector->gotoUrlAndExit($match[1]);
			} else {
				throw new Exception(X_Env::_('p_spainradio_err_invalidchannel'));
			}
		} else {*/
			// if user abort request (vlc/wii stop playing, this process ends)
			ignore_user_abort(false);
			
			// close and clean the output buffer, everything will be read and send to device
			ob_end_clean();
			
			// readfile open a file and send it directly to output buffer
			readfile($videoUrl, false, $context);
		//}
		
	}
}

