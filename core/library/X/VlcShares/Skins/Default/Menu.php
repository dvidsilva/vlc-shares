<?php 

/**
 * Wrap content with a <ul class="menu $variant">$content</ul> tag
 * 
 * Allowed options:
 * 		menuentry.label: eventually a name for the menu
 * 		menuentry.href: and an href arg
 * 		menu.level: the level of this menu (if root = 0)
 * 
 * Decorator options:
 * 		variant: disabled, highligh, ''
 * 
 */
class X_VlcShares_Skins_Default_Menu extends X_VlcShares_Skins_Default_Abstract {
	
	/**
	 * Wrap content with a <div class="container"></div> tag 
	 * 
	 * @param string $content content to decorate
	 * @param stdClass $options decorator options
	 * @return string
	 */
	public function decorate($content, $options) {

		$variant = !empty($this->decoratorOptions['variant']) ? $this->decoratorOptions['variant'] : '';		
		
		$options = $this->normalizeOptions($options);

		$label = $options['menuentry.label'];
		$href = $options['menuentry.href'];
		$level = (int) $options['menu.level'];
		
		$menuheader = '';
		if ( !empty($label) ) {
			$menuheader = $this->wrap($label, 'span');
			if ( !empty($href) ) { 
				$menuheader = $this->wrap($label, 'a', "href=\"$href\" class=\"menu-header\"" );
			}
		}
		
		$content = $this->wrap($content, 'ul', "class=\"menu $variant\"");
		$content = $menuheader.$content;
		
		if ( $level > 0 ) {
			$content = $this->wrap($content, 'li', "class=\"menuentry submenu submenu-level-$level\"");
		}
		
		return $content;
	}
	
	protected function getDefaultOptions() {
		return array(
			'menuentry.label' => '',
			'menuentry.href' => '',
			'menu.level' => 0
		);
	}
	
}
