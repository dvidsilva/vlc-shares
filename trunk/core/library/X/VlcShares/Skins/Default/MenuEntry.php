<?php 

/**
 * Wrap content with a <li class="$variant">$content</li> tag
 * 
 * Allowed options:
 * 		menuentry.label: extra classes args appended to container
 * 		menuentry.href: extra args appended to class arg
 * 
 * Decorator options:
 * 		type: BUTTON, LINK, LABEL
 * 		variant: disable, highlight, ''
 * 
 */
class X_VlcShares_Skins_Default_MenuEntry extends X_VlcShares_Skins_Default_Abstract {
	
	const LABEL = "label";
	const LINK = "link";
	const BUTTON = "button";
	
	/**
	 * Wrap content with a <li class="$variant"></li> tag 
	 * 
	 * @param string $content content to decorate
	 * @param stdClass $options decorator options
	 * @return string
	 */
	public function decorate($content, $options) {
		
		$options = $this->normalizeOptions($options);
		
		// menuentry decorator allow variant:
		$variant = !empty($this->decoratorOptions['variant']) ? $this->decoratorOptions['variant'] : '';
		$type = !empty($this->decoratorOptions['type']) ? $this->decoratorOptions['type'] : 'label'; 
		
		
		$label = $options['menuentry.label'];
		$href = $options['menuentry.href'];
		
		if ( !empty($content) ) {
			$content = $label . " " . $content;
		} else {
			$content = $label;
		}
		
		switch ($type) {
			case self::BUTTON: 
				$content = $this->recursiveWrap($content, array(
					array('a', "class=\"button\" href=\"$href\""),
					array('span')
				));
				break;
			case self::LABEL:
				$content = $this->wrap($content, 'span'); 
				break; // label
			// default as link
			default:
			case self::LINK:
				$content = $this->recursiveWrap($content, array(
					array('a', "href=\"$href\""),
					array('span')
				));
				break;
			//case self
			
		}
		
		$content = $this->wrap($content, 'li', "class=\"menuentry $variant\"");
		return $content;
	}
	
	protected function getDefaultOptions() {
		return array(
			'menuentry.label' => '',
			'menuentry.href' => ''
		);
	}
	
}
