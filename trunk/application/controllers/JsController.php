<?php

/**
 * Fallback controller triggered when is called an invalid image url
 */
class JsController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    	parent::init();
    	if ( $this->getRequest()->getActionName() != 'no-js.js' ) {
    		$this->_helper->redirector('no-js.js', 'js');
    	}
    }
	
}

