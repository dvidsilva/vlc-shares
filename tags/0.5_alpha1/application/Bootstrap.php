<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	protected function _initApacheAltPort() {
		$this->bootstrap('configs');
		$this->bootstrap('debug');
		
		$configs = $this->getResource('configs');
		if ( $configs instanceof Zend_Config ) {
			try {
				if ( $configs->general->apache_altPort ) {
					X_Env::initForcedPort($configs->general->apache_altPort);
				}
			} catch (Exception $e) {}
		}
	}
	
	protected function _initTranslation() {
		$this->bootstrap('configs');
		$this->bootstrap('debug');
		
		$configs = $this->getResource('configs');
		$translation = null;
		if ( $configs instanceof Zend_Config ) {
			try {
				$translation = new Zend_Translate('ini', $configs->general->get('languageFile', APPLICATION_PATH ."/../languages/en_GB.ini" ));
				X_Env::initTranslator($translation);
			} catch (Exception $e) {
				// no translation available
				X_Debug::e("Translation disabled: {$e->getMessage()}");
			}
		}
		return $translation;
	}
	
	protected function _initConfigs() {
		return new Zend_Config_Ini(X_VlcShares::config());
	}
	
	protected function _initPlugins() {
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
				if ( $configs->general->debug_enabled ) {
					// init debug system:
					// config default:
					//		/tmp/vlcShares.debug.log
					//		log none
					X_Debug::init(
						$configs->general->get('debug_path', sys_get_temp_dir().'/vlcShares.debug.log' ),
						(int) $configs->general->get('debug_level', X_Debug::LVL_NONE)
					);
				}
			} catch (Exception $e) {
				// no init
			}
		}
	}
}

