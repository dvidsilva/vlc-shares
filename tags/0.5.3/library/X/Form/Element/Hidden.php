<?php

class X_Form_Element_Hidden extends Zend_Form_Element_Hidden {
	
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
			$this->setDecorators(array(
				//'X_Form_Decorator_Button'
				'viewHelper'
			));
		}
	}

}
