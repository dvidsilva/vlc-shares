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
		->setPriority('getIndexManageLinks');
	}
	
	public function gen_afterPageBuild(X_Page_ItemList_PItem &$list, Zend_Controller_Action $controller) {
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
		}
		
		foreach ( $list->getItems() as $i => $item ) {
			/* @var $item X_Page_Item_PItem */
			$plxItemName = ($item->isHighlight() ? '-) ' : '' ). $item->getLabel();

			$plxItemWiimcplusIcon = null;
			
			switch ( $item->getType() ) {
				case X_Page_Item_PItem::TYPE_CONTAINER:
						// check for type=folder if wiimcplus
						$plxItemType = $enhanced ? 'folder' : X_Plx_Item::TYPE_PLAYLIST;
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


