<?php

require_once 'X/VlcShares/Plugins/Abstract.php';


/**
 * This is a soc plugin:
 * It's an example of how to add warning messages in dashboard
 * This plugin only produce allert message for the dashboard if this is a dev release
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_WidgetDevNews extends X_VlcShares_Plugins_Abstract {
	
	public function __construct() {
		$this->setPriority('getIndexNews'); // i add it near the top of the stack
	}
	
	/**
	 * Retrieve news from plugins
	 * @param Zend_Controller_Action $this
	 * @return array The format of the array should be:
	 * 		array(
	 * 			array(
	 * 				'tab'	=> TAB LABEL
	 * 				'title' => ITEM TITLE,
	 * 				'text' => HTML STYLIZED TEXT 
	 * 			), ...
	 * 		)
	 */
	public function getIndexNews(Zend_Controller_Action $controller) {
	
		try {
			$view = $controller->getHelper('viewRenderer');
			
			$view->view->headScript()->appendFile('http://www.google.com/jsapi');
			$view->view->headScript()->appendFile($view->view->baseUrl("/js/widgetdevnews/script.js"));
			
			$text = include(dirname(__FILE__).'/WidgetDevNews.commits.phtml');
			
			return array(
				array(
					'tab'	=> X_Env::_('p_widgetdevnews_commits_tab'),
					'title' => X_Env::_('p_widgetdevnews_commits_title'),
					'text'	=> $text
				),
			);
		} catch (Exception $e) {
			X_Debug::e('No view O_o');
		}
	}
	
	
}
