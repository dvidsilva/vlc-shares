<?php

require_once 'X/Env.php';

class Application_Form_Profile extends Zend_Form
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
 
        $this->addElement('text', 'arg', array(
            'label'      => X_Env::_('p_profiles_form_arg_label'),
        	'description'=> X_Env::_('p_profiles_form_arg_desc'),
			'required'   => true,
        	'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 1000))
                )
        ));

        $this->addElement('select', 'audio', array(
            'label'      => X_Env::_('p_profiles_form_audio_label'),
        	'description'=> X_Env::_('p_profiles_form_audio_desc'),
			'required'   => true,
        ));
        
        $this->addElement('select', 'video', array(
            'label'      => X_Env::_('p_profiles_form_video_label'),
        	'description'=> X_Env::_('p_profiles_form_video_desc'),
			'required'   => true,
        ));
        
        $this->addElement('select', 'device', array(
            'label'      => X_Env::_('p_profiles_form_devices_label'),
        	'description'=> X_Env::_('p_profiles_form_devices_desc'),
			'required'   => true,
        ));
        
        $this->addElement('text', 'weight', array(
            'label'      => X_Env::_('p_profiles_form_weight_label'),
        	'description'=> X_Env::_('p_profiles_form_weight_desc'),
			'required'   => false,
        	'filters'    => array('Int'),
            'validators' => array('Int')
        ));
        
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => X_Env::_('submit'),
        	'decorators' => array('ViewHelper')
        ));
        
        // Add the submit button
        $this->addElement('button', 'abort', array(
            'ignore'   => true,
            'label'    => X_Env::_('abort'),
        	'decorators' => array('ViewHelper')
        ));
 
        // And finally add some CSRF protection
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        	'salt'	=> 'p_profiles_salt',
        	'decorators' => array('ViewHelper')
        ));
        
        $this->addElement('hidden', 'id', array(
            'ignore' => true,
        	'required'	=> false,
        	'decorators' => array('ViewHelper')
        ));
        
    }
}