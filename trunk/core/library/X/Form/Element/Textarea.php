<?php

class X_Form_Element_Textarea extends Zend_Form_Element_Textarea {
	
	function init() {
		parent::init();
		if ( is_null($this->getAttrib('rows')) ) {
			$this->setAttrib('rows', '5');
		}
	}
	
	/**
	 * Load default decorators
	 *
	 * @return void
	 */
	public function loadDefaultDecorators() {
		if ($this->loadDefaultDecoratorsIsDisabled ()) {
			return;
		}
		
		$decorators = $this->getDecorators();
		if ( empty($decorators) ) {
			$this->addPrefixPath('X_Form_Decorator',
                            'X/Form/Decorator',
                            'decorator');        
			$this->setDecorators(array(
				'Composite'
			));
		}
	}

}
