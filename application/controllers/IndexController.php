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
    	// links on top
    	$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->preGetCollectionsItems($this));
    	// normal links
    	$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->getCollectionsItems($this));
    	// bottom links
		$pageItems = array_merge($pageItems, X_VlcShares_Plugins::broker()->postGetCollectionsItems($this));
		
		// filter out items (parental-control / hidden file / system dir)
		foreach ($pageItems as $key => $item) {
			if ( in_array(false, X_VlcShares_Plugins::broker()->filterCollectionsItems($item, $this)) ) {
				unset($pageItems[$key]);
			}
		}
		
		// trigger for page creation
		X_VlcShares_Plugins::broker()->gen_afterPageBuild(&$pageItems, $this);
    	
    }
	public function pcAction() {
		
	}

}

