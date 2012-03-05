<?php

class Application_Form_AclClass extends X_Form
{
    public function init()
    {
    	$this->setName('aclclass');
        // Set the method for the display form to POST
        $this->setMethod('post');
 
        $this->addElement('text', 'name', array(
            'label'      => X_Env::_('p_auth_form_acl_class_name_label'),
            'required'   => true,
            'filters'    => array('StringTrim')
        ));
 
        $this->addElement('text', 'description', array(
            'label'      => X_Env::_('p_auth_form_acl_class_description_label'),
			'required'   => false,
        	'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 255))
                )
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