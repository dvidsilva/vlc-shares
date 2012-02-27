<?php


class IndexController extends X_Controller_Action
{

    public function indexAction()
    {
    	// uses the device helper for wiimc recognition
    	// maybe i will add a trigger here 
		if ( X_VlcShares_Plugins::helpers()->devices()->isWiimc() ) {
			// wiimc 1.0.9 e inferiori nn accetta redirect
			X_Debug::i("Forwarding...");
			$this->_forward('collections', 'index');
		} else {
			$this->_helper->redirector('index','manage');
		}
    }

    public function collectionsAction() {
    	
    	$pageItems = new X_Page_ItemList_PItem();
    	// links on top
    	$pageItems->merge(X_VlcShares_Plugins::broker()->preGetCollectionsItems($this));
    	// normal links
    	$pageItems->merge(X_VlcShares_Plugins::broker()->getCollectionsItems($this));
    	// bottom links
		$pageItems->merge(X_VlcShares_Plugins::broker()->postGetCollectionsItems($this));
		
		// filter out items (parental-control / hidden file / system dir)
		foreach ($pageItems->getItems() as $key => $item) {
			$results = X_VlcShares_Plugins::broker()->filterCollectionsItems($item, $this);
			if ( $results != null && in_array(false, $results) ) {
				//unset($pageItems[$key]);
				$pageItems->remove($item);
			}
		}
		
		// trigger for page creation
		X_VlcShares_Plugins::broker()->gen_afterPageBuild($pageItems, $this);
    	
    }
	public function pcAction() {
		
	}

}

