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

        $this->addElement('radio', 'auth', array(
            'label'      => X_Env::_('installer_authrequired_label'),
        	'description' => X_Env::_('installer_authrequired_desc'),
            'required'   => true,
            'filters'    => array('StringTrim'),
        	'multiOptions' => array(
        		1 => X_Env::_('configs_options_yes'),
        		0 => X_Env::_('configs_options_no'),
        	)
        ));
        
        $this->addElement('text', 'username', array(
            'label'      => X_Env::_('p_auth_form_account_username_label'),
            'required'   => true,
            'filters'    => array('StringTrim')
        ));
 
        $this->addElement('password', 'password', array(
            'label'      => X_Env::_('p_auth_form_account_password_label'),
        	'description'      => X_Env::_('p_auth_form_account_password_desc2'),
			'required'   => true,
        	'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(5, 32))
                )
        ));

        $this->addElement('text', 'threads', array(
       		'label'      	=> X_Env::_('config_general_threads_forker_label'),
       		'description'   => X_Env::_('config_general_threads_forker_desc'),
       		'required'   	=> true,
       		'class'			=> 'hidden'
        ));
        
        $this->addElement('multiCheckbox', 'plugins', array(
        	'required' => false,
        	'label' => X_Env::_('installer_optionalplugins'),
        	'description' => X_Env::_('installer_optionalplugins_desc'),
        	'escape' => false,
        	'decorators' => array(
        		'PluginsMultiCheckBox'
        	)
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