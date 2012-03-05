<?php

class Application_Form_AclResource extends X_Form
{
    public function init()
    {
    	$this->setName('aclresource');
        // Set the method for the display form to POST
        $this->setMethod('post');
 
        $this->addElement('text', 'key', array(
            'label'      => X_Env::_('p_auth_form_acl_resource_key_label'),
        	'description' => X_Env::_('p_auth_form_acl_resource_key_desc'),
            'required'   => true,
            'filters'    => array('StringTrim')
        ));
 
        $this->addElement('select', 'class', array(
            'label'      => X_Env::_('p_auth_form_acl_resource_class_label'),
        	'description' => X_Env::_('p_auth_form_acl_resource_class_desc'),
			'required'   => true,
        ));

        $this->addElement('select', 'generator', array(
        		'label'      => X_Env::_('p_auth_form_acl_resource_generator_label'),
        		'description' => X_Env::_('p_auth_form_acl_resource_generator_desc'),
        		'required'   => true,
        ));
        
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => X_Env::_('submit'),
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