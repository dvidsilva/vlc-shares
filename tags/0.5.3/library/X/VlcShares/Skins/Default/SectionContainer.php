<?php 

/**
 * Wrap content with a <div class="block"><div class="inner">$content</div></div> tag
 * 
 * Allowed options:
 * 		container.classes: extra classes args appended to container
 * 		container.args: extra args appended to class arg
 * 
 * Allowed portions:
 * 		block.header: header of the block
 * 		block.footer: footer of the block
 * 		block.title: title of the block
 * 
 */
class X_VlcShares_Skins_Default_SectionContainer extends X_VlcShares_Skins_Default_Container {
	
	/**
	 * Wrap the content with a <div class="block"><div class="inner">$content</div></div> tag
	 * 
	 * @param string $content content to decorate
	 * @param stdClass $options decorator options
	 */
	public function decorate($content, $options) {

		$options = $this->normalizeOptions($options);

		$sameHeight = $options['sectioncontainer.same-height'];
		$perRow = !empty($options['sectioncontainer.per-row']) ? 'on-'.$options['sectioncontainer.per-row'] : '';
		$classes = $options['container.classes'];
		$args = $options['container.args'];
		
		
		// we don't want parent decorator
		//$content = parent::decorate($content, $options);
		
		
		
		$content = $this->wrap($content,'div', "class=\"columns $perRow $sameHeight $classes\" $args");
		
		return $content;
	}
	
	protected function getDefaultOptions() {
		return array_merge(parent::getDefaultOptions(), array(
			'sectioncontainer.per-row' => '',
			'sectioncontainer.same-height' => false,
		));
	}
	
}
