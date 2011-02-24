<?php

require_once 'Zend/Translate.php';
require_once ('X/VlcShares/Plugins/Helper/Abstract.php');

class X_VlcShares_Plugins_Helper_Language extends X_VlcShares_Plugins_Helper_Abstract {

	private $lang = 'en_GB.ini';
	
	public function __construct() {
		
		$conf = new Application_Model_Config();
		Application_Model_ConfigsMapper::i()->fetchByKey('languageFile', $conf);
		if ( $conf->getId() !== null ) {
			$this->lang = $conf->getValue();
		}
	}
	
	public function addTranslation($key) {
		$moduleName = $key . '.' . $this->lang;
		// i have to check $moduleName to be sure that the file is inside the languages/ directory
		if ( file_exists(realpath(APPLICATION_PATH . "/../languages/$moduleName")) ) {
			$translate = new Zend_Translate('ini', realpath(APPLICATION_PATH . "/../languages/$moduleName") );
		} elseif ($this->lang != 'en_GB.ini' && file_exists(realpath(APPLICATION_PATH . "/../languages/$key.en_GB.ini"))) {
			// fallback to english translation
			X_Debug::w("Language file not found: $moduleName. Falling back to english translation");
			$translate = new Zend_Translate('ini', realpath(APPLICATION_PATH . "/../languages/$key.en_GB.ini") );
		} else {
			X_Debug::w("Language file not found: $moduleName");
			return false;
		}
		
		// time to append translator to the global instance
		X_Env::initTranslator($translate);
		return true;
	}
}

