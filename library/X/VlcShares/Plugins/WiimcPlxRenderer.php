<?php

require_once ('X/VlcShares/Plugins/Abstract.php');
require_once ('X/Plx.php');
require_once ('X/Plx/Item.php');

/**
 * 
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_WiimcPlxRenderer extends X_VlcShares_Plugins_Abstract {

	function __construct() {
		$this->setPriority('gen_afterPageBuild')
		->setPriority('getIndexManageLinks');
	}
	
	public function gen_afterPageBuild(&$items, Zend_Controller_Action $controller) {
		if ( !((bool) $this->config('forced.enabled', false)) && !$this->helpers()->devices()->isWiimc() ) return;
		
		X_Debug::i("Plugin triggered");

		$request = $controller->getRequest();
		
		$plx = new X_Plx(
			X_Env::_('p_wiimcplxrenderer_plxtitle_'.$request->getControllerName().'_'.$request->getActionName()),
			X_Env::_('p_wiimcplxrenderer_plxdescription_'.$request->getControllerName().'_'.$request->getActionName())
		);
		
		$enhanced = $this->helpers()->devices()->isWiimcEnhanced() && $this->config('support.enhanced', true);
		
		foreach ( $items as $i => $item ) {
			$plxItemName = (@$item['highlight'] ? '-) ' : '' ). $item['label'];
			$plxItemType = (array_key_exists('type', $item) ? $item['type'] : X_Plx_Item::TYPE_PLAYLIST );
			$plxItem = new X_Plx_Item($plxItemName, $item['link'], $plxItemType);
			
			// this adds support for enchanced version of wiimc
			if ( $enhanced && array_key_exists( 'itemType', $item) && $item['itemType'] == 'folder' ) {
				$plxItem->setType('folder');
			}
			
			// this adds thumb support for wiimc
			if ( array_key_exists('thumb', $item) ) {
				// i have to be sure that image address is a complete url, no relative url is allowed for wiimc
				if ( X_Env::startWith($item['thumb'], 'http') || X_Env::startWith($item['thumb'], 'https') ) {
					$plxItem->setThumb($item['thumb']);
				} else {
					$plxItem->setThumb(X_Env::completeUrl($item['thumb']));
				}
			}
			
			$plx->addItem($plxItem);
		}
		$this->_render($plx, $controller);
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

		$urlHelper = $controller->getHelper('url');
		
		return array(
			array(
				'title'		=>	X_Env::_('p_wiimcplxrenderer_managetitle'),
				'label'		=>	X_Env::_('p_wiimcplxrenderer_mlink'),
				'link'		=>	$urlHelper->url(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'wiimc'
				)),
				'icon'		=>	'/images/wiimcplxrenderer/logo.png',
				'subinfos'	=> array()
			),
		);
	}
	
	
	/**
	 * Send to output plx playlist
	 * @param X_Plx $plx
	 */
	private function _render(X_Plx $plx, Zend_Controller_Action $controller) {
		$this->_disableRendering($controller);
		// if isn't wiimc, add a conversion filter
		if ( !$this->helpers()->devices()->isWiimc() && $this->config('forced.fancy', true)) {
			$showRaw = $this->config('forced.showRaw', false);
			$showThumbs = $this->config('forced.showThumbs', true);
			$plxItems = $plx->getItems();
			$body = include(dirname(__FILE__).'/WiimcPlxRenderer.fancy.phtml');
		} else {
			$controller->getResponse()->setHeader('Content-type', 'text/plain', true);
			$body = (string) $plx;
		}
		$controller->getResponse()->setBody($body);
	}
	
	/**
	 * Disable layout and viewRenderer
	 * @param Zend_Controller_Action $controller
	 */
	private function _disableRendering(Zend_Controller_Action $controller) {
		try {
			$controller->getHelper('viewRenderer')->setNoRender(true);
			// disableLayout must be called at the end
			// i don't know if layout is enabled
			// and maybe an exception is raised if
			// i call e disableLayout without layout active
			$controller->getHelper('layout')->disableLayout();
		} catch (Exception $e) {
			X_Debug::w("Unable to disable viewRenderer or Layout: {$e->getMessage()}");
		}
	}
	
}


