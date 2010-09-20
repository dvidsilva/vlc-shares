<?php

class ManageController extends X_Controller_Action
{
	protected $vlc = null;

    public function init()
    {
        /* Initialize action controller here */
    	parent::init();
    	$this->vlc = new X_Vlc($this->options->vlc);
    }

    public function indexAction()
    {
		$pluginsConfs = X_Env::triggerEvent(X_VlcShares::TRG_MANAGE_PLUGINS_CONFS);
		$pluginsLinks = X_Env::triggerEvent(X_VlcShares::TRG_MANAGE_PLUGINS_LINKS);
		
		$this->view->version = X_VlcShares::VERSION;
		$this->view->configPath = X_VlcShares::config();
		$this->view->test = $this->_helper->url('index', 'test');
		$this->view->pcstream = $this->_helper->url('pcstream', 'index');
		$this->view->pcstream_enabled = ($this->options->pcstream->get('commanderEnabled', false) && $this->vlc->isRunning());
		$this->view->pluginsConfs = $pluginsConfs;
		$this->view->pluginsLinks = $pluginsLinks;
    	
    }
}

