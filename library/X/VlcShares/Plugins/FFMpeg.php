<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/Plx.php';
require_once 'X/Plx/Item.php';
require_once 'X/Vlc.php';
require_once 'Zend/Config.php';

class X_VlcShares_Plugins_FFMpeg extends X_VlcShares_Plugins_Abstract {

	/**
	 * 
	 * @var Zend_Config
	 */
	private $options;
	
	private $_registeredEvents = array(
		X_VlcShares::TRG_VLC_SPAWN_PRE	=>	'spawnFFMpeg',
		X_VlcShares::TRG_PROFILES_ADDITIONALS	=>	'getProfiles',
		X_VlcShares::TRG_VLC_ARGS_SUBTITUTE		=>	'getArgs',
	);
	
	public function __construct(Zend_Config $options) {
		$this->options = $options;
		$this->id = $this->options->id;
		$this->registerEvents($this->_registeredEvents);
	}

	/**
	 * 
	 * @param X_Vlc $vlc
	 */
	public function spawnFFMpeg($vlc) {
		
		X_Env::execute('killall ffmpeg', X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
		
		// devo controllare se e' per ffmpeg
		$source = $vlc->getArg('source');
		//$ffmpeg = $vlc->getArg('source');
		
		
		//$vlc_profile = $this->options->get('vlc', new Zend_Config(array()) )->get('profile', new Zend_Config(array()));
		$ffmpeg_path = $this->options->get('ffmpeg', new Zend_Config(array()))->get('path', '/usr/bin/ffmpeg');
		$ffmpeg_args = $this->options->get('ffmpeg', new Zend_Config(array()))->get('args', '-i {%source%} -y -ab 128 -b 1200 {%dest%}');
		$ffmpeg_temp = $this->options->get('ffmpeg', new Zend_Config(array()))->get('tmp', sys_get_temp_dir() . '/ffmpegOutput.mpg');
		$ffmpeg_wait = (int) $this->options->get('ffmpeg', new Zend_Config(array()))->get('wait', 10);
		
		
		// devo sostituire il profilo
		//$vlc->registerArg('profile', $vlc_profile);
		$vlc->registerArg('source', $ffmpeg_temp);
		
		
		$ffmpeg_command = '"'.$ffmpeg_path.'" '.str_replace(array('{%source%}', '{%dest%}'), array($source, "\"$ffmpeg_temp\""), $ffmpeg_args);
		
		// a questo punto devo eseguire ffmpeg
		X_Env::execute($ffmpeg_command, X_Env::EXECUTE_OUT_NONE, X_Env::EXECUTE_PS_WAIT);
		
		sleep($ffmpeg_wait);
		
	}
	
	public function getProfiles() {
		$_profiles = $this->options->get('profiles', new Zend_Config(array()))->toArray();
		foreach ($_profiles as $pKey => $pValue) {
			$profiles['plg:'.$this->getId().':'.$pKey] = $pValue;
		}
		return $profiles;
	}
	
	public function getArgs($argv = array()) {
		
		X_Env::debug(__METHOD__);
		
		$request = $argv[0];
		
		$qId = $request->getParam('qId');
		
		if ( substr($qId, 0, strlen('plg:'.$this->getId())) == 'plg:'.$this->getId()) {
			$_profiles = $this->options->get('profiles', new Zend_Config(array()))->toArray();
			foreach ($_profiles as $pKey => $pValue) {
				$profiles['plg:'.$this->getId().':'.$pKey] = $pValue;
			}
			return array('profile' => $profiles[$qId]['args']);
		} else {
			return array();
		}
	}
	
	
}
