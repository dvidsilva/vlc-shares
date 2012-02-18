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
	 * @param X_Streamer_Engine $engine streamer engine
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return X_Page_ItemList_PItem 
	 */
	public function getControlItems(X_Streamer_Engine $engine, Zend_Controller_Action $controller) {
	
		$urlHelper = $controller->getHelper('url');
		
		$return = new X_Page_ItemList_PItem();
		
		if ( $this->config('stop.enabled', true)) {
			// stop
			$item = new X_Page_Item_PItem('controls-stop', X_Env::_('p_controls_stop'));
			$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setIcon('/images/icons/stop.png')
				->setLink(array(
					'controller'		=>	'controls',
					'action'	=>	'execute',
					'a'			=>	'stop',
					'pid'		=>	$this->getId(),
			), 'default', false);
			$return->append($item);
		}
		
		if ( $engine instanceof X_Streamer_Engine_Vlc ) {

			if ( $this->config('pauseresume.enabled', false)) {
				// pause/resume
				$item = new X_Page_Item_PItem('controls-pauseresume', X_Env::_('p_controls_pauseresume'));
				$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setIcon('/images/icons/pause.png')
					->setLink(array(
						'controller'		=>	'controls',
						'action'	=>	'execute',
						'a'			=>	'pause',
						'pid'		=>	$this->getId(),
					), 'default', false);
				$return->append($item);
			}
			if ( $this->config('forwardrelative.enabled', false)) {
				// forward relative
				$item = new X_Page_Item_PItem('controls-forwardcustom', X_Env::_('p_controls_forwardcustom'));
				$item->setType(X_Page_Item_PItem::TYPE_REQUEST)
					->setIcon('/images/icons/forward.png')
					->setDescription(X_Env::_('p_controls_forwardcustom_desc'))
					->setLink(array(
						'controller'		=>	'controls',
						'action'			=>	'execute',
						'a'					=>	'forward',
						'pid'				=>	$this->getId(),
						'param'				=>	'' // this will replaced by wiimc....
					), 'default', false);
				$return->append($item);
			}
			if ( $this->config('backrelative.enabled', false)) {
				// rewind relative
				$item = new X_Page_Item_PItem('controls-backcustom', X_Env::_('p_controls_backcustom'));
				$item->setType(X_Page_Item_PItem::TYPE_REQUEST)
					->setIcon('/images/icons/back.png')
					->setDescription(X_Env::_('p_controls_backcustom_desc'))
					->setLink(array(
						'controller'		=>	'controls',
						'action'			=>	'execute',
						'a'					=>	'back',
						'pid'				=>	$this->getId(),
						'param'				=>	'' // this will replaced by wiimc....
					), 'default', false);
				$return->append($item);
			}
			if ( $this->config('seek.enabled', true)) {
				// seek to time
				$item = new X_Page_Item_PItem('controls-seek', X_Env::_('p_controls_seektominute'));
				$item->setType(X_Page_Item_PItem::TYPE_REQUEST)
					->setIcon('/images/icons/seek.png')
					->setDescription(X_Env::_('p_controls_seektominute_desc'))
					->setLink(array(
						'controller'		=>	'controls',
						'action'			=>	'execute',
						'a'					=>	'seek',
						'pid'				=>	$this->getId(),
						'param'				=>	'' // this will replaced by wiimc....
					), 'default', false);
				$return->append($item);
			}
			
			if ( $this->config('oldstylecontrols.enabled', false) ) {
				
				$item = new X_Page_Item_PItem('controls-back5', X_Env::_('p_controls_back_5'));
				$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setIcon('/images/icons/back.png')
					->setLink(array(
						'controller'		=>	'controls',
						'action'			=>	'execute',
						'a'					=>	'back',
						'pid'				=>	$this->getId(),
						'param'				=>	'5' // this will replaced by wiimc....
					), 'default', false);
				$return->append($item);
	
				$item = new X_Page_Item_PItem('controls-forward5', X_Env::_('p_controls_forward_5'));
				$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setIcon('/images/icons/forward.png')
					->setLink(array(
						'controller'		=>	'controls',
						'action'			=>	'execute',
						'a'					=>	'forward',
						'pid'				=>	$this->getId(),
						'param'				=>	'5' // this will replaced by wiimc....
					), 'default', false);
				$return->append($item);
	
							$item = new X_Page_Item_PItem('controls-back30', X_Env::_('p_controls_back_30'));
				$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setIcon('/images/icons/back.png')
					->setLink(array(
						'controller'		=>	'controls',
						'action'			=>	'execute',
						'a'					=>	'back',
						'pid'				=>	$this->getId(),
						'param'				=>	'30' // this will replaced by wiimc....
					), 'default', false);
				$return->append($item);
	
				$item = new X_Page_Item_PItem('controls-forward30', X_Env::_('p_controls_forward_30'));
				$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setIcon('/images/icons/forward.png')
					->setLink(array(
						'controller'		=>	'controls',
						'action'			=>	'execute',
						'a'					=>	'forward',
						'pid'				=>	$this->getId(),
						'param'				=>	'30' // this will replaced by wiimc....
					), 'default', false);
				$return->append($item);
				
			}
		
		}
		
		return $return;
	
	}
	
	
	/**
	 * Execute the action
	 * 
	 * @param X_Streamer_Engine $vlc
	 * @param string $pid
	 * @param string $action
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 */
	public function execute(X_Streamer_Engine $engine, $pid, $action, Zend_Controller_Action $controller) {
		// the trigger isn't for this plugin
		if ( $this->getId() != $pid ) return;
		
		X_Debug::i("Plugin triggered for action {$action}");
		
		$param = $controller->getRequest()->getParam('param', null);
		
		if ( method_exists($this, "_action_$action") ) {
			$method = "_action_$action";
			if ( $action === 'stop' ) {
				$this->$method($engine, $param);
			} else {
				if ( $engine instanceof X_Streamer_Engine_Vlc ) {
					$this->$method($engine->getVlcWrapper(), $param);
				}
			}
		} else {
			X_Debug::e("Invalid action $action");
		}
		
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
	 * Add the go-to-control-page
	 * 
	 * @param X_Streamer_Engine $engine selected streamer engine
	 * @param string $uri
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to stream
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return X_Page_ItemList_PItem 
	 */
	public function getStreamItems(X_Streamer_Engine $engine, $uri, $provider, $location, Zend_Controller_Action $controller) {
		$urlHelper = $controller->getHelper('url');

		$item = new X_Page_Item_PItem($this->getId(), X_Env::_('p_controls_gotocontrols'));
		$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setIcon('/images/icons/controls.png')
			->setLink(array(
						'controller'		=>	'controls',
						'action'			=>	'control',
					), 'default', false);
		return new X_Page_ItemList_PItem(array($item));
		
	}
		
	
	private function _action_stop(X_Streamer_Engine $engine, $param) {
		X_Streamer::i()->stop();
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
