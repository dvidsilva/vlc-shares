<?php 

require_once 'X/VlcShares/Skins/Default/Abstract.php';

class X_VlcShares_Skins_Default_XHeader extends X_VlcShares_Skins_Default_Abstract {
	
	/**
	 * Wrap the header
	 * 
	 * @param string $content content to decorate
	 * @param stdClass $options decorator options
	 */
	public function decorate($content, $options) {

		/*
		$content = $this->recursiveWrap($content, array(
			array('div', "class=\"block $variant $title $with_header\""),
			array('div', "class=\"inner\"")
		));
		
		return $content;
		*/
		
		return $this->wrap($content, 'div', 'class="header"');
	}
	
}
