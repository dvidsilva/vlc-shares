<?php

require_once 'X/VlcShares.php';
require_once 'X/Env.php';
require_once 'X/Controller/Action.php';
require_once 'X/Plx.php';

class PluginController extends X_Controller_Action
{

    public function init()
    {
		parent::init();
		X_Env::debug(__METHOD__);
    }

    public function indexAction()
    {
        $this->_forward('index', 'index');
    }

    public function execAction() {
    	
    	$id = $this->getRequest()->getParam('id', false);
    	if ( $id === false ) {
    		$this->_helper->viewRenderer->setNoRender(true);
    		return;
    	}
    	
    	$params = array (
    		'id' => $id,
    		'request' => $this->getRequest()
    	);
    	
    	$plxs = X_Env::triggerEvent(X_VlcShares::TRG_PLUGIN_PAGE, $params);
    	
    	foreach ($plxs as $_plx) {
    		if ( $_plx instanceof X_Plx ) {
    			$plx = $_plx;
    			break; 
    		}
    	}
    	
		$echoArrayPlg = X_Env::triggerEvent(X_VlcShares::TRG_ENDPAGES_OUTPUT_FILTER_PLX, $plx );
		$echo = '';
		foreach ($echoArrayPlg as $plgOutput) {
			$echo .= $plgOutput;
		}
		if ( $echo != '' ) {
			echo $echo;
		} else {
    		header('Content-Type:text/plain');
			echo $plx;
		}
		$this->_helper->viewRenderer->setNoRender(true);
    	
    }
}

