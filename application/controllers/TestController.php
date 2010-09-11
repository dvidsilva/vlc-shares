<?php

require_once 'Zend/Controller/Action.php';
require_once 'Zend/Config/Ini.php';
require_once 'Zend/Config.php';
require_once 'Zend/Translate.php';
require_once 'X/Env.php';
require_once 'X/VlcShares.php';


class TestController extends Zend_Controller_Action
{
	/**
	 * 
	 * @var Zend_Config
	 */
	protected $options = null;
	

    public function indexAction()
    {
    	
    	$tests = $this->doTests();

    	if ( $this->options )
    		$this->view->log = @file_get_contents($this->options->general->get('debug_path', sys_get_temp_dir() . '/vlcShares.debug.log'));
    	$this->view->tests = $tests;
    	
    }

    public function doTests() {
    	
    	$tests = array();
    	
    	// versione
    	$tests[] = $this->_check('VLC-Share version', true, X_VlcShares::VERSION);

    	// mostra il percorso del file di configurazione
    	$tests[] = $this->_check('Config file is', true, X_VlcShares::config() , '');
    	
    	// controlliamo che il file di configurazione ci sia
    	$tests[] = $this->_check('Config file is available', file_exists(X_VlcShares::config()), 'Success', 'File not found or not readable. Following tests are skipped');
    	// skippa i test successivi
    	if ( !file_exists(X_VlcShares::config()) ) return $tests;

    	// carichiamo le configurazioni
    	$this->options = new Zend_Config_Ini(X_VlcShares::config());
    	
    	//controlliamo che il file di configurazione sia valido
    	$tests[] = $this->_check('Shared collection are >= 2 (workaround)', $this->options->shares->count() >= 2, 'Success', 'Failed');

    	//controlliamo che le collezioni siano ben formate
    	$tests[] = $this->_check('Shared collection format is valid', $this->_shareCheck($this->options->shares->toArray()), 'Success', 'Shared collection format is not valid (or path misses last /)');
    	
    	// controlliamo la path di vlc
    	$tests[] = $this->_check('Vlc path is valid', $this->_vlcPathCheck($this->options->vlc->path), 'Success', 'Path is not valid (or check is failed)');
    	
    	//controlliamo che i profili siano ben formati
    	$tests[] = $this->_check('profiles format is valid', $this->_vlcProfileCheck($this->options->profiles->toArray()), 'Success', 'profiles format is not valid');
    	
    	//controlliamo che nc sia presente
    	$tests[] = $this->_check('Language file is valid', file_exists($this->options->general->languageFile), 'Success', 'Failed');

    	//controlliamo che la porta sia impostata
    	$tests[] = $this->_check('Check Apache altPort', $_SERVER['SERVER_PORT'] == $this->options->general->get('apache_altPort', '80'), 'Success', 'apache_altPort should be '.$_SERVER['SERVER_PORT']);
    	
    	//controlliamo che nc sia presente
    	$tests[] = $this->_check('Debug log', !$this->options->general->debug_enabled, 'Disable', 'Enable (should be enabled for debug only)');
    	
    	//controlliamo la path del file di debug
    	$tests[] = $this->_check('Debug log path', is_writable(dirname($this->options->general->get('debug_path', sys_get_temp_dir() . '/vlcShares.debug.log'))), 'Success', 'Failed');

    	//controlliamo pcstream
    	$tests[] = $this->_check('PCStream is enabled', !$this->options->pcstream->get('commanderEnabled', false), 'False', 'True (should be enabled for tests only)');
    	
    	//controlliamo l'adapter
    	$tests[] = $this->_check('Adapter used', true, 'X_Vlc_Adapter_'.$this->options->vlc->get('adapter',new Zend_Config(array()))->get('name',(X_Env::isWindows()?'Windows':'Linux')), 'Disabled');
    	
    	//controlliamo il commander
    	$tests[] = $this->_check('Commander used', true, $this->options->vlc->get('commander',new Zend_Config(array()))->get('name', 'Default'), 'Disabled');

    	$commander = $this->options->vlc->get('commander',new Zend_Config(array()))->get('name', 'Default');
    	if ( $commander != 'Default' ) {
    		// visualizzo le configurazioni
    		$tests[] = $this->_check('Commander options', true, '<pre>'.print_r($this->options->vlc->commander->toArray(), true).'</pre>', 'Disabled');
    	}
    	
    	
    	// controlliamo la piattaforma
    	$tests[] = $this->_check('OS', true, X_Env::isWindows() ? 'Windows' : 'Linux' , 'Disabled');
		    	
    	
    	if ( X_Env::isWindows() ) {
    		$tests = array_merge($tests, $this->_windowsSpecificTests());
    	} else {
    		$tests = array_merge($tests, $this->_LinuxSpecificTests());
    	}
    	
    	$vlc = new X_Vlc($this->options->vlc);
    	
    	if ( $this->options->pcstream->get('commanderEnabled', false) ) {
    		$runningMsg = 'Yes (<a href="' . X_Env::routeLink('controls', 'pcstream')  . '">go to PCStream interface</a>)';
    	} else {
    		$runningMsg = 'Yes';
    	}
    	
    	$tests[] = $this->_check('Vlc is running', true, $vlc->isRunning() ? $runningMsg : 'No' , 'Disabled');
    	
    	$plugins = array();
    	
    	foreach( $this->options->plugins->toArray() as $pluginKey => $pluginValues ) {
    		$plugins[$pluginKey] = $pluginValues['class'] . (@$pluginValues['path'] ? "( {$pluginValues['path']} )" : '');
    	}
    	
    	$tests[] = $this->_check('Plugins enabled', true, '<pre>'.print_r($plugins, true).'</pre>', 'Disabled');
    	
    	$tests[] = $this->_check('Back to Manage', true, '<a href="' . X_Env::routeLink('index', 'index') . '">Click here</a>', 'Disabled');
    	
    	return $tests;
    	
    }
    
    public function _linuxSpecificTests() {

    	$tests = array();

    	$tests[] = $this->_check('[LINUX-TEST] Adapter is for Linux', $this->options->vlc->get('adapter',new Zend_Config(array()))->get('name', 'Linux') == 'Linux', 'Success', 'Failed');
    	
    	return $tests;
    	
    }
    

    public function _windowsSpecificTests() {
    	
    	$tests = array();
    	
    	$tests[] = $this->_check('[WIN-TEST] Adapter is for Windows', $this->options->vlc->get('adapter',new Zend_Config(array()))->get('name', 'Windows') == 'Windows', 'Success', 'Failed');
    	
		$tests[] = $this->_check('[WIN-TEST] Check if RC commander is used', $this->options->vlc->get('commander',new Zend_Config(array()))->get('name', 'Default') != 'X_Vlc_Commander_Rc', 'No', 'Yes (RC commander should not be used in Windows, it\'s really slow)');    	
    	
    	return $tests;
    	    	
    }
    
    
    private function _check($name, $test, $success = 'Success', $failure = 'Failure') {
    	return array($name, $test, $test ? $success : $failure);
    }

    private function _shareCheck($sharesArray) {
    	foreach ($sharesArray as $share) {
    		if ( !array_key_exists('name', $share) || !array_key_exists('name', $share) ) {
    			return false;
    		}
    		if ( substr($share['path'], -1) != '/' ) {
    			return false;
    		}
    	}
    	return true;
    }
    
    private function _vlcPathCheck($vlcPath) {
    	/*
    	$pos = strpos($vlcPath, '--play-and-exit');
    	if ( $pos !== false ) {
	    	$vlcpath = trim(substr($vlcPath, 0, $pos));
    	}
    	*/
    	if ( substr($vlcPath, 0, 1) == '"') {
		    $vlcPath = substr($vlcPath, 1, -1);
    	}
    	return file_exists($vlcPath); 
    }
    
    private function _vlcProfileCheck($profileArray) {
    	foreach ($profileArray as $profile) {
    		if ( !array_key_exists('name', $profile) || !array_key_exists('args', $profile) ) {
    			return false;
    		}
    	}
    	return true;
    }
    
    private function _ncCheck() {
    	$output = trim(exec('which nc'));
    	if ($output == '') {
    		return false; 
    	} else {
    		return file_exists($output);
    	}
    }
    
}

