<?php 

/**
 * Wrap content with a <div class="column"></div> tag
 * 
 * Allowed options:
 * 		container.classes: extra classes args appended to container
 * 		container.args: extra args appended to class arg
 * 
 */
class X_VlcShares_Skins_Default_Section extends X_VlcShares_Skins_Default_Container {
	
	/**
	 * Wrap content with a <div class="column"></div> tag 
	 * 
	 * @param string $content content to decorate
	 * @param stdClass $options decorator options
	 * @return string
	 */
	public function decorate($content, $options) {
		
		$options = $this->normalizeOptions($options);
		
		$styleArgs = '';
		$widthClass = '';
		$heightClass = '';
		$vertical = '';
		$horizontal = '';
		$span = '';
		
		switch ($options['section.width.type']) {
			case X_VlcShares_Elements_Section::FIXED:
				$widthClass = 'fixed';
				$styleArgs .= 'width: '.$options['section.width.value'].'; ';
				break;
			case X_VlcShares_Elements_Section::RANGE:
				$widthClass = 'fixed';
				$styleArgs .= 'min-width: '.$options['section.width.min'].'; ';
				$styleArgs .= 'max-width: '.$options['section.width.max'].'; ';
				break;
			case X_VlcShares_Elements_Section::ELASTIC:
				$widthClass = 'elastic';
				break;
			case X_VlcShares_Elements_Section::FULL:
				$widthClass = 'full-width';
				break;
			default: 
				break;
		}
		
		switch ($options['section.height.type']) {
			case X_VlcShares_Elements_Section::FIXED:
				$styleArgs .= 'height: '.$options['section.height.value'].'; ';
				break;
			case X_VlcShares_Elements_Section::RANGE:
				$styleArgs .= 'min-height: '.$options['section.height.min'].'; ';
				$styleArgs .= 'max-height: '.$options['section.height.max'].'; ';
				break;
			case X_VlcShares_Elements_Section::ELASTIC:
				$heightClass = 'elastic-height';
				break;
			case X_VlcShares_Elements_Section::FULL:
				$heightClass = 'full-height';
				break;
			default: 
				break;
		}
		
		switch ($options['section.horizontal']) {
			case X_VlcShares_Elements_Section::MIDDLE:
				$horizontal = 'horizontal-center';
				break;
			default:
				break;
		}

		switch ($options['section.vertical']) {
			default;
			case X_VlcShares_Elements_Section::TOP:
				// it's the default behaviour
				break;
			case X_VlcShares_Elements_Section::MIDDLE:
				$vertical = 'vertical-center';
				break;
			case X_VlcShares_Elements_Section::MIDDLE:
				$vertical = 'bottom';
				break;
		}
		
		if ( !empty($options['section.span']) ) {
			$span = 'span-'.$options['section.span'];
		}
		
		$content = parent::decorate($content, $options);
		
		//die($content);
		
		return $this->wrap($content, 'div', array(
			"class=\"$widthClass column $span $heightClass $horizontal $vertical\"",
			"style=\"$styleArgs\""
		));
	}
	
	protected function getDefaultOptions() {
		
		return array_merge(parent::getDefaultOptions(), array(
			'section.width.type' => '',
			'section.width.value' => '',
			'section.width.min' => '',
			'section.width.max' => '',
		
			'section.height.type' => '',
			'section.height.value' => '',
			'section.height.min' => '',
			'section.height.max' => '',
		
			'section.span'	=> '',
		
			'section.vertical' => '',
		
			'section.horizontal' => ''
		));
				
	}
	
}
