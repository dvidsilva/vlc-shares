<?php

require_once 'X/Env.php';

class Application_Form_AuthLogin extends X_Form
{
    public function init()
    {
    	$this->setName('login');
        // Set the method for the display form to POST
        $this->setMethod('post');
 
        $this->addElement('text', 'username', array(
            'label'      => X_Env::_('p_auth_form_login_username_label'),
            'required'   => true,
            'filters'    => array('StringTrim')
        ));
 
        $this->addElement('password', 'password', array(
            'label'      => X_Env::_('p_auth_form_login_password_label'),
			'required'   => true,
        	'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 1000))
                )
        ));
        
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => X_Env::_('p_auth_login'),
        	'decorators' => array('ViewHelper')
        ));
 
        // And finally add some CSRF protection
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        	'salt'	=> __CLASS__,
        	//'decorators' => array('ViewHelper')
        ));
        
        $this->addDisplayGroup(array('submit', 'csrf'), 'buttons', array('decorators' => $this->getDefaultButtonsDisplayGroupDecorators()));
        
    }
}