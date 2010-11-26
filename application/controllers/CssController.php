<?php

/**
 * Fallback controller triggered when is called an invalid image url
 */
class CssController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    	parent::init();
    	if ( $this->getRequest()->getActionName() != 'no-css.css' ) {
    		$this->_helper->redirector('no-css.css', 'css');
    	}
    }
	
}

