<?php

require_once 'X/Env.php';

class Application_Form_PluginIConfirm extends X_Form
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
            'label'    => X_Env::_('plugin_action_install'),
        	'decorators' => array('ViewHelper')
        ));
        
        // And finally add some CSRF protection
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        	'salt'	=> __CLASS__,
        	'decorators' => array('ViewHelper')
        ));
        
        $this->addDisplayGroup(array('submit', 'csrf', 'key'), 'buttons', array('decorators' => $this->getDefaultButtonsDisplayGroupDecorators()));
        
    }
}