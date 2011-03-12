<?php

require_once 'Zend/Controller/Action.php';
require_once 'Zend/Config/Ini.php';
require_once 'Zend/Config.php';
require_once 'Zend/Translate.php';
require_once 'X/Env.php';
require_once 'X/VlcShares.php';
require_once 'X/Page/Item/Test.php';
require_once 'X/Page/ItemList/Test.php';


class TestController extends X_Controller_Action
{

    public function indexAction()
    {
    	
    	$tests = $this->doSystemTests();

    	if ( $this->options ) {
	    	$tests->merge(X_VlcShares_Plugins::broker()->preGetTestItems($this->options, $this));
	    	// normal links
	    	$tests->merge(X_VlcShares_Plugins::broker()->getTestItems($this->options, $this));
	    	// bottom links
			$tests->merge(X_VlcShares_Plugins::broker()->postGetTestItems($this->options, $this));
    		
			$debugPath = sys_get_temp_dir().'/vlcShares.debug.log';
			if ( $this->options->general->debug->path != null && trim($this->options->general->debug->path) != '' ) {
				$debugPath = $this->options->general->debug->path;
			}
    		$this->view->log = @file_get_contents($debugPath);
    	}
    	$this->view->tests = $tests;
    	
    }

    public function reportAction() {
    	
    	$results = array();

    	$results[] = '-----------------------';
    	$results[] = 'vlc-shares: '. X_VlcShares::VERSION;
    	$results[] = 'zend-framework: '. Zend_Version::VERSION;
    	$results[] = '';
    	$results[] = 'Configs:';
    	
    	$vlcshares = Application_Model_ConfigsMapper::i()->fetchAll();
    	foreach ($vlcshares as $conf) {
    		/* @var $conf Application_Model_Config */
    		if ( $conf->getSection() == 'plugins' ) continue;
    		$results[] = "\t{$conf->getSection()}/{$conf->getKey()} = {$conf->getValue()}";
    	}

    	$results[] = '';
    	$results[] = 'Plugins:';
    	$plugins = X_VlcShares_Plugins::broker()->getPlugins();
    	foreach ($plugins as $id => $plugin) $results[] = "\t$id => ".get_class($plugin);
    	
    	$results[] = '';
    	$results[] = 'Tests:';
    	
    	$tests = $this->doSystemTests();
    	if ( $this->options ) {
	    	$tests->merge(X_VlcShares_Plugins::broker()->preGetTestItems($this->options, $this));
	    	// normal links
	    	$tests->merge(X_VlcShares_Plugins::broker()->getTestItems($this->options, $this));
	    	// bottom links
			$tests->merge(X_VlcShares_Plugins::broker()->postGetTestItems($this->options, $this));
    	}
    	
    	foreach ($tests->getItems() as $test) {
    		/* @var $test X_Page_Item_Test */
    		$results[] = "\t{$test->getLabel()} ({$test->getType()}): {$test->getReason()}";
    	}
    	
    	
    	$results[] = ''; $results[] = ''; $results[] = '';
    	$results[] = '-----------------------';
    	$results[] = 'Server:';

    	foreach ($_SERVER as $k => $v) $results[] = "\t$k = $v";
    	
    	
    	// PHP SETTINGS
    	$results[] = ''; $results[] = ''; $results[] = '';
    	$results[] = '-----------------------';
    	$results[] = 'PHP: '.PHP_VERSION.' '.PHP_OS.' '.PHP_SAPI;
    	$results[] = 'Loaded extesions:';
    	$exts = get_loaded_extensions();
    	foreach ($exts as $ext) $results[] = "\t$ext";
    	$results[] = '';
    	
    	
    	$php = ini_get_all(null, false);
    	foreach ($php as $k => $v) $results[] = "\t$k = $v";
    	
    	$this->getResponse()->setHeader('Content-Type', 'plain/text');
    	$this->getResponse()->setHeader('Content-Disposition', 'attachment; filename=vlc-shares_report_'.time().'.txt');
    	$this->_helper->viewRenderer->setNoRender(true);
    	$this->_helper->layout->disableLayout();
    	
    	echo implode(PHP_EOL, $results);
    	
    }
    
    /**
	 * @return X_Page_ItemList_Test
     */
    public function doSystemTests() {
    	
    	$tests = array();
    	
    	$tests[] = $this->_check('VLCShares version', X_Page_Item_Test::TYPE_INFO, X_VlcShares::VERSION);
    	$tests[] = $this->_check('VLC path is valid ('.$this->options->vlc->path.')', $this->_vlcPathCheck($this->options->vlc->path));
    	
    	$tests[] = $this->_check('Language file is valid ('.$this->options->general->languageFile.')', $this->_languageCheck($this->options->general->languageFile));
    	
    	//$tests[] = $this->_check('Mediainfo helper enabled', (boolean) $this->options->helpers->mediainfo->enabled);
    	//if ( $this->options->helpers->mediainfo->enabled ) {
    		//$tests[] = $this->_check('Mediainfo path is valid ('.$this->options->helpers->mediainfo->path.')', $this->_mediainfoCheck($this->options->helpers->mediainfo->path));
    	//}

    	$tests[] = $this->_check('FFMpeg helper enabled', (boolean) $this->options->helpers->ffmpeg->enabled);
    	if ( $this->options->helpers->ffmpeg->enabled ) {
    		$tests[] = $this->_check('FFMpeg path is valid ('.$this->options->helpers->ffmpeg->path.')', $this->_ffmpegCheck($this->options->helpers->ffmpeg->path));
    	}
    	
    	$tests[] = $this->_check("[PHP settings] magic_quotes_gpc = Off", (bool) !get_magic_quotes_gpc(), "Ok", "magic_quotes_gpc is enabled. Please disable it in php.ini" );
    	$tests[] = $this->_check("[PHP settings] register_globals = Off", (bool) !ini_get('register_globals'), "Ok", "register_globals is enabled. Please disable it in php.ini" );
    	
    	
    	$tests = new X_Page_ItemList_Test($tests);
    	
    	return $tests;
    	
    }
    
    
    private function _check($name, $test, $success = 'Success', $failure = 'Failure') {
    	//return array($name, $test, ( ($test === true || $test === null) ? $success : $failure));
    	$t = new X_Page_Item_Test($name, $name);
    	if ( is_bool($test) ) {
	    	if ( $test ) {
	    		$t->setType(X_Page_Item_Test::TYPE_SUCCESS);
	    		$t->setReason($success);
	    	} else {
	    		$t->setType(X_Page_Item_Test::TYPE_ERROR);
	    		$t->setReason($failure);
	    	}
    	} else {
    		$t->setType($test);
    		$t->setReason($success);
    	}
    	return $t;
    }
    
	private function _vlcPathCheck($vlcPath) {
		$vlcPath = trim($vlcPath, '"');
        $exists = file_exists($vlcPath);
        return ( $exists == null ? false : $exists); 
    }
    
    private function _languageCheck($file) {
        $exists = file_exists(APPLICATION_PATH."/../languages/".$file);
        return ( $exists == null ? false : $exists); 
	}
    
	private function _mediainfoCheck($path) {
		if ( file_exists($path) ) {
			if ( X_Env::isWindows() ) {
				// windows check
				if ( is_file($path) ) {
					$filename = pathinfo($path, PATHINFO_BASENAME);
					if ( strtolower($filename) == 'mediainfo.exe') {
						return true;
					}
				}
			} else {
				if ( is_file($path) ) {
					$filename = pathinfo($path, PATHINFO_BASENAME);
					if ( strtolower($filename) == 'mediainfo') {
						return true;
					}
				}
			}
		}
		return false;
	}
	
	private function _ffmpegCheck($path) {
		if ( file_exists($path) ) {
			if ( X_Env::isWindows() ) {
				// windows check
				if ( is_file($path) ) {
					$filename = pathinfo($path, PATHINFO_BASENAME);
					if ( strtolower($filename) == 'ffmpeg.exe') {
						return true;
					}
				}
			} else {
				if ( is_file($path) ) {
					$filename = pathinfo($path, PATHINFO_BASENAME);
					if ( strtolower($filename) == 'ffmpeg') {
						return true;
					}
				}
			}
		}
		return false;
	}
	
}

