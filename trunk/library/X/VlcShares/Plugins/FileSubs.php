<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/Plx.php';
require_once 'X/Plx/Item.php';
require_once 'Zend/Config.php';
require_once 'Zend/Controller/Request/Abstract.php';

class X_VlcShares_Plugins_FileSubs extends X_VlcShares_Plugins_Abstract {
	
	const FILE = 'file';
	const STREAM = 'stream';

	public function __construct() {
		
		$this->setPriority('getModeItems')
			->setPriority('preGetSelectionItems')
			->setPriority('getSelectionItems');
		
		
	}	


	/**
	 * Give back the link for change modes
	 * and the default config for this location
	 * 
	 * @param string $provider
	 * @param string $location
	 * @param Zend_Controller_Action $controller
	 */
	public function getModeItems($provider, $location, Zend_Controller_Action $controller) {
		
		// check for resolvable $location
		// this plugin is useless if i haven't an access
		// to the real location (url for stream or path for file) 
		$provider = X_VlcShares_Plugins::broker()->getPlugins($provider);
		if ( !( $provider instanceof X_VlcShares_Plugins_ResolverInterface ) ) {
			return;
		} 
		
		$urlHelper = $controller->getHelper('url');

		$subLabel = X_Env::_('p_filesubs_selection_none');

		$subParam = $controller->getRequest()->getParam($this->getId(), false);
		
		if ( $subParam !== false ) {
			$subParam = base64_decode($subParam);
			list($type, $source) = explode(':', $subParam, 2);
			$subLabel = X_Env::_("p_filesubs_subtype_$type")." ($source)";
		}
		
		
		return array(
			array(
				'label'	=>	X_Env::_('p_filesubs_sub').": $subLabel",
				'link'	=>	X_Env::completeUrl($urlHelper->url(array(
						'action'	=>	'selection',
						'pid'		=>	$this->getId()
					), 'default', false)
				)
			)
		);
		
	}

	public function preGetSelectionItems($provider, $location, $pid, Zend_Controller_Action $controller) {
		// we want to expose items only if pid is this plugin
		if ( $this->getId() != $pid) return;
		
		// check for resolvable $location
		// this plugin is useless if i haven't an access
		// to the real location (url for stream or path for file) 
		$provider = X_VlcShares_Plugins::broker()->getPlugins($provider);
		if ( !( $provider instanceof X_VlcShares_Plugins_ResolverInterface ) ) {
			return;
		} 
		
		$urlHelper = $controller->getHelper('url');
		
		return array(
			array(
				'label' => X_Env::_('p_filesubs_selection_title'),
				'link'	=>	X_Env::completeUrl($urlHelper->url()),
			),
		);
	}
	
	public function getSelectionItems($provider, $location, $pid, Zend_Controller_Action $controller) {
		// we want to expose items only if pid is this plugin
		if ( $this->getId() != $pid) return;
		
		// check for resolvable $location
		// this plugin is useless if i haven't an access
		// to the real location (url for stream or path for file) 
		$provider = X_VlcShares_Plugins::broker()->getPlugins($provider);
		if ( !( $provider instanceof X_VlcShares_Plugins_ResolverInterface ) ) {
			return;
		} 
		$providerClass = get_class($provider);
		
		$urlHelper = $controller->getHelper('url');
		
		// i try to mark current selected sub based on $this->getId() param
		// in $currentSub i get the name of the current profile
		$currentSub = $controller->getRequest()->getParam($this->getId(), false);
		if ( $currentSub !== false ) $currentSub = base64_decode($currentSub);

		$return = array(
			array(
				'label'	=>	X_Env::_('p_filesubs_selection_none'),
				'link'	=>	X_Env::completeUrl($urlHelper->url(array(
						'action'	=>	'mode',
						$this->getId() => null, // unset this plugin selection
						'pid'		=>	null
					), 'default', false)
				),
				'highlight' => ($currentSub === false)
			)
		);
		
		// i do the check for this on top
		// location param come in a plugin encoded way
		$location = $provider->resolveLocation($location);
		
		
		// check for infile subs
		$infileSubs = $this->helpers()->stream()->setLocation($location)->getSubsInfo();
		foreach ($infileSubs as $streamId => $sub) {
			X_Debug::i("Valid infile-sub: [{$streamId}] {$sub['language']} ({$sub['format']})");
			$return[] = array(
				'label'	=>	X_Env::_("p_filesubs_subtype_".self::STREAM)." {$streamId} {$sub['language']} {$sub['format']}",
				'link'	=>	X_Env::completeUrl($urlHelper->url(array(
						'action'	=>	'mode',
						'pid'		=>	null,
						$this->getId() => base64_encode(self::STREAM.":{$streamId}") // set this plugin selection as stream:$streamId
					), 'default', false)
				),
				'highlight' => ($currentSub == self::STREAM.":{$streamId}"),
				__CLASS__.':sub' => self::STREAM.":{$streamId}"
			);
		}
		
		// for file system source i will search for subs in filename notation
		if ( is_a($provider, 'X_VlcShares_Plugins_FileSystem') ) {
			
			$dirname = pathinfo($location, PATHINFO_DIRNAME);
			$filename = pathinfo($location, PATHINFO_FILENAME);
			
			$extSubs = $this->getFSSubs($dirname, $filename);
			foreach ($extSubs as $streamId => $sub) {
				X_Debug::i("Valid extfile-sub: {$sub['language']} ({$sub['format']})");
				$return[] = array(
					'label'	=>	X_Env::_("p_filesubs_subtype_".self::FILE)." {$sub['language']} ({$sub['format']})",
					'link'	=>	X_Env::completeUrl($urlHelper->url(array(
							'action'	=>	'mode',
							'pid'		=>	null,
							$this->getId() => base64_encode(self::FILE.":{$streamId}") // set this plugin selection as stream:$streamId
						), 'default', false)
					),
					'highlight' => ($currentSub == self::FILE.":{$streamId}"),
					__CLASS__.':sub' => self::FILE.":{$streamId}"
				);
			}
				
			
		}
		
		// general profiles are in the bottom of array
		return $return;
	}
	
	
	// FIXME
	public function getFSSubs($dirPath, $filename) {

		X_Debug::i("Check for subs in $dirPath for $filename");
		$validSubs = explode('|', $this->config('file.extensions', 'sub|srt|txt'));
		
		$dir = new DirectoryIterator($dirPath);
		$subsFound = array();
		foreach ($dir as $entry) {
			if ( $entry->isFile() ) {
				// se e' un file sub valido
				if ( array_search(pathinfo($entry->getFilename(), PATHINFO_EXTENSION), $validSubs ) !== false ) {
					// stessa parte iniziale
					if ( substr($entry->getFilename(),0,strlen($filename)) == $filename ) {
						X_Debug::i("$entry is valid");
						$subName = substr($entry->getFilename(), strlen($filename));
						$subsFound[$subName] = array(
							'language'	=> trim(pathinfo($subName, PATHINFO_FILENAME), '.'),
							'format'	=> pathinfo($subName, PATHINFO_EXTENSION)
						);						
					} else {
						X_Debug::i("$entry is invalid (no same startwith)");
					}
				} else {
					X_Debug::i("$entry is invalid (not valid format)");
				}
			}
		}
		return $subsFound;
		
	}
	
}
