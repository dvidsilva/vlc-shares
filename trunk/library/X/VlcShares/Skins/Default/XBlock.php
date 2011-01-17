<?php 

require_once 'X/VlcShares/Skins/Default/Abstract.php';

class X_VlcShares_Skins_Default_XBlock extends X_VlcShares_Skins_Default_Abstract {
	
	/**
	 * Wrap the block
	 * 
	 * @param string $content content to decorate
	 * @param stdClass $options decorator options
	 */
	public function decorate($content, $options) {
		
		$title = $options->title;
		$variant = $options->variant;
		$with_header = $options->header;
		
		if ( $title !== false ) {
			$content = "<h1>$title</h1>".PHP_EOL
						.$content;
			$title = 'titled';
		} else {
			$title = '';
		}
		
		switch ($variant) {
			case X_VlcShares_View_Helper_XBlock::VARIANT_NONE: $variant = '';
				break;
			case X_VlcShares_View_Helper_XBlock::VARIANT_HIGHLIGHT: $variant = 'red';
				break;
			case X_VlcShares_View_Helper_XBlock::VARIANT_DISABLED: $variant = 'gray';
				break;
			default:
				break;
		}
		
		$with_header = $with_header ? 'with-header' : '';
		
		$content = $this->recursiveWrap($content, array(
			array('div', "class=\"block $variant $title $with_header\""),
			array('div', "class=\"inner\"")
		));
		
		return $content;
	}
	
}
