<?php

require_once 'Zend/Controller/Action.php';
require_once 'Zend/Config/Ini.php';
require_once 'Zend/Config.php';
require_once 'Zend/Translate.php';
require_once 'X/Env.php';
require_once 'X/VlcShares.php';
require_once 'X/Plx.php';
require_once 'X/Plx/Item.php';


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
		
		X_VlcShares_Plugins::broker()->gen_beforeInit($this);		
		
		$this->bootstrap = $this->getFrontController()->getParam('bootstrap');
    	$this->options = $this->bootstrap->getResource('configs'); 
    	$this->translate = $this->bootstrap->getResource('translation');
    	
    	X_VlcShares_Plugins::broker()->gen_afterInit($this);
    	
	}
	
	public function preDispatch() {
		X_Debug::i("Required action: [".$this->getRequest()->getControllerName() . '/' . $this->getRequest()->getActionName() .']');
		parent::preDispatch();
		
		// call plugins trigger
		// TODO check if plugin broker should be called before parent::preDispatch
		X_VlcShares_Plugins::broker()->gen_beforePageBuild($this);

		//$this->_helper->url->url()
		
	}
	
}
