<?php

require_once 'X/Env.php';

class Application_Form_Output extends Zend_Form
{
    public function init()
    {
    	$this->setName('outputs');
        // Set the method for the display form to POST
        $this->setMethod('post');
 
        $this->addElement('text', 'label', array(
            'label'      => X_Env::_('p_outputs_form_label_label'),
        	'description'=> X_Env::_('p_outputs_form_label_desc'),
            'required'   => true,
            'filters'    => array('StringTrim')
        ));
 
        $this->addElement('text', 'link', array(
            'label'      => X_Env::_('p_outputs_form_link_label'),
        	'description'=> X_Env::_('p_outputs_form_link_desc'),
			'required'   => true,
        	'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 255))
                )
        ));

        $this->addElement('text', 'arg', array(
            'label'      => X_Env::_('p_outputs_form_arg_label'),
        	'description'=> X_Env::_('p_outputs_form_arg_desc'),
			'required'   => true,
        	'filters'    => array('StringTrim'),
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 255))
                )
        ));

        $this->addElement('text', 'weight', array(
            'label'      => X_Env::_('p_outputs_form_weight_label'),
        	'description'=> X_Env::_('p_outputs_form_weight_desc'),
			'required'   => false,
        	'filters'    => array('Int'),
            'validators' => array('Int')
        ));
        
        $devices = array(
        	'-1' => X_Env::_('p_outputs_devicetype_generic'),
        	X_VlcShares_Plugins_Helper_Devices::DEVICE_WIIMC => 'WiiMC',
        	X_VlcShares_Plugins_Helper_Devices::DEVICE_ANDROID => X_Env::_('p_outputs_devicetype_android'),
        	X_VlcShares_Plugins_Helper_Devices::DEVICE_PC => 'Pc',
        );
        
        
        $this->addElement('select', 'cond_devices', array(
            'label'      => X_Env::_('p_outputs_form_conddevices_label'),
        	'description'=> X_Env::_('p_outputs_form_conddevices_desc'),
			'required'   => true,
        	'multiOptions' => $devices
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
        	'salt'	=> 'p_outputs_salt',
        	'decorators' => array('ViewHelper')
        ));
        
        $this->addElement('hidden', 'id', array(
            'ignore' => true,
        	'required'	=> false,
        	'decorators' => array('ViewHelper')
        ));
        
    }
}