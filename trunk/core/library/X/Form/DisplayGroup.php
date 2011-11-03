<?php 

class X_Form_DisplayGroup extends Zend_Form_DisplayGroup
{
    /**
     * Load default decorators
     * 
     * @return void
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }
 
        $decorators = $this->getDecorators();
        if (empty($decorators)) {
        	
            $this->addDecorator('FormElements')
            	->addDecorator('H2FieldSet', array('placement' => 'prepend'));
        }
    }
}
