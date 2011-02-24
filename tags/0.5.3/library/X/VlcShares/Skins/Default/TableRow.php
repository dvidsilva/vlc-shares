<?php 

/**
 * Wrap content with a <tr /> tag
 * 
 * Allowed options:
 * 		container.classes: extra classes args appended to container
 * 		container.args: extra args appended to class arg
 * 
 */
class X_VlcShares_Skins_Default_TableRow extends X_VlcShares_Skins_Default_Abstract {
	
	/**
	 * Wrap the content with a <tr /> tag
	 * 
	 * @param string $content content to decorate
	 * @param stdClass $options decorator options
	 */
	public function decorate($content, $options) {

		$variant = empty($this->decoratorOptions['variant']) ?  '' : 'class="'. $this->decoratorOptions['variant'] .'"';
		
		$content = $this->wrap($content, 'tr', $variant);
		
		return $content;
	}
	
	protected function getDefaultOptions() {

		return array_merge(parent::getDefaultOptions(), array(
		));
		
	}
	
}
