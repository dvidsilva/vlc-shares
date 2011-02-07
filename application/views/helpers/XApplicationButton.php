<?php 

class X_VlcShares_View_Helper_XApplicationButton extends Zend_View_Helper_Abstract {
	
	/**
	 * decorate a managelink
	 * @return string
	 */
	public function xApplicationButton($content) {
		
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

	/**
	 * @param string $content
	 * @param string|array $params if string, it will be appended to tag definition.
	 * 			if array, it will imploded and appended to tag definition
	 * @return string;
	 */
	protected function wrap($content, $tag, $params = '') {
		if ( is_array($params) ) {
			$params = implode(' ', $params);
		}
		if ( $params != '' ) $params = ' '.$params;
		return "<{$tag}{$params}>\n\t{$content}\n</{$tag}>\n";
	}
	
	/**
	 * @param string $content
	 * @param array	$array a list of array($tag, $params) that will be forwarded to wrap
	 * @return string
	 */
	protected function recursiveWrap($content, $array) {
		$array = array_reverse($array);
		foreach ($array as $single) {
			if ( !is_array($single) ) continue;
			@list($tag, $params) = $single;
			$content = $this->wrap($content, $tag, $params);
		}
		return $content;
	}
	
}