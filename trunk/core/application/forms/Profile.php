<?php

require_once 'X/Env.php';

class Application_Form_Profile extends X_Form
{
    public function init()
    {
    	$this->setName('profiles');
        // Set the method for the display form to POST
        $this->setMethod('post');
 
        $this->addElement('text', 'label', array(
            'label'      => X_Env::_('p_profiles_form_label_label'),
        	'description'=> X_Env::_('p_profiles_form_label_desc'),
            'required'   => true,
            'filters'    => array('StringTrim')
        ));
 
        $this->addElement('textarea', 'arg', array(
            'label'      => X_Env::_('p_profiles_form_arg_label'),
        	'description'=> X_Env::_('p_profiles_form_arg_desc'),
			'required'   => true,
        	'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 1000))
                )
        ));
        
        $this->addElement('text', 'link', array(
            'label'      => X_Env::_('p_profiles_form_link_label'),
        	'description'=> X_Env::_('p_profiles_form_link_desc'),
			'required'   => true,
        	'filters'    => array('StringTrim'),
        ));
        
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => X_Env::_('submit'),
        	'decorators' => array('ViewHelper')
        ));
        
        // Add the submit button
        $this->addElement('reset', 'abort', array(
            'ignore'   => true,
            'label'    => X_Env::_('abort'),
        	//'decorators' => array('ViewHelper')
        ));
 
        // And finally add some CSRF protection
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        	'salt'	=> 'p_profiles_salt',
        	//'decorators' => array('ViewHelper')
        ));
        
        $this->addElement('hidden', 'id', array(
            'ignore' => true,
        	'required'	=> false,
        	//'decorators' => array('ViewHelper')
        ));
        
        $this->addDisplayGroup(array('submit', 'csrf', 'id', 'abort'), 'buttons', array('decorators' => $this->getDefaultButtonsDisplayGroupDecorators()));
        
    }
}