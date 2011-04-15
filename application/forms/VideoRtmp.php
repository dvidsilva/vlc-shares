<?php

class Application_Form_VideoRtmp extends X_Form
{
    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');
        
        $this->addElement('text', 'rtmp', array(
            'label'      => X_Env::_('p_onlinelibrary_form_rtmp_rtmp_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_rtmp_rtmp_description'),
            'required'   => true,
            'filters'    => array('StringTrim'),
        ));
 
        $this->addElement('radio', 'live', array(
            'label'      => X_Env::_('p_onlinelibrary_form_live_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_live_description'),
        	'required'	 => false,
        	'value' 	=> '',
        	'multiOptions' => array(
        		'' => X_Env::_('configs_options_no'),
        		'true' => X_Env::_('configs_options_yes'),
        	)
        ));
        
        $this->addElement('text', 'host', array(
            'label'      => X_Env::_('p_onlinelibrary_form_rtmp_host_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_rtmp_host_description'),
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
 
        $this->addElement('text', 'port', array(
            'label'      => X_Env::_('p_onlinelibrary_form_rtmp_port_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_rtmp_port_description'),
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
 
        $this->addElement('text', 'socks', array(
            'label'      => X_Env::_('p_onlinelibrary_form_rtmp_protocol_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_rtmp_protocol_description'),
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
 
        $this->addElement('text', 'playpath', array(
            'label'      => X_Env::_('p_onlinelibrary_form_rtmp_playpath_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_rtmp_playpath_description'),
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
 
        $this->addElement('text', 'swfUrl', array(
            'label'      => X_Env::_('p_onlinelibrary_form_rtmp_swfUrl_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_rtmp_swfUrl_description'),
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
 
        $this->addElement('text', 'tcUrl', array(
            'label'      => X_Env::_('p_onlinelibrary_form_rtmp_tcUrl_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_rtmp_tcUrl_description'),
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
 
        $this->addElement('text', 'pageUrl', array(
            'label'      => X_Env::_('p_onlinelibrary_form_rtmp_pageUrl_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_rtmp_pageUrl_description'),
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
 
        $this->addElement('text', 'app', array(
            'label'      => X_Env::_('p_onlinelibrary_form_rtmp_app_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_rtmp_app_description'),
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
 
        $this->addElement('text', 'swfhash', array(
            'label'      => X_Env::_('p_onlinelibrary_form_rtmp_swfhash_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_rtmp_swfhash_description'),
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
 
        $this->addElement('text', 'swfsize', array(
            'label'      => X_Env::_('p_onlinelibrary_form_rtmp_swfsize_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_rtmp_swfsize_description'),
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
 
        $this->addElement('text', 'swfVfy', array(
            'label'      => X_Env::_('p_onlinelibrary_form_rtmp_swfVfy_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_rtmp_swfVfy_description'),
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
 
        $this->addElement('text', 'swfAge', array(
            'label'      => X_Env::_('p_onlinelibrary_form_rtmp_swfAge_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_rtmp_swfAge_description'),
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
 
        $this->addElement('text', 'auth', array(
            'label'      => X_Env::_('p_onlinelibrary_form_rtmp_auth_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_rtmp_auth_description'),
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
 
        $this->addElement('text', 'conn', array(
            'label'      => X_Env::_('p_onlinelibrary_form_rtmp_conn_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_rtmp_conn_description'),
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
 
        
        // Add the comment element
        $this->addElement('text', 'flashVer', array(
            'label'      => X_Env::_('p_onlinelibrary_form_flashVer_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_flashVer_description'),
			'required'   => false,
            'validators' => array(
                array('validator' => 'StringLength', 'options' => array(0, 50))
                )
        ));
        
        $this->addElement('text', 'token', array(
            'label'      => X_Env::_('p_onlinelibrary_form_rtmp_token_name'),
        	'description'=> X_Env::_('p_onlinelibrary_form_rtmp_token_description'),
            'required'   => false,
            'filters'    => array('StringTrim'),
        ));
 
        
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => X_Env::_('p_onlinelibrary_form_rtmpsubmit'),
        ));

        // Add the submit button
        $this->addElement('hidden', 'quiet', array(
            'value'   => 'true',
        ));
        
        // Add the submit button
        $this->addElement('button', 'abort', array(
        	'onClick'	=> 'javascript:history.back();',
            'ignore'   => true,
            'label'    => X_Env::_('p_onlinelibrary_form_abort'),
        ));
		
        $this->addDisplayGroup(array('submit', 'abort', 'quiet'), 'buttons', 
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