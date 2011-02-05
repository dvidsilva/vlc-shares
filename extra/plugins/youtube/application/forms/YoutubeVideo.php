<?php

require_once 'X/Env.php';

class Application_Form_YoutubeVideo extends X_Form
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
        
        $this->addElement('text', 'idYoutube', array(
            'label'      => X_Env::_('p_youtube_form_video_label'),
        	'description'=> X_Env::_('p_youtube_form_video_label_desc'),
            'required'   => true,
            'filters'    => array('StringTrim'),
        	//'decorators' => $decorators
        ));

        $categories = array();
        
        foreach (Application_Model_YoutubeCategoriesMapper::i()->fetchAll() as $category) {
        	/* @var $category Application_Model_YoutubeCategory */
        	$categories[$category->getId()] = $category->getLabel();
        }
        
        $this->addElement('select', 'idCategory', array(
            'label'      => X_Env::_('p_youtube_form_video_idCategory'),
        	'description'=> X_Env::_('p_youtube_form_video_idCategory_desc'),
			'required'   => true,
        	'multiOptions' => $categories,
        	//'decorators' => $decorators
        ));
        
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
        	'required'	=> false,
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
            //'ignore' => true,
        	'salt'	=> 'p_youtube_video_new_salt',
        	//'decorators' => array('ViewHelper')
        ));
        
        $this->addDisplayGroup(array('submit', 'abort', 'csrf'), 'buttons', array('decorators' => $this->getDefaultButtonsDisplayGroupDecorators()));
        
    }
}