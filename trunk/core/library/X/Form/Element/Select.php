<?php

class X_Form_Element_Select extends Zend_Form_Element_Select {
	
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
