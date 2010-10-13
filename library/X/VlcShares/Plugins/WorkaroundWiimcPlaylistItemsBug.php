<?php

require_once ('X/VlcShares/Plugins/Abstract.php');

/** 
 * @author ximarx
 * 
 * Workaround for bug in wiimc:
 * if playlist items number is 1,
 * wiimc load the url as a video
 * even if the url is a playlist item.
 * This bug is flagged as fixed in 1.0.9+
 * 
 */
class X_VlcShares_Plugins_WorkaroundWiimcPlaylistItemsBug extends X_VlcShares_Plugins_Abstract {
	
	function __construct() {
		// gen_afterPageBuild must be called as first
		$this->setPriority('gen_afterPageBuild', 0);
	}
	
	public function gen_afterPageBuild(&$items, Zend_Controller_Action $controller) {
		if ( $this->helpers()->devices()->isWiimc() && $this->helpers()->devices()->isWiimcBeforeVersion('1.0.9') ) {
			if ( count($items) === 1 ) {
				X_Debug::i("Plugin triggered");
				$items[] = array(
					'label' => '-- Workaround for bug in Wiimc <= 1.0.9 --',
					'link' => X_Env::completeUrl($controller->getHelper('url')->url())
				);
			}
		}
	}
}
