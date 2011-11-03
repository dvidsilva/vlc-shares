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
		$this->setPriority('getIndexNews');
	}
	
	/**
	 * Retrieve news from plugins
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_News
	 */
	public function getIndexNews(Zend_Controller_Action $controller) {
	
		try {
			$view = $controller->getHelper('viewRenderer');
			
			$view->view->headScript()->appendFile('http://www.google.com/jsapi');
			$view->view->headScript()->appendFile($view->view->baseUrl("/js/widgetdevnews/script.js"));
			$view->view->headLink()->appendStylesheet($view->view->baseUrl('/css/widgetdevnews/style.css'));
			
			$text = include(dirname(__FILE__).'/WidgetDevNews.commits.phtml');
			
			$item = new X_Page_Item_News($this->getId(), '');
			$item->setTab(X_Env::_('p_widgetdevnews_commits_tab'))
				->setContent($text);
			
			return new X_Page_ItemList_News(array($item));
			
		} catch (Exception $e) {
			X_Debug::e('No view O_o');
		}
	}
	
	
}
