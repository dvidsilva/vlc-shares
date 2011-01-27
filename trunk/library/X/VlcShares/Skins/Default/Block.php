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
class X_VlcShares_Skins_Default_Block extends X_VlcShares_Skins_Default_Container {
	
	/**
	 * Wrap the content with a <div class="block"><div class="inner">$content</div></div> tag
	 * 
	 * @param string $content content to decorate
	 * @param stdClass $options decorator options
	 */
	public function decorate($content, $options) {

		// as first thing: default skin decorators have $this->decoratorOptions array
		// for per-instance configurations
		
		// block decorator allow variant:
		$variant = !empty($this->decoratorOptions['variant']) ? $this->decoratorOptions['variant'] : ''; 
		
		
		$this->normalizeOptions($options);
		
		$title = empty($options['block.title']) ? false : $options['block.title'];
		$header = empty($options['block.header']) ? false : $options['block.header'];
		$footer = empty($options['block.footer']) ? false : $options['block.footer'];
		
		$content = parent::decorate($content, $options);
		
		if ( $header !== false ) {
			$header = $this->wrap($header, 'div', 'class="header"');
			$content = $header.PHP_EOL
						.$content;
			// i use $header var for css class
			$header = 'with-header';
		} else {
			$header = '';
		}
		
		if ( $title !== false ) {
			$content = "<h1>$title</h1>".PHP_EOL
						.$content;
			// i use $title var for css class
			$title = 'titled';
		} else {
			$title = '';
		}

		if ( $footer !== false ) {
			$footer = $this->wrap($footer, 'div', 'class="footer"');
			// append footer to content
			$content .= PHP_EOL.$footer;
			// i use $header var for css class
			$footer = 'with-footer';
		} else {
			$footer = '';
		}
		
		$content = $this->recursiveWrap($content, array(
			array('div', "class=\"block $variant $title $header $footer\""),
			array('div', "class=\"inner\"")
		));
		
		return $content;
	}
	
	protected function getDefaultOptions() {

		return array_merge(parent::getDefaultOptions(), array(
			'block.title' => '',
			'block.header' => '',
			'block.footer' => ''
		));
		
	}
	
}
