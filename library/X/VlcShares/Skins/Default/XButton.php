<?php 

require_once 'X/VlcShares/Skins/Default/Abstract.php';

class X_VlcShares_Skins_Default_XButton extends X_VlcShares_Skins_Default_Abstract {
	
	/**
	 * Create a button
	 * 
	 * @param string $content content to decorate
	 * @param stdClass $options decorator options
	 */
	public function decorate($content, $options) {

		$variant = $options->variant;
		$dimension = $options->dimension;
		
		$label = $content->label;
		$url = $content->url;

		switch ($variant) {
			case X_VlcShares_View_Helper_XButton::VARIANT_NONE: $variant = 'blue';
				break;
			case X_VlcShares_View_Helper_XButton::VARIANT_HIGHLIGHT: $variant = 'red';
				break;
			case X_VlcShares_View_Helper_XButton::VARIANT_DISABLED: $variant = 'disabled';
				break;
			default:;
		}
			
		if ( $variant == X_VlcShares_View_Helper_XButton::VARIANT_DISABLED ) {
			$content = $this->recursiveWrap($label, array(
				array('span', "href=\"$url\" class=\"button $variant $dimension\""),
				array('span')
			));
		} else {
			$content = $this->recursiveWrap($label, array(
				array('a', "href=\"$url\" class=\"button $variant $dimension\""),
				array('span')
			));
		}
		
		return $content;
	}
	
}
