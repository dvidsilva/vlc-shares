<?php 

/**
 * Wrap content with a <div class="container"></div> tag
 * 
 * Allowed options:
 * 		container.classes: extra classes args appended to container
 * 		container.args: extra args appended to class arg
 * 
 */
class X_VlcShares_Skins_Default_Container extends X_VlcShares_Skins_Default_Abstract {
	
	/**
	 * Wrap content with a <div class="container"></div> tag 
	 * 
	 * @param string $content content to decorate
	 * @param stdClass $options decorator options
	 * @return string
	 */
	public function decorate($content, $options) {
		
		$options = $this->normalizeOptions($options);
		
		$classes = $options['container.classes'];
		$args = $options['container.args'];
		
		return $this->wrap($content, 'div', "class=\"container $classes\" $args");
	}
	
	protected function getDefaultOptions() {
		
		return array(
			'container.classes' => '',
			'container.args' => ''
		);
		
	}
	
}
