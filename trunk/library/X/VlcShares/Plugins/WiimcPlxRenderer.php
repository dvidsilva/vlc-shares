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
		$this->setPriority('gen_afterPageBuild');
	}
	
	public function gen_afterPageBuild(&$items, Zend_Controller_Action $controller) {
		//if ( !$this->helpers()->devices()->isWiimc() ) return;
		
		X_Debug::i("Plugin triggered");

		$plx = new X_Plx('VLCShares - '.X_Env::_('Collections'), X_Env::_("title_description"));
		
		foreach ( $items as $i => $item ) {
			$plx->addItem(new X_Plx_Item($item['label'], $item['link']));
		}
		
		$this->_render($plx, $controller);
	}
	
	/**
	 * Send to output plx playlist
	 * @param X_Plx $plx
	 */
	private function _render(X_Plx $plx, Zend_Controller_Action $controller) {
		$this->_disableRendering($controller);
		$controller->getResponse()->setHeader('Content-type', 'text/plain', true);
		$controller->getResponse()->setBody((string) $plx);
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


