<?php 

require_once 'X/VlcShares/Skins/Default/Abstract.php';
require_once 'X/Page/Item/ManageLink.php';

class X_VlcShares_Skins_Default_XApplicationButton extends X_VlcShares_Skins_Default_Abstract {
	
	/**
	 * Wrap the header
	 * 
	 * @param string $content content to decorate
	 * @param stdClass $options decorator options
	 */
	public function decorate($content, $options) {

		if ( !($content instanceof X_Page_Item_ManageLink) ) {
			throw new Exception('Invalid content');
		}

		/* @var $urlHelper Zend_View_Helper_Url */
		$urlHelper = $this->view->getHelper('url');
		/* @var $baseUrlHelper Zend_View_Helper_BaseUrl */
		$baseUrlHelper = $this->view->getHelper('baseUrl');
		
		$highlight = $content->isHighlight() ? 'selected' : '';
		$url = $urlHelper->url($content->getLink(), $content->getRoute(), $content->isReset());
		$imageAlt = $content->getTitle() . " - " . $content->getLabel();
		$imageSrc = $baseUrlHelper->baseUrl($content->getIcon() != null ? $content->getIcon() : '/images/manage/plugin.png' );
		
		$span = $this->wrap($content->getTitle(), 'span');
		$image = $this->wrap('', 'img',array(
				"alt=\"$imageAlt\"",
				"src=\"$imageSrc\""
			));
		
		$content = $this->recursiveWrap($image.$span, array(
			array('div', "class=\"topnav-button $highlight\""),
			array('a', "href=\"$url\""),
		));
		
		return $content;
	}
	
}
