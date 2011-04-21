<?php

require_once 'X/Env.php';

class Application_Form_YoutubeAccount extends X_Form
{
    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');
 
        
        $decorators = array(
		    array('ViewHelper'),
		    array('Errors'),
		    array('Description', array('tag' => 'p', 'class' => 'description')),
		);
        
        $this->addElement('text', 'label', array(
            'label'      => X_Env::_('p_youtube_form_account_label'),
        	'description'=> X_Env::_('p_youtube_form_account_label_desc'),
            'required'   => true,
            'filters'    => array('StringTrim'),
        	//'decorators' => $decorators
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
        	'salt'	=> 'p_youtube_account_new_salt',
        	//'decorators' => array('ViewHelper')
        ));
        
        $this->addElement('hidden', 'id', array(
            'ignore' => true,
        	'required'	=> false,
        	//'decorators' => array('ViewHelper')
        ));
        
        $this->addDisplayGroup(array('submit', 'abort', 'csrf', 'id'), 'buttons', array('decorators' => $this->getDefaultButtonsDisplayGroupDecorators()));
    }
}