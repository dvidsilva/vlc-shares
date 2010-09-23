<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

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
				X_Env::initPlugins($configs->plugins);
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
}

