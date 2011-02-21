<?php

require_once 'X/Env.php';

class Application_Form_Installer extends X_Form
{
	
	function __construct($options = null) {
		parent::__construct($options);
	}
	
    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');
        $this->setName('installer');
 

        $this->addElement('select', 'lang', array(
        	'required' => true,
        	'label' => X_Env::_('installer_selectlanguage'),
        	'multiOptions' => array(),
        ));
        

        $this->addElement('multiCheckbox', 'plugins', array(
        	'required' => false,
        	'label' => X_Env::_('installer_optionalplugins'),
        	'description' => X_Env::_('installer_optionalplugins_desc'),
        ));
        
        
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => X_Env::_('installer_startbutton'),
        	//'decorators' => array('ViewHelper')
        ));
 
        // And finally add some CSRF protection
        $this->addElement('hash', 'csrf', array(
        	'salt'	=> __CLASS__,
            'ignore' => true,
        	//'decorators' => array('ViewHelper')
        ));
        
        $this->addDisplayGroup(array('submit', 'csrf'), 'buttons', array('decorators' => $this->getDefaultButtonsDisplayGroupDecorators())); 
        
    }
}