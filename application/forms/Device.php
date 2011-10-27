<?php

require_once 'X/Env.php';

class Application_Form_Device extends X_Form
{
    public function init()
    {
    	$this->setName('devices');
        // Set the method for the display form to POST
        $this->setMethod('post');
 
        $this->addElement('text', 'label', array(
            'label'      => X_Env::_('p_devices_form_label_label'),
        	'description'=> X_Env::_('p_devices_form_label_desc'),
            'required'   => true,
            'filters'    => array('StringTrim')
        ));
 
        $this->addElement('text', 'pattern', array(
            'label'      => X_Env::_('p_devices_form_pattern_label'),
        	'description'=> X_Env::_('p_devices_form_pattern_desc', "http://www.php.net/manual/en/pcre.pattern.php"),
			'required'   => true,
        	'filters'    => array('StringTrim'),
        /*
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 1000))
                )
		*/
        ));

        $this->addElement('radio', 'exact', array(
            'label'      => X_Env::_('p_devices_form_exact_label'),
        	'description'=> X_Env::_('p_devices_form_exact_desc'),
			'required'   => true,
        	'value' 	=> '',
        	'multiOptions' => array(
        		0 => X_Env::_('configs_options_no'),
        		1 => X_Env::_('configs_options_yes'),
        	)
		));

        $this->addElement('select', 'gui', array(
            'label'      => X_Env::_('p_devices_form_gui_label'),
        	'description'=> X_Env::_('p_devices_form_gui_desc'),
			'required'   => true,
        ));

        $this->addElement('select', 'output', array(
            'label'      => X_Env::_('p_devices_form_output_label'),
        	'description'=> X_Env::_('p_devices_form_output_desc'),
			'required'   => true,
        ));

		$this->addElement('select', 'profile', array(
            'label'      => X_Env::_('p_devices_form_profile_label'),
        	'description'=> X_Env::_('p_devices_form_profile_desc'),
			'required'   => true,
        ));
        
		$this->addElement('multiCheckbox', 'profiles', array(
            'label'      => X_Env::_('p_devices_form_profiles_label'),
        	'description'=> X_Env::_('p_devices_form_profiles_desc'),
			'required'   => false,
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
            'label'    => X_Env::_('reset'),
        	//'decorators' => array('ViewHelper')
        ));
 
        // And finally add some CSRF protection
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        	'salt'	=> 'p_devices_salt',
        	//'decorators' => array('ViewHelper')
        ));
        
        $this->addElement('hidden', 'id', array(
            'ignore' => true,
        	'required'	=> false,
        	//'decorators' => array('ViewHelper')
        ));
        
        $this->addDisplayGroup(array('submit', 'csrf', 'id', 'abort'), 'buttons', array('decorators' => $this->getDefaultButtonsDisplayGroupDecorators()));
    }
    
    /**
	 * @return Application_Form_Device
     */
    function setOutputsValues($options = array()) {
    	
    	$this->getElement('output')->setMultiOptions($options);
    	
    	return $this;
    }

    /**
	 * @return Application_Form_Device
     */
    function setProfilesValues($options = array()) {
    	
    	$this->getElement('profile')->setMultiOptions($options);
    	
    	return $this;
    }

    /**
	 * @return Application_Form_Device
     */
    function setAltProfilesValues($options = array()) {
    	
    	$this->getElement('profiles')->setMultiOptions($options);
    	
    	return $this;
    }
    
    /**
	 * @return Application_Form_Device
     */
    function setGuisValues($options = array()) {
    	
    	$this->getElement('gui')->setMultiOptions($options);
    	
    	return $this;
    }
    
}