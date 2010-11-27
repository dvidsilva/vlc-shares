<?php

/**
 * Fallback controller triggered when is called an invalid image url
 */
class ImagesController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    	parent::init();
    	if ( $this->getRequest()->getActionName() != 'no-image.png' ) {
    		$this->getResponse()->setHeader('Content-Type', 'image/png');
    		$this->_helper->redirector('no-image.png', 'images');
    	}
    }
	
}

