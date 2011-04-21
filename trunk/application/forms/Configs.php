<?php

require_once 'X/Env.php';

class Application_Form_Configs extends X_Form
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
        $this->setName('configs');
 
        $sections = Application_Model_ConfigsMapper::i()->fetchSections();
        
        // general section go on top

        $displayGroup = array('general' => array());
        
        $configs = $this->configs;
        
        foreach ( $configs as $config ) {
        	/* @var $config Application_Model_Config */
        	
        	if ( $config->getSection() == 'plugins') continue;
        	
        	$elementType = ''; 
        	$defaultStr = null;
        	switch ($config->getType()) {
        		
        		case Application_Model_Config::TYPE_RADIO: $elementType = 'radio';
        			$defaultStr = $config->getDefault();
        			break;
        			
        		case Application_Model_Config::TYPE_BOOLEAN: $elementType = 'radio';
        			$opt = array(1 => X_Env::_('configs_options_yes'), 0 => X_Env::_('configs_options_no') );
        			$defaultStr = $opt[$config->getDefault()];
        			break;
        		
        		case Application_Model_Config::TYPE_TEXTAREA: $elementType = 'textarea';
        			$defaultStr = $config->getDefault(); 
        			break;
        		case Application_Model_Config::TYPE_SELECT: $elementType = 'select';
        			$defaultStr = $config->getDefault(); 
        			break;
				// case Application_Model_Config::TYPE_FILE: $elementType = 'file'; break; // TODO check for it        		
        		case Application_Model_Config::TYPE_TEXT:
        		default: $elementType = 'text';
        			$defaultStr = $config->getDefault();
        			break;
        	}
        	
        	$elementName = $config->getSection().'_'.str_replace('.', '_', $config->getKey());
        	
        	$elementLabel = ($config->getLabel() != null && $config->getLabel() != '' ? X_Env::_($config->getLabel()) : $config->getKey() );
        	$elementDescription = ($config->getDescription() ? X_Env::_($config->getDescription()) . '<br/>' : '' ) . ($config->getDefault() != null ?  "<br/><i>Default:</i> ".$defaultStr : '<br/><i>Default:</i> '.X_Env::_('configs_options_novalue')); 
        	
        	$element = $this->createElement($elementType, $elementName, array(
        		'label'			=> $elementLabel,
        		'description'	=> $elementDescription,
        		/*
        		'options'		=> array(
        			'class'			=> $config->getClass()
        		)
        		*/
        	));       	
        	$element->getDecorator('composite')->setOption('class', $config->getClass());
        	/*
        	$element->getDecorator('description')->setEscape(false);
        	$element->getDecorator('htmlTag')->setOption('class', $config->getClass());
        	$element->getDecorator('label')->setOption('class', $element->getDecorator('label')->getOption('class') . ' ' . $config->getClass());
        	*/
        	
        	if ( $config->getType() == Application_Model_Config::TYPE_BOOLEAN) {
        		$element->setMultiOptions(array(1 => X_Env::_('configs_options_yes'), 0 => X_Env::_('configs_options_no') ));
        	}
        	
        	$this->addElement($element);
        	
        	if ( array_key_exists($config->getSection(), $displayGroup)) {
        		$displayGroup[$config->getSection()][] = $elementName;
        	} else {
        		$displayGroup[$config->getSection()] = array($elementName);
        	}
        	
        }
        
        foreach ($displayGroup as $section => $group) {
        	$this->addDisplayGroup($group, $section, array(
        		'legend' => X_Env::_("config_sections_$section")
        	));
        }
        
        
        
        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => X_Env::_('configs_form_submit'),
        	//'decorators' => array('ViewHelper')
        ));

        // Add the submit button
        $this->addElement('hidden', 'isapply', array(
            'ignore'   => true,
        	'value'	   => 0,
        	'decorators' => array('ViewHelper')
        ));
        
        
        // Add the submit button
        $this->addElement('reset', 'abort', array(
        	//'onClick'	=> 'javascript:history.back()',
            'ignore'   => true,
            'label'    => X_Env::_('configs_form_reset'),
        	//'decorators' => array('ViewHelper')
        ));
 
        // And finally add some CSRF protection
        $this->addElement('hash', 'csrf', array(
        	'salt'	=> 'configs',
            'ignore' => true,
        	//'decorators' => array('ViewHelper')
        ));
        
		$this->addElement('hidden', 'redirect', array(
			'value' => '',
			'ignore' => true,
			'decorators' => array('ViewHelper')
		));        
        
        $this->addDisplayGroup(array('submit', 'abort', 'csrf', 'isapply', 'redirect'), 'buttons', array('decorators' => $this->getDefaultButtonsDisplayGroupDecorators())); 
        
    }
}