<?php

require_once 'X/Env.php';

class Application_Form_PluginInstall extends Zend_Form
{
    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post')
        	->setAttrib('enctype', 'multipart/form-data');
 
        $this->addElement('file', 'file', array(
            'label'      	=> X_Env::_('plugin_install_label'),
        	'description'	=> X_Env::_('plugin_install_desc'),
            'required'   	=> true,
            'filters'    	=> array('StringTrim'),
        	'destination'	=> APPLICATION_PATH . '/../data/plugin/',
        	'validators'	=> array(
				array(
					'validator' => 'Count',
					'options'       => array( false, 1)
				),
				array(
					'validator' => 'Size',
					'options'       => array( false, ini_get('upload_max_filesize')	)
				),
				array(
					'validator' => 'Extension',
					'options'       => array( false, 'zip,xegg')
				),
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
        	'salt'	=> 'plugin_install_salt',
        	'decorators' => array('ViewHelper')
        ));
        
    }
}