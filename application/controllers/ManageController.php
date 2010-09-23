<?php

require_once 'X/Controller/Action.php';

class ManageController extends X_Controller_Action
{
	protected $vlc = null;
	
	/**
	 * @var Application_Form_Configs
	 */
	protected $configForm = null;

    public function init()
    {
        /* Initialize action controller here */
    	parent::init();
    	$this->vlc = new X_Vlc($this->options->vlc);
    }

    /**
     * Show the dashboard
     */
    public function indexAction() {
    	
    	$coreLinks = array(
    		array(
    			'label'		=>	X_Env::_('manage_goto_browse'),
    			'link'		=>	$this->_helper->url('collections', 'index'),
    			'icon'		=>	'/images/manage/browse.png',
    			'title'		=>	X_Env::_('manage_goto_browsetitle'),
    		),
    		array(
    			'label'		=>	X_Env::_('manage_goto_test'),
    			'link'		=>	$this->_helper->url('index', 'test'),
    			'icon'		=>	'/images/manage/test.png',
    			'title'		=>	X_Env::_('manage_goto_testtitle'),
    		),
    		array(
    			'label'		=>	X_Env::_('manage_goto_configs'),
    			'link'		=>	$this->_helper->url('configs', 'manage'),
    			'icon'		=>	'/images/manage/configs.png',
    			'title'		=>	X_Env::_('manage_goto_configstitle'),
    		),
    	);

    	$messages = X_VlcShares_Plugins::broker()->getIndexMessages($this);
    	
    	$news = X_VlcShares_Plugins::broker()->getIndexNews($this);
    	
    	// fetch links from plugins
		$actionLinks = X_VlcShares_Plugins::broker()->getIndexActionLinks($this);
		$manageLinks = X_VlcShares_Plugins::broker()->getIndexManageLinks($this);
		$statistics = X_VlcShares_Plugins::broker()->getIndexStatistics($this);
		
		
		
		
		$this->view->version = X_VlcShares::VERSION;
		//$this->view->configPath = X_VlcShares::config();
		//$this->view->test = $this->_helper->url('index', 'test');
		//$this->view->pcstream = $this->_helper->url('pcstream', 'index');
		//$this->view->pcstream_enabled = ($this->options->pcstream->get('commanderEnabled', false) && $this->vlc->isRunning());
		$this->view->coreLinks = $coreLinks;
		$this->view->actionLinks = $actionLinks;
		$this->view->manageLinks = $manageLinks;
		$this->view->statistics = $statistics;
		$this->view->news = $news;
		$this->view->messages = $messages;
    	
		
		// i need to get first class links
		
    }
    
    /**
     * Show configs page
     */
    public function configsAction() {

    	$configs = Application_Model_ConfigsMapper::i()->fetchAll();
    	
    	$form = $this->_initConfigsForm($configs);

    	$defaultValues = array();
    	foreach($configs as $config) {
    		/* @var $config Application_Model_Config */
    		$elementName = $config->getSection().'_'.str_replace('.', '_', $config->getKey());
    		$defaultValues[$elementName] = $config->getValue();
    	}
    	
    	$form->setDefaults($defaultValues);
    	
    	$plugins = Application_Model_PluginsMapper::i()->fetchAll();
    	
    	$this->view->plugins = $plugins;
    	$this->view->form = $form;
    	$this->view->messages = array_merge($this->_helper->flashMessenger->getMessages(), $this->_helper->flashMessenger->getCurrentMessages()) ;
    	
    }
    
    // save configs
    public function saveAction() {

    	/* @var $request Zend_Controller_Request_Http */
    	$request = $this->getRequest();
    	
    	$configs = Application_Model_ConfigsMapper::i()->fetchAll();
    	
    	if ( $request->isPost() ) {
    		$form = $this->_initConfigsForm($configs, $request->getPost());
    		if ( !$form->isErrors() ) {
    			$post = $request->getPost();
    			$isError = false;
    			foreach ( $configs as $config ) {
    				/* @var $config Application_Model_Config */
    				if ( $config->getSection() == 'plugins' ) continue; // plugins config will not be handled here
    				
    				$postStoreName = $config->getSection()."_".str_replace('.', '_', $config->getKey());
    				
    				if ( array_key_exists($postStoreName, $post) && $config->getValue() != $request->getPost($postStoreName) ) {
    					// new value
    					try {
    						$config->setValue($request->getPost($postStoreName));
    						Application_Model_ConfigsMapper::i()->save($config);
    						X_Debug::i("New config: {$config->getKey()} = {$config->getValue()}");
    					} catch (Exception $e) {
    						$isError = true;
    						$this->_helper->flashMessenger(X_Env::_('configs_save_err_db').": {$e->getMessage()}");
    					}
    				} 
    			}
    			if (!$isError) {
    				$this->_helper->flashMessenger(X_Env::_('configs_save_done'));
    			}
    			$this->_helper->redirector('configs', 'manage');
    			
    		} else {
    			$this->_forward('configs');
    		}
    	} else {
    		$this->_helper->flashMessenger(X_Env::_('configs_save_nodata'));
    		$this->_helper->redirector('configs', 'manage');
    	}
    	
    }
    
    public function uninstallAction() {
    	$this->_helper->flashMessenger(X_Env::_('configs_plugins_uninstall_disabled'));
    	$this->_helper->redirector('configs');
    }
    
    public function disableAction() {
    	
    	/* @var $request Zend_Controller_Request_Http */
    	$request = $this->getRequest();
    	$pluginId = $request->getParam('pluginId', false);
		$plugin = new Application_Model_Plugin();
    	
    	if ( $pluginId !== false ) {
    		Application_Model_PluginsMapper::i()->find($pluginId, $plugin);
    		if ( $plugin->getId() != null && $plugin->getId() == $pluginId ) {
    			if ( $plugin->getType() != Application_Model_Plugin::SYSTEM ) {
    				try {
	    				$plugin->setEnabled(false);
	    				Application_Model_PluginsMapper::i()->save($plugin);
	    				$this->_helper->flashMessenger(X_Env::_('configs_plugins_plugindisabled'));
    				} catch ( Exception $e) {
    					$this->_helper->flashMessenger(X_Env::_('configs_plugins_err_db').": {$e->getMessage()}");
    				}
    			} else {
    				$this->_helper->flashMessenger(X_Env::_('configs_plugins_err_pluginId_notdisable'));
    			}
    		} else {
    			$this->_helper->flashMessenger(X_Env::_('configs_plugins_err_pluginId_unknown'));	
    		} 
    	} else {
    		$this->_helper->flashMessenger(X_Env::_('configs_plugins_err_pluginId_missing'));
    	}
    	
    	$this->_helper->redirector('configs');
    	
    }
    
    public function enableAction() {

    	/* @var $request Zend_Controller_Request_Http */
    	$request = $this->getRequest();
    	$pluginId = $request->getParam('pluginId', false);
		$plugin = new Application_Model_Plugin();
    	
    	if ( $pluginId !== false ) {
    		Application_Model_PluginsMapper::i()->find($pluginId, $plugin);
    		if ( $plugin->getId() != null && $plugin->getId() == $pluginId ) {
    			if ( $plugin->getType() != Application_Model_Plugin::SYSTEM ) {
    				try {
	    				$plugin->setEnabled(true);
	    				Application_Model_PluginsMapper::i()->save($plugin);
	    				$this->_helper->flashMessenger(X_Env::_('configs_plugins_pluginenabled'));
    				} catch ( Exception $e) {
    					$this->_helper->flashMessenger(X_Env::_('configs_plugins_err_db').": {$e->getMessage()}");
    				}
    			} else {
    				$this->_helper->flashMessenger(X_Env::_('configs_plugins_err_pluginId_notenable'));
    			}
    		} else {
    			$this->_helper->flashMessenger(X_Env::_('configs_plugins_err_pluginId_unknown'));	
    		} 
    	} else {
    		$this->_helper->flashMessenger(X_Env::_('configs_plugins_err_pluginId_missing'));
    	}
    	
    	$this->_helper->redirector('configs');
    	
    }
    
    
    public function autosearchAction() {
    	
		$autosearch_LINUX = array(
			'/usr/bin/vlc',
			'/bin/vlc',
			'/usr/local/bin/vlc',
		);
		
		
		$autosearch_WINDOWS = array(
			'C:/Programmi/VideoLan/Vlc/vlc.exe',
			'C:/Programmi/Vlc/vlc.exe',
			'C:/Programmi/VideoLan/vlc.exe',
			'C:/Program files/VideoLan/Vlc/vlc.exe',
			'C:/Program files/Vlc/vlc.exe',
			'C:/Program files/VideoLan/vlc.exe',
			'C:/Program files (x86)/VideoLan/Vlc/vlc.exe',
			'C:/Program files (x86)/Vlc/vlc.exe',
			'C:/Program files (x86)/VideoLan/vlc.exe',
		);
		    	
		$searchPath = X_Env::isWindows() ? $autosearch_WINDOWS : $autosearch_LINUX;
		$found = false;
		
		foreach ($searchPath as $path) {
			if ( file_exists($path) ) {
				$found = $path;
				break;
			}
		}
		
		if ( $found !== false ) {
			$found = array('path' => $found, 'isError' => false);
		} else {
			$found = array('path' => '', 'isError' => true);
		}
				
		/*
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		*/
		$this->_helper->json($found);
    	
    }
    
    private function _initConfigsForm($configs, $posts = null) {
    	
    	if ( $this->configForm === null ) {
	    	$this->configForm = new Application_Form_Configs($configs);
	    	
	    	$this->configForm->setAction($this->_helper->url('save', 'manage'));
	    	
	    	$languages = array();
	    	foreach ( new DirectoryIterator(APPLICATION_PATH ."/../languages/") as $entry ) {
	    		if ( $entry->isFile() && pathinfo($entry->getFilename(), PATHINFO_EXTENSION) == 'ini' ) {
	    			$languages[$entry->getFilename()] = $entry->getFilename();
	    		}
	    	}
	    	try {
	    		$this->configForm->general_languageFile->setMultiOptions($languages);
	    	} catch(Exception $e) { X_Debug::w("No language settings? O_o"); }
	    	
	    	try {
	    		$this->configForm->general_debug_level->setMultiOptions(array(
	    			'-1' => X_Env::_('config_debug_level_optforced'),
	    			'0' => X_Env::_('config_debug_level_optfatal'),
	    			'1' => X_Env::_('config_debug_level_opterror'),
	    			'2' => X_Env::_('config_debug_level_optwarning'),
	    			'3' => X_Env::_('config_debug_level_optinfo'),
	    		));
	    	} catch(Exception $e) { X_Debug::w("No debug level settings? O_o"); }
    	}
    	
    	if ( $posts !== null && is_array($posts)  ) {
    		$this->configForm->isValid($posts);
    	}

    	return $this->configForm;
    }
    
}

