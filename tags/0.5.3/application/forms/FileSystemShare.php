<?php

require_once 'X/Env.php';

class Application_Form_FileSystemShare extends X_Form
{
    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');
 
        $this->addElement('text', 'label', array(
            'label'      => X_Env::_('p_filesystem_share_label_label'),
        	'description'=> X_Env::_('p_filesystem_share_label_desc'),
            'required'   => true,
            'filters'    => array('StringTrim')
        ));
 
        $this->addElement('text', 'path', array(
            'label'      => X_Env::_('p_filesystem_share_path_label'),
        	'description'=> X_Env::_('p_filesystem_share_path_desc'),
			'required'   => true,
        	'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 255))
                )
        ));

 
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => X_Env::_('submit'),
        	//'decorators' => array('ViewHelper')
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
        	'salt'	=> __CLASS__,
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