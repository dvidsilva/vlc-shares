<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'Zend/Controller/Action.php';
require_once 'Zend/Config.php';

/**
 * Add button to controls page
 * 
 * Configs:
 * 
 * - show Pause/Resume button
 * 		pauseresume.enabled = false
 * 
 * - show Stop button
 * 		stop.enabled = true
 * 
 * - show Forward minutes (type Search) button
 * 		forwardrelative.enabled = false
 * 
 * - show Back minutes (type Search) button
 * 		backrelative.enabled = false
 * 
 * - show Seek button (type Search) button
 * 		seek.enabled = true
 * 
 * - show buttons from the previous version of plugin (+/- 5, 30...)
 * 		oldstylecontrols.enabled = false
 * 
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Controls extends X_VlcShares_Plugins_Abstract {

	public function __construct() {
		$this->setPriority('getControlItems')
			->setPriority('execute')
			->setPriority('getIndexManageLinks')
			->setPriority('getStreamItems');
	}
	
	/**
	 * Add pause/resume, stop, forward, rewind, shift buttons
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array 
	 */
	public function getControlItems(Zend_Controller_Action $controller) {
	
		$urlHelper = $controller->getHelper('url');
		
		$return = array();

		if ( $this->config('pauseresume.enabled', false)) {
			// pause/resume
			$return[] =	array(
				'label'	=>	X_Env::_('p_controls_pauseresume'),
				'link'	=>	X_Env::completeUrl($urlHelper->url(array(
						'controller'		=>	'controls',
						'action'	=>	'execute',
						'a'			=>	'pause',
						'pid'		=>	$this->getId(),
					), 'default', false)
				),
				'icon'	=>	'/images/icons/pause.png'
			);
		}
		if ( $this->config('stop.enabled', true)) {
			// stop
			$return[] =	array(
				'label'	=>	X_Env::_('p_controls_stop'),
				'link'	=>	X_Env::completeUrl($urlHelper->url(array(
						'controller'		=>	'controls',
						'action'	=>	'execute',
						'a'			=>	'stop',
						'pid'		=>	$this->getId(),
					), 'default', false)
				),
				'icon'	=>	'/images/icons/stop.png'
			);
		}
		if ( $this->config('forwardrelative.enabled', false)) {
			// forward relative
			$return[] =	array(
				'label'	=>	X_Env::_('p_controls_forwardcustom'),
				'link'	=>	X_Env::completeUrl($urlHelper->url(array(
						'controller'		=>	'controls',
						'action'			=>	'execute',
						'a'					=>	'forward',
						'pid'				=>	$this->getId(),
						'param'				=>	'' // this will replaced by wiimc....
					), 'default', false)
				),
				'type'	=> X_Plx_Item::TYPE_SEARCH,
				'icon'	=>	'/images/icons/forward.png',
				'desc'	=>	X_Env::_('p_controls_forwardcustom_desc')
			);
		}
		if ( $this->config('backrelative.enabled', false)) {
			// rewind relative
			$return[] =	array(
				'label'	=>	X_Env::_('p_controls_backcustom'),
				'link'	=>	X_Env::completeUrl($urlHelper->url(array(
						'controller'		=>	'controls',
						'action'			=>	'execute',
						'a'					=>	'back',
						'pid'				=>	$this->getId(),
						'param'				=>	'' // this will replaced by wiimc....
					), 'default', false)
				),
				'type'	=> X_Plx_Item::TYPE_SEARCH,
				'icon'	=>	'/images/icons/back.png',
				'desc'	=>	X_Env::_('p_controls_backcustom_desc')
			);
		}
		if ( $this->config('seek.enabled', true)) {
			// seek to time
			$return[] =	array(
				'label'	=>	X_Env::_('p_controls_seektominute'),
				'link'	=>	X_Env::completeUrl($urlHelper->url(array(
						'controller'		=>	'controls',
						'action'			=>	'execute',
						'a'					=>	'seek',
						'pid'				=>	$this->getId(),
						'param'				=>	'' // this will replaced by wiimc....
					), 'default', false)
				),
				'type'	=> X_Plx_Item::TYPE_SEARCH,
				'icon'	=>	'/images/icons/seek.png',
				'desc'	=>	X_Env::_('p_controls_seektominute_desc')
			);
		}
		
		if ( $this->config('oldstylecontrols.enabled', false) ) {

			$return[] =	array(
				'label'	=>	X_Env::_('p_controls_back_5'),
				'link'	=>	X_Env::completeUrl($urlHelper->url(array(
						'controller'		=>	'controls',
						'action'			=>	'execute',
						'a'					=>	'back',
						'pid'				=>	$this->getId(),
						'param'				=>	'5' // this will replaced by wiimc....
					), 'default', false)
				),
				'type'	=> X_Plx_Item::TYPE_SEARCH,
				'icon'	=>	'/images/icons/back.png'
			);
			
			
			$return[] =	array(
				'label'	=>	X_Env::_('p_controls_forward_5'),
				'link'	=>	X_Env::completeUrl($urlHelper->url(array(
						'controller'		=>	'controls',
						'action'			=>	'execute',
						'a'					=>	'forward',
						'pid'				=>	$this->getId(),
						'param'				=>	'5' // this will replaced by wiimc....
					), 'default', false)
				),
				'type'	=> X_Plx_Item::TYPE_SEARCH,
				'icon'	=>	'/images/icons/forward.png'
			);
			
			$return[] =	array(
				'label'	=>	X_Env::_('p_controls_back_30'),
				'link'	=>	X_Env::completeUrl($urlHelper->url(array(
						'controller'		=>	'controls',
						'action'			=>	'execute',
						'a'					=>	'back',
						'pid'				=>	$this->getId(),
						'param'				=>	'30' // this will replaced by wiimc....
					), 'default', false)
				),
				'type'	=> X_Plx_Item::TYPE_SEARCH,
				'icon'	=>	'/images/icons/back.png'
			);
			
			
			$return[] =	array(
				'label'	=>	X_Env::_('p_controls_forward_30'),
				'link'	=>	X_Env::completeUrl($urlHelper->url(array(
						'controller'		=>	'controls',
						'action'			=>	'execute',
						'a'					=>	'forward',
						'pid'				=>	$this->getId(),
						'param'				=>	'30' // this will replaced by wiimc....
					), 'default', false)
				),
				'type'	=> X_Plx_Item::TYPE_SEARCH,
				'icon'	=>	'/images/icons/forward.png'
			);
			
		}
		
		
		return $return;
	
	}
	
	
	/**
	 * Execute the action
	 * 
	 * @param X_Vlc $vlc
	 * @param string $pid
	 * @param string $action
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 */
	public function execute(X_Vlc $vlc, $pid, $action, Zend_Controller_Action $controller) {
		// the trigger isn't for this plugin
		if ( $this->getId() != $pid ) return;
		
		X_Debug::i("Plugin triggered for action {$action}");
		
		$param = $controller->getRequest()->getParam('param', null);
		
		if ( method_exists($this, "_action_$action") ) {
			$method = "_action_$action";
			$this->$method($vlc, $param);
		} else {
			X_Debug::e("Invalid action $action");
		}
		
		$controller->getRequest()->setControllerName('controls')->setActionName('control')->setDispatched(false);
		
	}
	
	/**
	 * Add the link for -manage-output-
	 * @param Zend_Controller_Action $this
	 * @return array The format of the array should be:
	 * 		array(
	 * 			array(
	 * 				'title' => ITEM TITLE,
	 * 				'label' => ITEM LABEL,
	 * 				'link'	=> HREF,
	 * 				'highlight'	=> true|false,
	 * 				'icon'	=> ICON_HREF,
	 * 				'subinfos' => array(INFO, INFO, INFO)
	 * 			), ...
	 * 		)
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_controls_mlink'));
		$link->setTitle(X_Env::_('p_controls_managetitle'))
			->setIcon('/images/icons/controls.png')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'controls'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
	
	}
	
	/**
	 * Add the link -go-to-controls-page- in stream page
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to stream
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array 
	 */
	public function getStreamItems($provider, $location, Zend_Controller_Action $controller) {
		$urlHelper = $controller->getHelper('url');

		return array(
			array(
				'label'	=>	X_Env::_('p_controls_gotocontrols'),
				'link'	=>	X_Env::completeUrl($urlHelper->url(array(
						'controller'		=>	'controls',
						'action'			=>	'control',
					), 'default', false)
				),
				'icon'	=>	'/images/icons/controls.png'
			),
		);
		
	}
		
	
	private function _action_stop(X_Vlc $vlc, $param) {
		$vlc->forceKill();
		sleep(1); // wait here so i will get "no vlc running" when i'll try later
	}
	
	private function _action_seek(X_Vlc $vlc, $param) {
		
		$time = ((int) $param) * 60;
		
		$totalTime = $vlc->getTotalTime();
		
		if ( $time >= 0 && $time <= $totalTime) {
			$vlc->seek($time);
		} else {
			X_Debug::w("Time value out of range: $time vs $totalTime");
		}
	}

	private function _action_forward(X_Vlc $vlc, $param) {
		
		$time = ((int) $param) * 60;
		
		$vlc->seek($time, true);
	}
	
	private function _action_back(X_Vlc $vlc, $param) {
		
		$time = abs(((int) $param) * 60) * -1;
		
		$vlc->seek($time, true);
	}
	
	private function _action_pause(X_Vlc $vlc, $param) {
		
		$vlc->pause();
		
	}
	
	
}
