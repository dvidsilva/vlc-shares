<?php

class Application_Form_Video extends X_Form
{
    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');

        $this->addElement('select', 'hoster', array(
            'label'      => X_Env::_('p_onlinelibrary_form_hoster_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_hoster_description'),
            'required'   => true,
            'filters'    => array('StringTrim'),
        ));
        
        $this->addElement('text', 'idVideo', array(
            'label'      => X_Env::_('p_onlinelibrary_form_idvideo_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_idvideo_description'),
            'required'   => true,
            'filters'    => array('StringTrim'),
        ));
 
        $this->addElement('text', 'category', array(
            'label'      => X_Env::_('p_onlinelibrary_form_category_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_category_description'),
			'required'   => true,
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 50))
                )
        ));

        $this->addElement('checkbox', 'check', array(
            'label'      => X_Env::_('p_onlinelibrary_form_check_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_check_description'),
        	'required'	 => false,
        ));
        
        // Add the comment element
        $this->addElement('text', 'title', array(
            'label'      => X_Env::_('p_onlinelibrary_form_title_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_title_description'),
			'required'   => false,
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 50))
                )
        ));

        $this->addElement('textarea', 'description', array(
            'label'      => X_Env::_('p_onlinelibrary_form_description_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_description_description'),
        	'rows'		 => '5',
        	'filters'	 => array('StripTags'),
        	'required'	 => false
        ));

        // Add the comment element
        $this->addElement('text', 'thumbnail', array(
            'label'      => X_Env::_('p_onlinelibrary_form_thumbnail_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_thumbnail_description'),
			'required'   => false,
        ));
        
        
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => X_Env::_('p_onlinelibrary_form_submit'),
        ));
        
        // Add the submit button
        $this->addElement('button', 'abort', array(
        	'onClick'	=> 'javascript:history.back();',
            'ignore'   => true,
            'label'    => X_Env::_('p_onlinelibrary_form_abort'),
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