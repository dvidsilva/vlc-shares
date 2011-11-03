<?php

class X_Form extends Zend_Form {
	
	protected $_defaultDisplayGroupClass = 'X_Form_DisplayGroup';
	
	/**
	 * Constructor
	 *
	 * Add custom prefix path before parent constructor
	 *
	 * @param mixed $options
	 * @return void
	 */
	public function __construct($options = null) {
		$this->addPrefixPath ( 'X_Form', 'X/Form' );
		$this->addPrefixPath ( 'X_Form_Element', 'X/Form/Element', 'element' );
		$this->addPrefixPath('X_Form_Decorator', 'X/Form/Decorator', 'decorator');        
		$this->addElementPrefixPath('X_Form_Element', 'X/Form/Element');
		parent::__construct ( $options );
	}
	
	/**
	 * Load the default decorators
	 *
	 * @return void
	 */
	public function loadDefaultDecorators() {
		if ($this->loadDefaultDecoratorsIsDisabled ()) {
			return;
		}
		
		$decorators = $this->getDecorators ();
		if (empty ( $decorators )) {
	        $this->setDecorators(array(
	        	'formElements',
	        	array('htmlTag', array('tag' => 'div', 'class' => 'unit x_form')),
	        	'form',
	        ));
		}
	}
	
	public function getDefaultButtonsDisplayGroupDecorators() {
		return array(
			'FormElements',
       		array(array('center' => 'htmlTag'), array('tag' => 'center')),
       		array(array('container' => 'htmlTag'), array('tag' => 'div', 'class' => 'container buttons')),
			array(array('row' => 'htmlTag'), array('tag' => 'div', 'class' => 'column span-3')),
			array(array('right' => 'htmlTag'), array('tag' => 'div', 'class' => 'column empty', 'placement' => 'append')),
			array('htmlTag', array('tag' => 'div', 'class' => 'columns on-4 row')),
		);
	}
	
}
