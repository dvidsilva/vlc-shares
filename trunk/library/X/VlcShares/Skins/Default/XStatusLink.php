<?php 

require_once 'X/VlcShares/Skins/Default/Abstract.php';
require_once 'X/Page/Item/StatusLink.php';

class X_VlcShares_Skins_Default_XStatusLink extends X_VlcShares_Skins_Default_Abstract {
	
	/**
	 * Wrap the header
	 * 
	 * @param string $content content to decorate
	 * @param stdClass $options decorator options
	 */
	public function decorate($content, $options) {

		if ( !($content instanceof X_Page_Item_StatusLink) ) {
			throw new Exception('Invalid content');
		}

		/* @var $urlHelper Zend_View_Helper_Url */
		$urlHelper = $this->view->getHelper('url');
		/* @var $baseUrlHelper Zend_View_Helper_BaseUrl */
		$baseUrlHelper = $this->view->getHelper('baseUrl');
		
		switch ($content->getType() ) {
			case X_Page_Item_StatusLink::TYPE_BUTTON:
				$url = $urlHelper->url($content->getLink(), $content->getRoute(), $content->isReset());
				$content = $this->view->xButton($content->getLabel(), $url)
								->setDimension(X_VlcShares_View_Helper_XButton::DIMENSION_SMALL)
								->setVariant($content->isHighlight() ? X_VlcShares_View_Helper_XButton::VARIANT_HIGHLIGHT : null ); 
				break;
			case X_Page_Item_StatusLink::TYPE_LABEL:
				$content = $content->getLabel();
				break;
			case X_Page_Item_StatusLink::TYPE_LINK:
				$url = $urlHelper->url($content->getLink(), $content->getRoute(), $content->isReset());
				$content = $this->wrap($content->getLabel(), 'a', "href=\"$url\"");
				break;
		}
		
		return $content;
	}
	
}
