<?php

class Application_Form_Megavideo extends X_Form
{
    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');
        
        $this->addElement('text', 'idVideo', array(
            'label'      => X_Env::_('megavideo_form_idvideo_name'),
        	'description'=> X_Env::_('megavideo_form_idvideo_description'),
            'required'   => true,
            'filters'    => array('StringTrim'),
        ));
 
        $this->addElement('text', 'category', array(
            'label'      => X_Env::_('megavideo_form_category_name'),
        	'description'=> X_Env::_('megavideo_form_category_description'),
			'required'   => true,
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 50))
                )
        ));

        // Add the comment element
        $this->addElement('text', 'label', array(
            'label'      => X_Env::_('megavideo_form_label_name'),
        	'description'=> X_Env::_('megavideo_form_label_description'),
			'required'   => false,
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 50))
                )
        ));
        
        $this->addElement('textarea', 'description', array(
            'label'      => X_Env::_('megavideo_form_description_name'),
        	'description'=> X_Env::_('megavideo_form_description_description'),
        	'rows'		 => '5',
        	'filters'	 => array('StripTags')
        ));
 
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => X_Env::_('megavideo_form_submit'),
        ));
        
        // Add the submit button
        $this->addElement('button', 'abort', array(
        	'onClick'	=> 'javascript:history.back();',
            'ignore'   => true,
            'label'    => X_Env::_('megavideo_form_abort'),
        ));
		
        $this->addDisplayGroup(array('submit', 'abort'), 'buttons', 
        	array('decorators' => $this->getDefaultButtonsDisplayGroupDecorators())
		);
        
        /*
        // And finally add some CSRF protection
        $this->addElement('hash', 'csrf', array(
            'ignore' => true,
        ));
        */
    }
}