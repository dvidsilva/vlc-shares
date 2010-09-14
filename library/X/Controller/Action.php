<?php

require_once 'Zend/Controller/Action.php';
require_once 'Zend/Config/Ini.php';
require_once 'Zend/Config.php';
require_once 'Zend/Translate.php';
require_once 'X/Env.php';
require_once 'X/VlcShares.php';

class X_Controller_Action extends Zend_Controller_Action {

	/**
	 * 
	 * @var Zend_Config
	 */
	protected $options = null;
	
	/**
	 * @var $translate Zend_Transate
	 */
	protected $translate = null;
	
	/**
	 * 
	 * @var Bootstrap
	 */
	protected $bootstrap = null;
	
	public function init() {
		
		$this->bootstrap = $this->getFrontController()->getParam('bootstrap');
    	$this->options = $this->bootstrap->getResource('configs'); 
    	$this->translate = $this->bootstrap->getResource('translation');
				
	}
	
}
