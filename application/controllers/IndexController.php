<?php


class IndexController extends X_Controller_Action
{

    public function indexAction()
    {
		if ( strpos($_SERVER['HTTP_USER_AGENT'], 'WiiMC') !== false ) {
			// wiimc 1.0.5 e inferiori nn accetta redirect
			$this->_forward('collections');
		} else {
			$this->_helper->redirector('index','manage');
		}
    }

    public function collectionsAction() {
    	
    	$pageItems = array();
    	
    	$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->preGetCollectionsItems($this));
    	
		/*
    	$plx = new X_Plx('VLCShares - '.X_Env::_('Collections'), X_Env::_("title_description"));
    	
    	$shares = $this->options->get('shares')->toArray();
    	
		foreach ( $shares as $key => $share ) {
			
			$plx->addItem(new X_Plx_Item($share['name'], 
					X_Env::routeLink('browse', 'share', array('shareId' => $key))));
			
		}

		// aggiungo altre entry date dai plugin
		$pluginsOutput = X_Env::triggerEvent(X_VlcShares::TRG_COLLECTIONS_INDEX);
		foreach ($pluginsOutput as $item) {
			if ( $item instanceof X_Plx_Item )
				$plx->addItem($item);
		}
		*/
    	
    	$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->getCollectionsItems($this));
    	
		
		$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->postGetCollectionsItems($this));
		
		//$this->view->entries = $entries;
		//$this->view->translate = $this->translate;
		
		/*

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
				
		// disabilita il rendering
		$this->_helper->viewRenderer->setNoRender(true);
    	*/
		
		X_VlcShares_Plugins::broker()->gen_afterPageBuild($pageItems, $this);
    	
    }
	public function pcAction() {
		
	}

}

