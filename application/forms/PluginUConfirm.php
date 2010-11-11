<?php

require_once 'X/Env.php';

class Application_Form_PluginUConfirm extends Zend_Form
{
    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post')
        	->setAttrib('enctype', 'multipart/form-data');
 
        $this->addElement('hidden', 'key', array(
            'ignore'   => false,
        	'decorators' => array('ViewHelper'),
        	'required' => true
        ));
        	
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => X_Env::_('plugin_action_uninstall'),
        	'decorators' => array('ViewHelper')
        ));
        
        // And finally add some CSRF protection
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        	'salt'	=> 'plugin_uconfirm_salt',
        	'decorators' => array('ViewHelper')
        ));
        
    }
}