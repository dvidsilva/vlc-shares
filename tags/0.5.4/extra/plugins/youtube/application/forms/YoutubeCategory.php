<?php

require_once 'X/Env.php';

class Application_Form_YoutubeCategory extends X_Form
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
            'label'      => X_Env::_('p_youtube_form_category_label'),
        	'description'=> X_Env::_('p_youtube_form_category_label_desc'),
            'required'   => true,
            'filters'    => array('StringTrim'),
        	//'decorators' => $decorators
        ));

        $files = array(
        	'upload' => X_Env::_('p_youtube_form_category_thumbselect_otheroption'),
        );
        
        foreach (new DirectoryIterator(APPLICATION_PATH . '/../public/images/youtube/uploads/') as $file) {
        	/* @var $file DirectoryIterator */
        	if ( !$file->isDot() && $file->isFile() && $file->isReadable() && in_array(pathinfo($file->getFilename(), PATHINFO_EXTENSION), array('jpg','png','gif') ) ) {
        		$files[$file->getFilename()] = $file->getFilename();
        	}
        }
        
        $this->addElement('select', 'thumbselect', array(
            'label'      => X_Env::_('p_youtube_form_category_thumbselect'),
        	'description'=> X_Env::_('p_youtube_form_category_thumbselect_desc'),
			'required'   => true,
        	'multiOptions' => $files,
        	//'decorators' => $decorators
        ));
        
        $this->addElement('file', 'thumbnail', array(
            'label'      => X_Env::_('p_youtube_form_category_thumbnail'),
        	'description'=> X_Env::_('p_youtube_form_category_thumbnail_desc'),
			'required'   => false,
        	'ignore'	 => true,
        	'ignoreNoFile'	=> true,
        	'destination'	=> APPLICATION_PATH . '/../public/images/youtube/uploads/',
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
					'options'       => array( false, 'png,jpg,gif')
				),
			),
			'decorators' => array(
				'CompositeFile',
				/*
			    array('Errors'),
				array('Description', array('tag' => 'p', 'class' => 'description')),
				*/
			)
			
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
        	'salt'	=> 'p_youtube_category_new_salt',
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