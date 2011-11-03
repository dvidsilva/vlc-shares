<?php


class PublicController extends X_Controller_Action {

	function init() {}
	
	function preDispatch() {
    	// uses the device helper for wiimc recognition
		if ( X_VlcShares_Plugins::helpers()->devices()->isWiimc() ) {
			// wiimc 1.0.9 e inferiori nn accetta redirect
			//$this->_forward('collections', 'index');
			$this->getRequest()->setControllerName('index')->setActionName('collections')->setDispatched(false);
		} else {
			$this->_helper->redirector('index','index');
		}
	}

}