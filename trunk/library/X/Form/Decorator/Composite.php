<?php
class X_Form_Decorator_Composite extends Zend_Form_Decorator_Abstract {
	public function buildLabel() {
		$element = $this->getElement();
		$label = $element->getLabel();
		$translator = $element->getTranslator();
		if ( $translator ) {
			$label = $translator->translate ( $label );
		}
		if ($element->isRequired ()) {
			$label .= '*';
		}
		$label .= ':';
		$label = $element->getView ()->formLabel ( $element->getName (), $label );
		
		
		return '<div class="column"><div class="container full-height label">' . $label . '</div></div>';
	}
	
	public function buildInput() {
		$element = $this->getElement ();
		$helper = $element->helper;
		$input = $element->getView ()->$helper ( $element->getName (), $element->getValue (), $element->getAttribs (), $element->options );
		$description = $this->buildDescription();
		return '<div class="column span-2 elastic"><div class="container full-height element">' . $input . $description . '</div></div>';
	}
	
	public function buildErrors() {
		$element = $this->getElement ();
		$messages = $element->getMessages ();
		if (empty ( $messages )) {
			return '<div class="column empty"><div class="container full-height notes"></div></div>';
		}
		return '<div class="column"><div class="container full-height notes">' . $element->getView ()->formErrors ( $messages ) . '</div></div>';
	}
	
	public function buildDescription() {
		$element = $this->getElement ();
		$desc = $element->getDescription ();
		if (empty ( $desc )) {
			return '';
		}
		return '<p class="description">' . $desc . '</p>';
	}
	
	public function render($content) {
		$element = $this->getElement ();
		if (! $element instanceof Zend_Form_Element) {
			return $content;
		}
		if (null === $element->getView ()) {
			return $content;
		}
		
		$separator = $this->getSeparator ();
		$placement = $this->getPlacement ();
		$label = $this->buildLabel ();
		$input = $this->buildInput ();
		$errors = $this->buildErrors ();
		//$desc = $this->buildDescription ();
 
		$class = $this->getOption('class');
		$class = empty($class) ? '' : " $class";
		
        $output = '<div class="columns on-4 same-height row' . $class . '">'
                . $label
                . $input
                . $errors
                . '</div>';
 
        
		switch ($placement) {
			case (self::PREPEND) :
				return $output . $separator . $content;
			case (self::APPEND) :
			default :
				return $content . $separator . $output;
		}
	}
}