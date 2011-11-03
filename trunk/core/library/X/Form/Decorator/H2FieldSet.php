<?php 

class X_Form_Decorator_H2FieldSet extends Zend_Form_Decorator_Fieldset {
	
	function render($content) {

		$legend = $this->getLegend();
		
		$output = "<h2 class=\"fieldset-header\">$legend</h2>";
		
		$separator = $this->getSeparator ();
		$placement = $this->getPlacement ();
		
		switch ($placement) {
			case (self::PREPEND) :
				return $output . $separator . $content;
			case (self::APPEND) :
			default :
				return $content . $separator . $output;
		}
		
	}
	
}

