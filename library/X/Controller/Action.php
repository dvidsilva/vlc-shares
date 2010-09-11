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
	
	public function init() {
		
    	$this->options = new Zend_Config_Ini(X_VlcShares::config());
    	$this->translate = new Zend_Translate('ini', $this->options->general->get('languageFile', APPLICATION_PATH ."/../languages/en_GB.ini" ));
    	// INIT DEBUG
    	if ( $this->options->general->debug_enabled)
    		X_Env::initDebug($this->options->general->get('debug_path', sys_get_temp_dir().'/vlcShares.debug.log' ));
			
		if ( $this->options->general->apache_altPort ) {
			X_Env::initForcedPort($this->options->general->apache_altPort);
		}
    	
    	X_Env::debug(__METHOD__);
    	
    	// INIT TRANSLATOR
    	X_Env::initTranslator($this->translate );
    		
        // INIT PLUGIN SYSTEM
    	X_Env::initPlugins($this->options->plugins);
				
	}
	
}
