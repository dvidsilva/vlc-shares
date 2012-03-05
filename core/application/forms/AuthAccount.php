<?php

require_once 'X/Env.php';

class Application_Form_AuthAccount extends X_Form
{
    public function init()
    {
    	$this->setName('auth_account');
        // Set the method for the display form to POST
        $this->setMethod('post');
 
        $this->addElement('text', 'username', array(
            'label'      => X_Env::_('p_auth_form_account_username_label'),
            'required'   => true,
            'filters'    => array('StringTrim')
        ));
 
        $this->addElement('password', 'password', array(
            'label'      => X_Env::_('p_auth_form_account_password_label'),
        	'description'	=> X_Env::_('p_auth_form_account_password_desc'),
			'required'   => true,
        	'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(5, 32))
                )
        ));
        
        $this->addElement('radio', 'enabled', array(
        	'label'		=> X_Env::_('p_auth_form_account_enabled_label'),
        	'required'	=> true,
        	'multiOptions' => array(
        		1 => X_Env::_('enabled'),
        		0 => X_Env::_('disabled'),
        	)
        ));

        $this->addElement('radio', 'altallowed', array(
        	'label'		=> X_Env::_('p_auth_form_account_altallowed_label'),
        	'description'	=> X_Env::_('p_auth_form_account_altallowed_desc'),
        	'required'	=> true,
        	'multiOptions' => array(
        		1 => X_Env::_('enabled'),
        		0 => X_Env::_('disabled'),
        	)
        ));
        
        $this->addElement('multiCheckbox', 'permissions', array(
        	'label'		=> X_Env::_('p_auth_form_account_permissions_label'),
        	'description'	=> X_Env::_('p_auth_form_account_permissions_desc'),
        	'required'	=> true,
        	'escape' => false,        		
        ));
        
        
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => X_Env::_('p_auth_form_account_save'),
        	'decorators' => array('ViewHelper')
        ));
 
        // And finally add some CSRF protection
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        	'salt'	=> __CLASS__,
        	//'decorators' => array('ViewHelper')
        ));
        
        $this->addElement('hidden', 'id', array(
            'ignore' => true,
        	'required'	=> false,
        	//'decorators' => array('ViewHelper')
        ));
        
        $this->addDisplayGroup(array('submit', 'csrf', 'id'), 'buttons', array('decorators' => $this->getDefaultButtonsDisplayGroupDecorators()));
        
    }
}