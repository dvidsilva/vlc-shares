<?php

class X_Form_Element_Reset extends Zend_Form_Element_Reset {
	
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
				'viewHelper'
			));
		}
	}

}
