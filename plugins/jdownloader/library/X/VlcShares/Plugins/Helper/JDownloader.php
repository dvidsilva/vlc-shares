<?php

/**
 * Handle JDownloader comunications.
 * It's a wrapper for RemoteAPI interface
 */
class X_VlcShares_Plugins_Helper_JDownloader extends X_VlcShares_Plugins_Helper_Abstract {
	
	const VERSION_CLEAN = '0.2.1';
	const VERSION = '0.2.1';
	
	private $options;
	
	function __construct(Zend_Config $options = null) {
		
		if ($options == null) {
			$options = new Zend_Config ( array ('ip' => 'localhost', 'port' => '10025', 'timeout' => '1', 'nightly' => false ) );
		}
		$this->options = $options;
		
	}

	/**
	 * Get the list of downloads from Jdownloader
	 * @return array of Application_Model_JDownloaderPackage
	 */
	public function getDownloads() {
		
		if ( $this->options->get('nightly', false) ) {
			$data = $this->sendRawCommand("/get/downloads/all/list");
		} else {
			$data = $this->sendRawCommand(self::CMD_GET_DOWNLOADS_ALL_LIST);
		}
		// time to parse xml
		
		// WHILE NOT DOWNLOADING
		/*
			<?xml version="1.0" encoding="UTF-8" standalone="no"?>
			<jdownloader>
			<package package_ETA="~" package_linksinprogress="0" package_linkstotal="1" package_loaded="0 B" package_name="Added 1298019679499" package_percent="0,00" package_size="82.11 MB" package_speed="0 B" package_todo="82.11 MB">
			<file file_hoster="megavideo.com" file_name="The Big Bang Theory - 1x02 - The Big Bran Hypothes" file_package="Added 1298019679499" file_percent="0,00" file_speed="0" file_status=""/>
			</package>
			</jdownloader>
		 */

		// WHILE DONWLOADING
		/*
			<?xml version="1.0" encoding="UTF-8" standalone="no"?>
			<jdownloader>
			<package package_ETA="14m:37s" package_linksinprogress="1" package_linkstotal="1" package_loaded="1.52 MB" package_name="Added 1298019679499" package_percent="1,85" package_size="82.11 MB" package_speed="94.00 KB" package_todo="80.59 MB">
			<file file_hoster="megavideo.com" file_name="The Big Bang Theory - 1x02 - The Big Bran Hypothes.flv" file_package="Added 1298019679499" file_percent="1,85" file_speed="96256" file_status="ETA 14m:37s @ 94.00 KB/s (1/1)"/>
			</package>
			</jdownloader>
		 */
		
		$xml = new Zend_Dom_Query($data);
		
		if ( $this->options->get('nightly', false) ) {
			$result = $xml->queryXpath('//packages');
		} else {
			$result = $xml->queryXpath('//package');
		}
		
		$packages = array();
		
		while ( $result->valid() ) {
			
			/* @var $domPackage DOMElement */
			$domPackage = $result->current();
			
			$package = new Application_Model_JDownloaderPackage();
			$package->setName($domPackage->getAttribute('package_name'))
				->setETA($domPackage->getAttribute('package_ETA'))
				->setPercent($domPackage->getAttribute('package_percent'))
				->setSize($domPackage->getAttribute('package_size'))
				->setDownloading( ($domPackage->getAttribute('package_linksinprogress') != '0' ) )
				;
				
			/* @var $domFiles DOMNodeList */
			$domFiles = $domPackage->getElementsByTagName('file');
			
			for ( $i = 0; $i < $domFiles->length; $i++ ) {
				
				/* @var $domFile DOMElement */
				$domFile = $domFiles->item($i);
				
				$file = new Application_Model_JDownloaderFile();
				$file->setName($domFile->getAttribute('file_name'))
					->setHoster($domFile->getAttribute('file_hoster'))
					->setPercent($domFile->getAttribute('file_percent'))
					->setDownloading( ($domFile->getAttribute('file_status') != '') );
					;
					
				// add file inside the package
				$package->appendFile($file);
			}
			
			// add package in the list
			$packages[] = $package;
			
			$result->next();
			
		}
		
		return $packages;
		
	}
	
	public function addLink($link, $grabber = false, $autostart = true) {
		if ( is_array($link) ) {
			$link = implode(' ', $link);
		}
		$oldAutoadding = null;
		$oldAutostarting = null;
		if ( $this->options->get('nightly', false) ) {
			
			$oldAutoadding = trim($this->sendRawCommand('/grabber/isset/startafteradding'));
			if ( ($oldAutoadding == 'true' && $grabber == false) ||  ($oldAutoadding == 'false' && $grabber == true) ) {
				$oldAutoadding = null;
			} else {
				$this->sendRawCommand('/set/grabber/autoadding/%s', (!$grabber ? 'true' : 'false'));
			}
			
			$oldAutostarting = trim($this->sendRawCommand('/grabber/isset/autoadding'));
			if ( ($oldAutostarting == 'true' && $autostart == true) || ($oldAutostarting == 'false' && $autostart == true) ) {
				$oldAutostarting = null;
			} else {
				$this->sendRawCommand('/set/grabber/startafteradding/%s', ($autostart ? 'true' : 'false'));
			}
			
		}
		$link = urlencode($link);
		$return = $this->sendRawCommand(self::CMD_ACTION_ADD_LINKS__GRABBERBOOL_STARTBOOL_LINKS, ($grabber ? 1 : 0), ($autostart ? 1 : 0), $link);
		
		if ( $oldAutoadding !== null ) {
			$this->sendRawCommand('/set/grabber/autoadding/%s', $oldAutoadding);
		}
		
		if ( $oldAutostarting !== null ) {
			$this->sendRawCommand('/set/grabber/startafteradding/%s', $oldAutostarting);
		}
		
		
	}
	

	/**
	 * Send a raw command to jdownloader remoteapi interface
	 * and return the output as string
	 * @param string command
	 * @params mixed optional command binds
	 * @throws Exception on connection timeout
	 */
	public function sendRawCommand($command) {
	
		if ( func_num_args() > 1 ) {
			$args = func_get_args();
			array_shift($args);
			$command = vsprintf($command, $args);
		}
		
		// $command has params binded
		$url = "http://{$this->options->get('ip', 'localhost')}:{$this->options->get('port', '10025')}{$command}";

		X_Debug::i("Sending: $url");
		
		/* @var $client Zend_Http_Client */
		$client = new Zend_Http_Client($url, array(
			'timeout'		=> $this->options->get('timeout', 1),
			'keepalive' 	=> true
		));
		
		$client->setHeaders('User-Agent', 'vlc-shares/'.X_VlcShares::VERSION.' jdownloader/'.self::VERSION);
		
		return $client->request(Zend_Http_Client::GET)->getBody();
	}
	
	
	
	
	// ==== COMMANDS (list taken from revision 9568)

	// === Get main information/configuration ===;

	// Get RemoteControl version
	const CMD_GET_RCVERSION = "/get/rcversion";

	// Get version;
	const CMD_GET_VERSION = "/get/version";

	// Get config
	const CMD_GET_CONFIG = "/get/config";
	
	// Get IP;
	const CMD_GET_IP = "/get/ip";

	// Get random IP as replacement for real IP-check;
	const CMD_GET_RANDOMIP = "/get/randomip";

	// Get current speed;
	const CMD_GET_SPEED = "/get/speed";

	// Get current speed limit;
	const CMD_GET_SPEEDLIMIT = "/get/speedlimit";

	// Get download status => RUNNING, NOT_RUNNING or STOPPING;
	const CMD_GET_DOWNLOADSTATUS = "/get/downloadstatus";

	// Get whether reconnect is enabled or not;
	const CMD_GET_ISRECONNECT = "/get/isreconnect";

	
	// === Get linkgrabber information ===;

	// Get all links that are currently held by the link grabber (XML);
	const CMD_GET_GRABBER_LIST = "/get/grabber/list";

	// Get number of all links in linkgrabber;
	const CMD_GET_GRABBER_COUNT = "/get/grabber/count";

	// Get whether linkgrabber is busy or not;
	const CMD_GET_GRABBER_ISBUSY = "/get/grabber/isbusy";

	// Get whether downloads should start or not start after they were added to the download queue;
	const CMD_GET_GRABBER_ISSET_STARTAFTERADDING = "/get/grabber/isset/startafteradding";

	// Get whether packages in the linkgrabber list should be added automatically after their availability was checked.;
	const CMD_GET_GRABBER_ISSET_AUTOADDING = "/get/grabber/isset/autoadding";
	

	// === Get download list information ===;

	// Get number of all downloads;
	const CMD_GET_DOWNLOADS_ALL_COUNT = "/get/downloads/allcount";

	// Get number of current downloads;
	const CMD_GET_DOWNLOADS_CURRENT_COUNT = "/get/downloads/currentcount";

	// Get number of finished downloads;
	const CMD_GET_DOWNLOADS_FINISHED_COUNT = "/get/downloads/finishedcount";

	// Get list of all downloads (XML);
	const CMD_GET_DOWNLOADS_ALL_LIST = "/get/downloads/alllist";

	// Get list of current downloads (XML);
	const CMD_GET_DOWNLOADS_CURRENT_LIST = "/get/downloads/currentlist";

	// Get list of finished downloads (XML);
	const CMD_GET_DOWNLOADS_FINISHED_LIST = "/get/downloads/finishedlist";
	

	// === Set (download-/grabber-)configuration", "set-values ===;

	// Set reconnect enabled or not;
	const CMD_SET_RECONNECT__BOOL = "/set/reconnect/%s";

	// Set premium usage enabled or not;
	const CMD_SET_PREMIUM__BOOL = "/set/premium/%s";

	// Set the general download directory %X%;
	const CMD_SET_DOWNLOADDIR_GENERAL__PATH = "/set/downloaddir/general/%s";

	// Set download speedlimit %X%;
	const CMD_SET_DOWNLOAD_LIMIT__INT = "/set/download/limit/%d";

	// Set max. sim. Downloads %X%;
	const CMD_SET_DOWNLOAD_MAX__INT = "/set/download/max/%d";

	// Set whether downloads should start or not start after they were added to the download queue;
	const CMD_SET_GRABBER_AFTERADDING__BOOL = "/set/grabber/startafteradding/%s";

	// Set whether the packages should be added to the downloadlist and started automatically after linkcheck.;
	const CMD_SET_GRABBER_AUTOADDING__BOOL = "/set/grabber/autoadding/%s";
	

	// === Control downloads ===;

	// Start downloads;
	const CMD_ACTION_START = "/action/start";

	// Pause downloads;
	const CMD_ACTION_PAUSE = "/action/pause";

	// Stop downloads;
	const CMD_ACTION_STOP = "/action/stop";

	// Toggle start/stop all downloads;
	const CMD_ACTION_TOGGLE = "/action/toggle";

	// Reconnect;
	const CMD_ACTION_RECONNECT = "/action/reconnect";
	

	// === Client actions ===;

	// Do a webupdate - /action/forceupdate will activate auto-restart if update is possible;
	const CMD_ACTION_FORCEUPDATE = "/action/forceupdate";

	// Do a webupdate;
	const CMD_ACTION_UPDATE = "/action/update";

	// Restart JDownloader;
	const CMD_ACTION_RESTART = "/action/restart";

	// Shutdown JDownloader;
	const CMD_ACTION_SHUTDOWN = "/action/shutdown";
	

	// === Add downloads ===;

	// Add links %X% to grabber. links have to be urlencoded and splitted by NEWLINE
	const CMD_ACTION_ADD_LINKS__LINKS = "/action/add/links/%s";
	

	// Add (remote or local) container %X%. container link have to be urlencoded
	const CMD_ACTION_ADD_CONTAINER__LINK = "/action/add/container/%s";
	
	// UBUNTU VERSION ONLY?
	/**
	 * Add Links %X% to Grabber
	 *	Optional:
	 *	grabber(0|1): Hide/Show LinkGrabber
	 *	grabber(0|1)/start(0|1): Hide/Show LinkGrabber and start/don't start downloads afterwards
	 *	
	 *	Sample:
	 *	/action/add/links/grabber0/start1/http://tinyurl.com/6o73eq http://tinyurl.com/4khvhn
	 * 	Don't forget Space between Links!
	 */
	const CMD_ACTION_ADD_LINKS__GRABBERBOOL_STARTBOOL_LINKS = '/action/add/links/grabber%d/start%d/%s';

	// === Export download packages ===;

	// Save DLC-container with all links to %X%<br/>" + "e.g. /action/add/container/%X%" + "<p>fromgrabber: save DLC-container from grabber list instead from download list</p>;
	// UNSUPPORTED
	//const CMD_action/save/container(/fromgrabber)/%X% = "/action/save/container(/fromgrabber)/%X%";
	

	// === Edit linkgrabber packages ===;
	
	// Add an archive password %Y% to one or more packages with packagename %X% hold by the linkgrabber, each packagename seperated by a slash);
	// UNSUPPORTED
	//const CMD_action/grabber/set/archivepassword/%X%/%Y% = "/action/grabber/set/archivepassword/%X%/%Y%";

	// Set the download directory %Y% for a specific package %X%;
	const CMD_ACTION_GRABBER_SET_DOWNLOADDIR__PACKAGE_PATH = "/action/grabber/set/downloaddir/%s/%s";

	// Set a comment %Y% for a specific package %X%;
	// UNSUPPORTED
	//const CMD_action/grabber/set/comment/%X%/%Y% = "/action/grabber/set/comment/%X%/%Y%";

	// Rename link grabber package from %X% to %Y%;
	// UNSUPPORTED
	//const CMD_action/grabber/rename/%X%/%Y% = "/action/grabber/rename/%X%/%Y%";

	// Join all denoted linkgrabber packages %Y%, each separated by a slash, to the package %X%;
	// UNSUPPORTED
	//const CMD_action/grabber/join/%X%/%Y% = "/action/grabber/join/%X%/%Y%";

	// Schedule all packages as download that are located in the link grabber;
	const CMD_ACTION_GRABBER_CONFIRMALL = "/action/grabber/confirmall";

	// Schedule all denoted grabber packages %X%, each seperated by a slash, as download;
	// UNSUPPORTED
	//const CMD_action/grabber/confirm/%X% = "/action/grabber/confirm/%X%";

	// Remove links from grabber that match the denoted type(s) %X% - the types must be seperated by a slash. Possible type values: 'offline' for offline links and 'available' for links that are already scheduled as download;
	// UNSUPPORTED
	//const CMD_action/grabber/removetype/%X% = "/action/grabber/removetype/%X%";

	// Remove all links from linkgrabber;
	const CMD_ACTION_GRABBER_REMOVEALL = "/action/grabber/removeall";

	// Remove packages %X% from linkgrabber, each packagename seperated by a slash;
	// UNSUPPORTED
	//const CMD_action/grabber/remove/%X% = "/action/grabber/remove/%X%";

	// Move %Y% (single link or list of links, each separated by NEWLINE char) to package %X%. In case the package given is not available, it will be newly created. Please note that if there are multiple packages named equally, the links will be put into the first one that is found. The term 'link' equals the 'browser url' you've provided previously, not the final download url. Package will be searched by case insensitive search.;
	// UNSUPPORTED
	//const CMD_action/grabber/move/%X%/%Y% = "/action/grabber/move/%X%/%Y%";
	

	// === Edit download packages ===;

	// Remove all scheduled downloads;
	const CMD_ACTION_DOWNLOADS_REMOVEALL = "/action/downloads/removeall";
	
	// Remove packages %X% from download list, each packagename seperated by a slash;
	const CMD_ACTION_DOWNLOADS_REMOVE__PACKAGE = "/action/downloads/remove/%s";
	

	// === Specials ===;
	
	// Check links in %X% without adding them to the linkgrabber or the download list. %X% may be a list of urls. Note: Links must be URLEncoded. Use NEWLINE between links!;
	const CMD_SPECIAL_CHECK__LINK = "/special/check/%s";

}

