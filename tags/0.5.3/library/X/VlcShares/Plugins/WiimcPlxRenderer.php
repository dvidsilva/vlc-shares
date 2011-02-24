<?php

require_once ('X/VlcShares/Plugins/Abstract.php');
require_once ('X/Plx.php');
require_once ('X/Plx/Item.php');

/**
 * 
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_WiimcPlxRenderer extends X_VlcShares_Plugins_Abstract {

	function __construct() {
		$this->setPriority('gen_afterPageBuild')
		->setPriority('gen_beforePageBuild')
		->setPriority('getIndexManageLinks');
	}
	
	/**
	 * Show an wiimc compatible error page in plx format
	 */
	function gen_beforePageBuild(Zend_Controller_Action $controller) {
		if ( !((bool) $this->config('forced.enabled', false)) && !$this->helpers()->devices()->isWiimc() ) return;
		
		$controllerName = $controller->getRequest()->getControllerName();
		$actionName = $controller->getRequest()->getControllerName();
		
		if ( "$controllerName/$actionName" != "error/error") return;

		X_Debug::i("Plugin triggered");
		
		try {
			$cachePlugin = X_VlcShares_Plugins::broker()->getPlugins('cache');
			if ( method_exists($cachePlugin, 'setDoNotCache' ) ) {
				$cachePlugin->setDoNotCache();
			}
		} catch (Exception $e) {}
		
		// setting request as dispatched prevent action execution
		$controller->getRequest()->setDispatched(true);
		
		/* @var $urlHelper Zend_Controller_Action_Helper_Url */
		$urlHelper = $controller->getHelper('url');
		
		
		
        $errors = $controller->getRequest()->getParam('error_handler');

        $view = new stdClass();
        
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
        
                // 404 error -- controller or action not found
                //$this->getResponse()->setHttpResponseCode(404);
                $view->message = 'Page not found';
                break;
            default:
                // application error
                //$this->getResponse()->setHttpResponseCode(500);
                $view->message = 'Application error';
                break;
        }
        
        X_Debug::f($view->message . ": " . $errors->exception->getMessage());
        X_Debug::f($errors->exception->getTraceAsString());
        
		$view->exception = $errors->exception;
            
		$plx = new X_Plx(
			X_Env::_('p_wiimcplxrenderer_plxtitle_error_error'),
			X_Env::_('p_wiimcplxrenderer_plxdescription_error_error')
		);
		
		$plx->addItem(new X_Plx_Item(X_Env::_('p_wiimcplxrenderer_plxerror_title', $view->message),X_Env::completeUrl($urlHelper->url()))); 
		
		$plx->addItem(new X_Plx_Item(X_Env::_('p_wiimcplxrenderer_plxerror_message', $errors->exception->getMessage()), X_Env::completeUrl($urlHelper->url())));
		
		$plx->addItem(new X_Plx_Item(X_Env::_('p_wiimcplxrenderer_plxerror_stacktrace_separator'),X_Env::completeUrl($urlHelper->url())));
		
		$stacktrace = explode("\n", $errors->exception->getTraceAsString());
		
		foreach ($stacktrace as $i => $trace) {
			$plx->addItem(new X_Plx_Item(X_Env::_('p_wiimcplxrenderer_plxerror_trace', $i, $trace), X_Env::completeUrl($urlHelper->url())));
		}
		
		$plx->addItem(new X_Plx_Item(X_Env::_('p_wiimcplxrenderer_plxerror_request_separator'),X_Env::completeUrl($urlHelper->url())));
        
		$params = $errors->request->getParams();
		
		foreach ($params as $key => $value) {
			$plx->addItem(new X_Plx_Item(X_Env::_('p_wiimcplxrenderer_plxerror_param', $key, $value), X_Env::completeUrl($urlHelper->url())));
		}
        
		$this->_render($plx, $controller);
		
		$controller->getResponse()->sendResponse();
		
		// the execution will stop here!
		// or zf will send in header code 500 and wiimc will not parse the response
		exit;
		
	}
	
	public function gen_afterPageBuild(X_Page_ItemList_PItem $list, Zend_Controller_Action $controller) {
		if ( !((bool) $this->config('forced.enabled', false)) && !$this->helpers()->devices()->isWiimc() ) return;
		
		X_Debug::i("Plugin triggered");

		$request = $controller->getRequest();
		
		$enhanced = $this->helpers()->devices()->isWiimcEnhanced() && $this->config('support.enhanced', true);
		
		$plx = new X_Plx(
			X_Env::_('p_wiimcplxrenderer_plxtitle_'.$request->getControllerName().'_'.$request->getActionName()),
			X_Env::_('p_wiimcplxrenderer_plxdescription_'.$request->getControllerName().'_'.$request->getActionName())
		);
		
		// wiimc plus custom tags
		if ( $enhanced ) {
			$plx->setWiimcplus_generator_name('vlc-shares'); // uses the __call api
			$plx->setWiimcplus_generator_version(X_VlcShares::VERSION_CLEAN); // uses the __call api
			if ( $request->getControllerName() == 'index' && $request->getActionName() == 'collections' ) {
				$plx->setWiimcplus_assert_mainmenu('true'); // uses the __call api
			}
			// show the current time as custom playlist header tag if the page is controls/control
			if ( $request->getControllerName() == 'controls' && $request->getActionName() == 'control' ) {
				$vlc = X_Vlc::getLastInstance();
				if ( $vlc ) { // check to be sure that vlc is running right now
					$currentTime = X_Env::formatTime($vlc->getCurrentTime());
					$totalTime = X_Env::formatTime($vlc->getTotalTime());
					$plx->setWiimcplus_current_time("$currentTime/$totalTime"); // uses the __call api
				}
			} elseif ( $request->getControllerName() == 'browse' && $request->getActionName() == 'selection' ) {
				$plx->setWiimcplus_assert_nohistory('true'); // uses the __call api
			}
		}
		
		foreach ( $list->getItems() as $i => $item ) {
			/* @var $item X_Page_Item_PItem */
			$plxItemName = ($item->isHighlight() ? '-) ' : '' ). $item->getLabel();

			$plxItemWiimcplusIcon = null;
			
			switch ( $item->getType() ) {
				case X_Page_Item_PItem::TYPE_CONTAINER:
						$plxItemType = X_Plx_Item::TYPE_PLAYLIST;
						$plxItemWiimcplusIcon = 'folder';
						break;
				case X_Page_Item_PItem::TYPE_ELEMENT:
						$plxItemType = X_Plx_Item::TYPE_PLAYLIST;
						if ( $request->getControllerName() == 'browse' && $request->getActionName() == 'share' ) {
							$plxItemWiimcplusIcon = 'file';
						}
						break;
				case X_Page_Item_PItem::TYPE_REQUEST:
						$plxItemType = X_Plx_Item::TYPE_SEARCH;
						break;
				case X_Page_Item_PItem::TYPE_PLAYABLE:
						$plxItemType = X_Plx_Item::TYPE_VIDEO;
						break;
				default: $plxItemType = $item->getType();
			}
			
			/* @var $urlHelper Zend_Controller_Action_Helper_Url */
			$urlHelper = $controller->getHelper('url');
						
			$plxItemUrl = $item->isUrl() ? $item->getLink() : X_Env::completeUrl($urlHelper->url($item->getLink(), $item->getRoute(), $item->isReset()));
			
			$plxItem = new X_Plx_Item($plxItemName, $plxItemUrl, $plxItemType);
			
			if ( $item->getThumbnail() != null ) {
				if ( X_Env::startWith($item->getThumbnail(), 'http') || X_Env::startWith($item->getThumbnail(), 'https') ) {
					$plxItem->setThumb($item->getThumbnail());
				} else {
					$plxItem->setThumb(X_Env::completeUrl($item->getThumbnail()));
				}
			}
			
			if ( $enhanced ) {
				if ( $plxItemWiimcplusIcon !== null ) {
					$plxItem->setWiimcplus_icon($plxItemWiimcplusIcon); 
				}
				if ( $item->getKey() == 'core-separator' ) {
					$plxItem->setWiimcplus_assert_separator('true');
				}
				if ( $item->getKey() == 'core-directwatch' ) {
					$plxItem->setWiimcplus_assert_directwatch('true');
					if ( $item->getCustom('subtitle') != null ) {
						$plxItem->setWiimcplus_subtitle($item->getCustom('subtitle'));
					}
				}
				if ( $item->getKey() == 'core-play' ) {
					$plxItem->setWiimcplus_assert_startvlc('true');
				}
			}
			
			$plx->addItem($plxItem);
		}
		$this->_render($plx, $controller);
	}
	
	/**
	 * Add the link for -manage-wiimc-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_wiimcplxrenderer_mlink'));
		$link->setTitle(X_Env::_('p_wiimcplxrenderer_managetitle'))
			->setIcon('/images/wiimcplxrenderer/logo.png')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'wiimc'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
	}
	
	
	/**
	 * Send to output plx playlist
	 * @param X_Plx $plx
	 */
	private function _render(X_Plx $plx, Zend_Controller_Action $controller) {
		$this->_disableRendering($controller);
		// if isn't wiimc, add a conversion filter
		if ( !$this->helpers()->devices()->isWiimc() && $this->config('forced.fancy', true)) {
			$showRaw = $this->config('forced.showRaw', false);
			$showThumbs = $this->config('forced.showThumbs', true);
			$plxItems = $plx->getItems();
			$body = include(dirname(__FILE__).'/WiimcPlxRenderer.fancy.phtml');
		} else {
			$controller->getResponse()->setHeader('Content-type', 'text/plain', true);
			$body = (string) $plx;
		}
		$controller->getResponse()->setBody($body);
		return $body;
	}
	
	/**
	 * Disable layout and viewRenderer
	 * @param Zend_Controller_Action $controller
	 */
	private function _disableRendering(Zend_Controller_Action $controller) {
		try {
			$controller->getHelper('viewRenderer')->setNoRender(true);
			// disableLayout must be called at the end
			// i don't know if layout is enabled
			// and maybe an exception is raised if
			// i call e disableLayout without layout active
			$controller->getHelper('layout')->disableLayout();
		} catch (Exception $e) {
			X_Debug::w("Unable to disable viewRenderer or Layout: {$e->getMessage()}");
		}
	}
	
}


