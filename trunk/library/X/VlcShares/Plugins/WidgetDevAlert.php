<?php

require_once 'X/VlcShares/Plugins/Abstract.php';


/**
 * This is a soc plugin:
 * It's an example of how to add warning messages in dashboard
 * This plugin only produce allert message for the dashboard if this is a dev release
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_WidgetDevAlert extends X_VlcShares_Plugins_Abstract {
	
	public function __construct() {
		$this->setPriority('getIndexMessages', 1); // i add it near the top of the stack
	}
	
	/**
	 * Retrieve statistic from plugins
	 * @param Zend_Controller_Action $this
	 * @return array The format of the array should be:
	 * 		array(
	 * 			array(
	 * 				'title' => ITEM TITLE,
	 * 				'label' => ITEM LABEL,
	 * 				'stats' => array(INFO, INFO, INFO),
	 * 				'provider' => array('controller', 'index', array()) // if provider is setted, stats key is ignored 
	 * 			), ...
	 * 		)
	 */
	public function getIndexMessages(Zend_Controller_Action $controller) {
		
		$version = X_VlcShares::VERSION;
		
		X_Debug::i('Plugin triggered');
		
		$showError = false;
		$type = 'warning';
		
		if ( strpos($version, 'alpha') !== false ||
				strpos($version, 'beta') !== false ||
				strpos($version, 'dev') !== false ||
				strpos($version, 'unstable') !== false ) {
		 	$showError = true;
		 	
		} elseif ( 	strpos($version, 'rc') !== false ||
		 		strpos($version, 'release_candidate') !== false ) {
			$showError = true;
			$type = 'info';
		}
		
		if ( $showError ) {
			/*
			return array(
				array(
					'type' => $type,
					'text' => X_Env::_('p_widgetdevalert_warningmessage')
				),
			);
			*/
			// Ported to new api
			$m = new X_Page_Item_Message($this->getId(), X_Env::_('p_widgetdevalert_warningmessage'));
			$m->setType($type);
			return new X_Page_ItemList_Message(array($m));
		}
		
	}
	
	
}
