<?php

require_once 'X/Env.php';

class Application_Form_Configs extends Zend_Form
{
	private $configs = array();
	
	function __construct($configs = array(), $options = null) {
		$this->configs = $configs;
		parent::__construct($options);
	}
	
    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');
 
        $sections = Application_Model_ConfigsMapper::i()->fetchSections();
        
        // general section go on top

        $displayGroup = array('general' => array());
        
        $configs = $this->configs;
        
        foreach ( $configs as $config ) {
        	/* @var $config Application_Model_Config */
        	
        	if ( $config->getSection() == 'plugins') continue;
        	
        	$elementType = ''; 
        	
        	switch ($config->getType()) {
        		
        		case Application_Model_Config::TYPE_BOOLEAN: $elementType = 'radio'; break;
        		case Application_Model_Config::TYPE_TEXTAREA: $elementType = 'textarea'; break;
        		case Application_Model_Config::TYPE_SELECT: $elementType = 'select'; break;
				// case Application_Model_Config::TYPE_FILE: $elementType = 'file'; break; // TODO check for it        		
        		case Application_Model_Config::TYPE_TEXT:
        		default: $elementType = 'text';
        			break;
        	}
        	
        	$elementName = $config->getSection().'_'.str_replace('.', '_', $config->getKey());
        	
        	$elementLabel = ($config->getLabel() != null && $config->getLabel() != '' ? X_Env::_($config->getLabel()) : $config->getKey() );
        	$elementDescription = X_Env::_($config->getDescription()) . ($config->getDefault() != null ?  "<br/><i>Default:</i> ".$config->getDefault() : ''); 
        	
        	$element = $this->createElement($elementType, $elementName, array(
        		'label'			=> $elementLabel,
        		'description'	=> $elementDescription,
        	));
        	
        	$element->getDecorator('description')->setEscape(false);
        	
        	if ( $config->getType() == Application_Model_Config::TYPE_BOOLEAN) {
        		$element->setMultiOptions(array(1 => X_Env::_('yes'), 0 => X_Env::_('no') ));
        	}
        	
        	$this->addElement($element);
        	
        	if ( array_key_exists($config->getSection(), $displayGroup)) {
        		$displayGroup[$config->getSection()][] = $elementName;
        	} else {
        		$displayGroup[$config->getSection()] = array($elementName);
        	}
        }
        
        foreach ($displayGroup as $section => $group) {
        	$this->addDisplayGroup($group, $section, array('legend' => "[".X_Env::_("config_sections_$section")."]"));
        }
        
        
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => X_Env::_('configs_form_submit'),
        	'decorators' => array('ViewHelper')
        ));
        
        // Add the submit button
        $this->addElement('button', 'abort', array(
        	'onClick'	=> 'javascript:history.back();',
            'ignore'   => true,
            'label'    => X_Env::_('configs_form_abort'),
        	'decorators' => array('ViewHelper')
        ));
 
        // And finally add some CSRF protection
        $this->addElement('hash', 'csrf', array(
        	'salt'	=> 'configs',
            'ignore' => true,
        	'decorators' => array('ViewHelper')
        ));
        
        $this->addDisplayGroup(array('submit', 'abort', 'hash'), 'buttons');
        
    }
}