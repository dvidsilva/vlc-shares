<?php

require_once 'X/Controller/Action.php';

class GconfigsController extends X_Controller_Action
{
	
	/**
	 *
	 * @var Application_Form_AutoConfigs
	 */
	private static $configForm = null;
	

    public function indexAction() {
    	$filter = $this->getRequest()->getParam('filter', false);
    	
    	X_Debug::i("Selected filter: {{$filter}}");
    	
    	$this->view->messages = $this->_helper->flashMessenger->getMessages();
    	$this->view->filter = $filter;
    }
    
    public function navtreeAction() {
    	
    	$selected = $this->getRequest()->getParam('filter', false);
    	
    	$configs = Application_Model_ConfigsMapper::i()->fetchAll();
    	$navTree = array();
    	 
    	// parse all configs
    	 
    	foreach ($configs as $config) {
    		/* @var $config Application_Model_Config */
    		if ( $config->getSection() == "plugins" || $config->getSection() == "helpers" ) {
    			// explode the key for 1 dot
    			list($baseKey,) = explode('.', $config->getKey());
    			// check if plugin is enabled or it will be ignored
    			if ( $config->getSection() == "plugins" && !X_VlcShares_Plugins::broker()->isRegistered($baseKey) ) {
    				continue;
    			}
    			
    			$navTree[$config->getSection()]["{$config->getSection()}:{$baseKey}"] = X_Env::_("p_".strtolower($baseKey)."_conf_title");
    		} else {
    			//list($baseKey,) = explode('.', $config->getKey());
    			$navTree['global'][$config->getSection()] = X_Env::_("config_sections_{$config->getSection()}");
    		}
    	
    	}
    	 
    	$this->view->selected = $selected;
    	$this->view->navtree = $navTree;
    	
    }
    
    public function formAction() {
    	
    	/* @var $request Zend_Controller_Request_Http */
    	$request = $this->getRequest();
    	
    	$filter = $request->getParam('filter', false);
    	if ( !$filter ) {
    		//$this->_helper->redirector('index', 'gconfigs');
    		$this->_helper->viewRenderer->setNoRender(true);
    		return;
    	}
    	
    	@list($section, $key) = explode(':', $filter, 2);
    	X_Debug::i("Filter: section = {{$section}}, baseKey = {{$key}}");
    	
    	/*
    	if ( $section == 'plugins' && $baseKey ) {
    		$this->_forward('index', 'config', 'default', array('key' => $baseKey));
    	}
    	*/
    	
    	if ( $section && $key ) {
    		if ( $section == 'plugins' && !X_VlcShares_Plugins::broker()->isRegistered($key) ) {
    			throw new Exception("Invalid plugin {{$key}}");
    		} else {
    			$configs = Application_Model_ConfigsMapper::i()->fetchBySectionNamespace($section, $key);
    		}
    	} elseif ( $section ) {
    		$configs = Application_Model_ConfigsMapper::i()->fetchBySection($section);
    	} 
    	$form = $this->_initConfigsForm($section, $key, $configs);
    	
    	$defaultValues = array();
    	foreach($configs as $config) {
    		/* @var $config Application_Model_Config */
    		$elementName = $config->getSection().'_'.str_replace('.', '_', $config->getKey());
    		$defaultValues[$elementName] = $config->getValue();
    	}
    	 
    	$form->setDefaults($defaultValues);
    	 
    	
    	$this->view->form = $form;
    	$this->view->section = $section;
    	$this->view->key = $key;
    	
    	
    	
    }
    
    
    // save configs
    public function saveAction() {

    	/* @var $request Zend_Controller_Request_Http */
    	$request = $this->getRequest();
    	
    	
    	$filter = $request->getParam('filter', false);
    	if ( !$filter ) {
    		//$this->_helper->redirector('index', 'gconfigs');
    		$this->_helper->viewRenderer->setNoRender(true);
    		return;
    	}
    	 
    	@list($section, $key) = explode(':', $filter, 2);
    	X_Debug::i("Filter: section = {{$section}}, baseKey = {{$key}}");
    	 
    	if ( $section && $key ) {
    		$configs = Application_Model_ConfigsMapper::i()->fetchBySectionNamespace($section, $key);
    	} elseif ( $section ) {
    		$configs = Application_Model_ConfigsMapper::i()->fetchBySection($section);
    	} 
    	    	
    	if ( $request->isPost() ) {
    		$form = $this->_initConfigsForm($section, $key, $configs, $request->getPost());
    		if ( !$form->isErrors() ) {
    			$post = $request->getPost();
    			$isError = false;
    			foreach ( $configs as $config ) {
    				/* @var $config Application_Model_Config */
    				
    				$postStoreName = $config->getSection()."_".str_replace('.', '_', $config->getKey());
    				$postValue = $request->getPost($postStoreName);
    				
    				if ( array_key_exists($postStoreName, $post) && $config->getValue() != $postValue ) {
    					// new value
    					try {
    						$config->setValue($postValue);
    						Application_Model_ConfigsMapper::i()->save($config);
    						if ( stripos($config->getKey(), 'password') != false ) {
    							X_Debug::i("New config: {$config->getSection()}.{$config->getKey()} = ***********");
    						} else {
    							X_Debug::i("New config: {$config->getSection()}.{$config->getKey()} = {$config->getValue()}");
    						}
    					} catch (Exception $e) {
    						$isError = true;
    						$this->_helper->flashMessenger(X_Env::_('configs_save_err_db').": {$e->getMessage()}");
    					}
    				} 
    			}
    			if (!$isError) {
    				$this->_helper->flashMessenger(X_Env::_('configs_save_done'));
    			}
    				
				$this->_helper->redirector('index', 'gconfigs', null, array('filter' => $this->getRequest()->getParam('filter')));
				    			
    		} else {
    			$this->_forward('index', 'gconfigs', null, array('filter' => $this->getRequest()->getParam('filter')));
    		}
    		
    	} else {
    		$this->_helper->flashMessenger(X_Env::_('configs_save_nodata'));
    		$this->_helper->redirector('index', 'configs');
    	}
    	
    }
    
    public function uninstallAction() {
    	$this->_helper->flashMessenger(X_Env::_('configs_plugins_uninstall_disabled'));
    	$this->_helper->redirector('index','configs');
    }
    
    public function disableAction() {
    	
    	/* @var $request Zend_Controller_Request_Http */
    	$request = $this->getRequest();
    	$pluginId = $request->getParam('pluginId', false);
		$plugin = new Application_Model_Plugin();
		
		$csrfValue = $request->getParam('csrf', false);

		$csrf = new Zend_Form_Element_Hash('csrf', array(
			'salt'  => __CLASS__
		));
		
		if ( $csrf->isValid($csrfValue) ) {
		
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
		} else {
			$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('configs_plugins_err_invalidtoken')));
		}
	    	    	
    	$this->_helper->redirector('index','configs');
    	
    }
    
    public function enableAction() {

    	/* @var $request Zend_Controller_Request_Http */
    	$request = $this->getRequest();
    	$pluginId = $request->getParam('pluginId', false);
		$plugin = new Application_Model_Plugin();
		
		$csrfValue = $request->getParam('csrf', false);

		$csrf = new Zend_Form_Element_Hash('csrf', array(
			'salt'  => __CLASS__
		));
		
		if ( $csrf->isValid($csrfValue) ) {
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
		} else {
			$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('configs_plugins_err_invalidtoken')));
		}
		
		
    	$this->_helper->redirector('index', 'configs');
    	
    }
    
    
    public function autosearchAction() {
    	
		$autosearch_LINUX = array(
			'/usr/bin/vlc',
			'/bin/vlc',
			'/usr/local/bin/vlc',
		);
		
		
		$autosearch_WINDOWS = array(
			'C:\\Programmi\\VideoLan\\Vlc\\vlc.exe',
			'C:\\Programmi\\Vlc\\vlc.exe',
			'C:\\Programmi\\VideoLan\\vlc.exe',
			'C:\\Program files\\VideoLan\\Vlc\\vlc.exe',
			'C:\\Program files\\Vlc\\vlc.exe',
			'C:\\Program files\\VideoLan\\vlc.exe',
			'C:\\Program files (x86)\\VideoLan\\Vlc\\vlc.exe',
			'C:\\Program files (x86)\\Vlc\\vlc.exe',
			'C:\\Program files (x86)\\VideoLan\\vlc.exe',
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
    
    public function browseAction() {
    	
    	/* @var $request Zend_Controller_Request_Http */
    	$request = $this->getRequest();
    	
    	$path = X_Env::decode($request->getParam('p', ''));
    	$filter = $request->getParam('f', 'file');
		$callback = $request->getParam('c', 'void');
		
    	$return = array();
    	
    	// i can't browse inside a file :)
    	if ( !is_dir($path) ) $path = dirname($path);
    	
		$path = realpath($path);
		if (is_dir($path) && is_readable($path)) { 
			$objects = scandir($path); 
			foreach ($objects as $object) { 
				if ($object != "." ) {
					if (@filetype($path."/".$object) == "dir" && is_readable($path."/".$object) ) {
						$return[] = array(
							'type' => 'folder',
							'path'	=> $path."/".$object.'/',
							'label'	=> $object.'/',
							'href'	=> $this->_helper->url(
								'browse',
								'configs',
								'default',
								array(
									'f' => $filter,
									'p' => X_Env::encode($path."/".$object),
									'c' => $callback
								)
							)
						);
					} elseif ($filter == 'file') {
						//$array["/".$dir."/".$object] = md5_file($dir."/".$object);
						$return[] = array(
							'type' => 'file',
							'path'	=> realpath($path."/".$object),
							'label'	=> $object,
							'c' => $callback
						);
					}
				} 
			}
	   		reset($objects);
		}

		usort($return, array(__CLASS__, 'sortFolderBased'));
		
		$return = array(
			'path' => $path,
			'filter' => $filter,
			'items' => $return
		);
    	
    	if ( $request->isXmlHttpRequest() ) {
    		
    		$this->_helper->json($return);
    		
    	} else {
    		$this->_helper->layout()->disableLayout();
    		$this->view->callback = $callback;
    		$this->view->path = $return['path'];
    		$this->view->filter = $return['filter'];
    		$this->view->items = $return['items']; 
    	}
    }
    
    
    private function _initConfigsForm($section, $namespace, $configs = array(), $posts = null) {
    	 
    	if ( self::$configForm === null ) {
    		self::$configForm = new Application_Form_AutoConfigs($configs);
    
    		/*
    		if ( $posts !== null && is_array($posts)  ) {
    			self::$configForm->setDefaults($posts);
    		}
    		*/
    
    		if ( $section == 'general' || $section == 'helpers' || $section == 'vlc' ) {
    			// call general handler
    			$this->prepareGeneralElementsForm(self::$configForm);
    		} else {
    		
	    		foreach (self::$configForm->getElements() as $key => $element) {
	    			// plugins prepare known elements
	    			X_VlcShares_Plugins::broker()->prepareGConfigsElement($section, $namespace, $key, $element, self::$configForm, $this);
	    		}
    		}
    		
    
    		if ( $posts !== null && is_array($posts)  ) {
    			self::$configForm->isValid($posts);

    			foreach (self::$configForm->getElements() as $key => $element) {
    				// after isValid, plugin triggered for check GConfigs elements
    				X_VlcShares_Plugins::broker()->checkGConfigsElement($section, $namespace, $key, $element, self::$configForm, @$posts[$key], $this);
    			}
    			
    		}
    
    	}
    
    	return self::$configForm;
    }


    private function prepareGeneralElementsForm(Application_Form_AutoConfigs $form) {
   
   		try {
   			if ($form->getElement('general_languageFile') ) {
   				$languages = array();
   				foreach ( new DirectoryIterator(APPLICATION_PATH ."/../languages/") as $entry ) {
   					if ( $entry->isFile() && pathinfo($entry->getFilename(), PATHINFO_EXTENSION) == 'ini' ) {
   						if ( count(explode('.',$entry->getFilename())) == 2 ) {
   							$languages[$entry->getFilename()] = $entry->getFilename();
   						}
   					}
   				}
   				
   				$form->general_languageFile->setMultiOptions($languages);
   			}
   		} catch(Exception $e) {
   			//X_Debug::w("No language settings? O_o");
   		}
   
   		try {
   			if ($form->getElement('general_debug_level') ) {
	   			$form->general_debug_level->setMultiOptions(array(
	   					'-1' => '-1: '. X_Env::_('config_debug_level_optforced'),
	   					'0' => '0: '. X_Env::_('config_debug_level_optfatal'),
	   					'1' => '1: '. X_Env::_('config_debug_level_opterror'),
	   					'2' => '2: '. X_Env::_('config_debug_level_optwarning'),
	   					'3' => '3: '. X_Env::_('config_debug_level_optinfo'),
	   			));
   			}
   		} catch(Exception $e) {
   			//X_Debug::w("No debug level settings? O_o");
   		}
   
   		try {
   			if ($form->getElement('vlc_version') ) {
	   			$form->vlc_version->setMultiOptions(array(
	   					'1.1.x' => '<= 1.1.x',
	   					'2.x' => '2.x',
	   			));
   			}
   		} catch(Exception $e) {
   			//X_Debug::w("No vlc version setting? O_o");
   		}
   
   		try {
   			if ($form->getElement('helpers_devices_gui') ) {
    			$_guis = X_VlcShares_Plugins::broker()->getPlugins();
    			$guis = array();
    			foreach ($_guis as $gui) {
    				if ( $gui instanceof X_VlcShares_Plugins_RendererInterface ) {
    					$guis[get_class($gui)] = "{$gui->getName()} - {$gui->getDescription()}";
    				}
    			}
    			$form->helpers_devices_gui->setMultiOptions($guis);
   			}
   		} catch (Exception $e) {
   			//X_Debug::w("No gui settings");
   		}
   
   		try {
   			if ($form->getElement('helpers_devices_profile') ) {
    			$_profiles = Application_Model_ProfilesMapper::i()->fetchAll();
    			foreach ($_profiles as $profile) {
    				$profiles[$profile->getId()] = "{$profile->getId()} - {$profile->getLabel()}";
    			}
    			$form->helpers_devices_profile->setMultiOptions($profiles);
   			}
   		} catch (Exception $e) {
   			//X_Debug::w("No gui settings");
   		}
    	
    }    
    
	static function sortFolderBased($item1, $item2) {
		
		// prevent warning for array modification
		$type1 = $item1['type'];
		$type2 = $item2['type'];
		
		if ( $type1 == 'folder' ) {
			if ( $type2 == 'folder' ) {
				return self::sortAlphabetically($item1, $item2);
			} else {
				return -1;
			}
		} else {
			if ( $type2 == 'folder' ) {
				return 1;
			} else {
				return self::sortAlphabetically($item1, $item2);
			}
		}
	}
	
	static function sortAlphabetically($item1, $item2) {
		// prevent warning for array modification
		$label1 = $item1['label'];
		$label2 = $item2['label'];
		
		return strcasecmp($label1, $label2);
	}
    
}

