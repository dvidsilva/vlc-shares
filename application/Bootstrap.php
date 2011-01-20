<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	// load plugins from extra/plugins/ folder in this is a dev mode
	protected function _initExtraPlugins() {
		$this->bootstrap('debug');
		$this->bootstrap('db');
		$this->bootstrap('plugins');
		$this->bootstrap('frontController');
		$this->bootstrap('view');
		
		// only add them if in development env
		if ( APPLICATION_ENV != 'development' ) {
			return;
		}

		X_Debug::i("Development mode: extra plugins mode enabled");
		
		// check for extra plugins path
		$extraPluginsPath = APPLICATION_PATH . '/../extra/plugins';
		if ( !file_exists($extraPluginsPath) ) {
			X_Debug::w("Extra plugins path not found");
			return;
		}

		$displayError = ini_get('display_errors');
		$displayStartup = ini_get('display_startup_errors');
		$errorReporting = ini_get('error_reporting');
		
		ini_set('display_errors', '1');
		ini_set('display_startup_errors', '1');
		ini_set('error_reporting', 'E_ALL');
		
		// scan /extra/plugins/$directory/ for dev_bootstrap.php file
		// and execute it.
		$directory = new DirectoryIterator($extraPluginsPath);
		foreach ($directory as $entry) {
			/* @var $entry DirectoryIterator */
			// it doesn't allow dotted directories (. and ..) and file/symlink
			if ( $entry->isDot() || !$entry->isDir() ) {
				continue;
			}
			
			$bootstrapFile = $entry->getRealPath() . '/dev_bootstrap.php';
			
			if ( file_exists($bootstrapFile) ) {
				X_Debug::i("Dev bootstrap file found: $bootstrapFile");
				{
					// delegate everything to dev_bootstrap.php
					include_once $bootstrapFile;
					include $extraPluginsPath.'/bootstrap.php';
				}
			} 
		}
		
		ini_set('display_errors', $displayError);
		ini_set('display_startup_errors', $displayStartup);
		ini_set('error_reporting', $errorReporting);
		
	}
	
	protected function _initApacheAltPort() {
		$this->bootstrap('configs');
		$this->bootstrap('debug');
		
		$configs = $this->getResource('configs');
		if ( $configs instanceof Zend_Config ) {
			try {
				if ( $configs->general->apache->port ) {
					X_Env::initForcedPort($configs->general->apache->port);
				}
			} catch (Exception $e) {}
		}
	}
	
	protected function _initTitle() {
		$this->bootstrap('view');
		/* @var $view Zend_View */
		$view = $this->getResource('view');
		$view->headTitle('VLCShares')
             ->setSeparator(' :: ');
	}
	
	protected function _initTranslation() {
		$this->bootstrap('configs');
		$this->bootstrap('debug');
		
		$configs = $this->getResource('configs');
		$translation = null;
		if ( $configs instanceof Zend_Config ) {
			try {
				$translation = new Zend_Translate('ini', APPLICATION_PATH ."/../languages/". $configs->general->get('languageFile', "en_GB.ini" ));
				X_Env::initTranslator($translation);
			} catch (Exception $e) {
				// no translation available
				X_Debug::e("Translation disabled: {$e->getMessage()}");
			}
		}
		return $translation;
	}
	
	protected function _initConfigs() {
		$this->bootstrap('db');

		// TODO cache
		
		// read configuration from the db as an array
		$configA = array();
		$configs = Application_Model_ConfigsMapper::i()->fetchAll();
		foreach ($configs as $config) {
			/* @var $config Application_Model_Config */
			$key = $config->getKey();
			$_array = $config->getValue();
			$exploded = explode('.', $key);
			$_first = true;
			for ( $i = count($exploded) - 1; $i >= 0; $i--) {
				$_array = array($exploded[$i] => $_array);
			}
			$_array = array($config->getSection() => $_array);
			$configA = array_merge_recursive($configA, $_array);
		}
		
		//echo '<pre>'.print_r($configA, true).'</pre>';
		
		return new Zend_Config($configA); 
		//return new Zend_Config_Ini(X_VlcShares::config()); // old way
	}
	
	protected function _initPlugins() {
		$this->bootstrap('db');
		$this->bootstrap('configs');
		$this->bootstrap('debug');
		
		$configs = $this->getResource('configs');
		if ( $configs instanceof Zend_Config ) {
			try {
				//X_Debug::i('Options: '.var_export($configs->helpers, true));
				X_VlcShares_Plugins::init($configs->plugins, $configs->helpers);
			} catch (Exception $e) {
				X_Debug::e("Plugins disabled: {$e->getMessage()}");
			}
		}
	}

	protected function _initDebug() {
		$this->bootstrap('configs');
		
		$configs = $this->getResource('configs');
		
		if ( $configs instanceof Zend_Config ) {
			try {
				if ( $configs->general->debug->enabled ) {
					// init debug system:
					// config default:
					//		/tmp/vlcShares.debug.log
					//		log none
					
					$debugPath = sys_get_temp_dir().'/vlcShares.debug.log';
					if ( $configs->general->debug->path != null && trim($configs->general->debug->path) != '' ) {
						$configs->general->debug->get('path', sys_get_temp_dir().'/vlcShares.debug.log' );
					}
					
					X_Debug::init(
						$debugPath,						
						(int) $configs->general->debug->level
					);
				}
			} catch (Exception $e) {
				// no init
			}
		}
	}
	
	protected function _initViewHelper() {
		/* @var $view Zend_View */
		$view = $this->getResource('view');
		
		$view->addHelperPath(APPLICATION_PATH.'/views/helpers/', 'X_VlcShares_View_Helper_');
		
	}
	
}

