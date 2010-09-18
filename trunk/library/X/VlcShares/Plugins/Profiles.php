<?php 

require_once 'X/VlcShares/Plugins/Abstract.php';

/**
 * Expose and allow the selection of different transcoding profiles
 * for vlc
 * 
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Profiles extends X_VlcShares_Plugins_Abstract {
	
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
		
		$urlHelper = $controller->getHelper('url');

		$profileLabel = X_Env::_('p_profiles_selection_auto');

		$profileId = $controller->getRequest()->getParam($this->getId(), false);
		
		if ( $profileId !== false ) {
			$profile = new Application_Model_Profile();
			Application_Model_ProfilesMapper::i()->find($profileId, $profile);
			if ( $profile->getId() != null ) {
				$profileLabel = $profile->getLabel();
			}
		}
		
		return array(
			array(
				'label'	=>	X_Env::_('p_profiles_profile').": $profileLabel",
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
		
		$urlHelper = $controller->getHelper('url');
		
		return array(
			array(
				'label' => X_Env::_('p_profiles_selection_title'),
				'link'	=>	X_Env::completeUrl($urlHelper->url()),
			),
			/*
			array(
				'label' => X_Env::_('p_profiles_selection_current').': '.$profileLabel,
				'link'	=>	X_Env::completeUrl($urlHelper->url()),
			)
			*/
		);
	}
	
	public function getSelectionItems($provider, $location, $pid, Zend_Controller_Action $controller) {
		// we want to expose items only if pid is this plugin
		if ( $this->getId() != $pid) return;
		
		
		$urlHelper = $controller->getHelper('url');
		
		try {
			$provider = X_VlcShares_Plugins::broker()->getPlugins($provider);
			$providerClass = get_class($provider);

			
			
			// i try to mark current selected profile based on $this->getId() param
			// in $profileLabel i get the name of the current profile
			$currentLabel = false;
			$profileId = $controller->getRequest()->getParam($this->getId(), false);
			if ( $profileId !== false ) {
				$_profile = new Application_Model_Profile();
				Application_Model_ProfilesMapper::i()->find($profileId, $_profile);
				if ( $_profile->getId() != null ) {
					$currentLabel = $_profile->getLabel();
				}
			}
			
			
			// if i can resolve the real location of the item
			// i can try to use special profiles
			$codecCond = null; 
			if ( $provider instanceof X_VlcShares_Plugins_ResolverInterface ) {
				
				// location param come in a plugin encoded way
				$location = $provider->resolveLocation($location);

				$codecCond = array();
				
				$this->helpers()->stream()->setLocation($location);
				
				if ( $this->helpers()->stream()->getVideoStreamsNumber() ) {
					$codecCond[] = $this->helpers()->stream()->getVideoCodecName();
				}
				
				
				if ( $this->helpers()->stream()->getVideoStreamsNumber() ) {
					$codecCond[] = $this->helpers()->stream()->getAudioCodecName();
				}

				$codecCond = implode('+', $codecCond);
				if ( $codecCond == '') $codecCond = null;
				
			}

			$profiles = Application_Model_ProfilesMapper::i()->fetchByConds($codecCond, $providerClass);

			
			$return = array(
				array(
					'label'	=>	X_Env::_('p_profiles_selection_auto'),
					'link'	=>	X_Env::completeUrl($urlHelper->url(array(
							'action'	=>	'mode',
							$this->getId() => null, // unset this plugin selection
							'pid'		=>	null
						), 'default', false)
					),
					'highlight' => ($currentLabel === false)
				)
			);
			
			if ( count($profiles) ) {
				X_Debug::i("Valid profiles for $location ($codecCond / $providerClass): ".count($profiles));
			} else {
				X_Debug::e("No valid profiles for $location ($codecCond / $providerClass): i need at least a profile");
			}
			
			// the best is the first
			foreach ($profiles as $profile) {
				/* @var $profile Application_Model_Profile */
				X_Debug::i("Valid profile: [{$profile->getId()}] {$profile->getLabel()} ({$profile->getCondFormats()} / {$profile->getCondProviders()})");
				$return[] = array(
					'label'	=>	$profile->getLabel(),
					'link'	=>	X_Env::completeUrl($urlHelper->url(array(
							'action'	=>	'mode',
							'pid'		=>	null,
							$this->getId() => $profile->getId() // set this plugin selection as profileId
						), 'default', false)
					),
					'highlight' => ($currentLabel == $profile->getLabel())
				);
			}
			
			// general profiles are in the bottom of array
			return $return;
			
		} catch (Exception $e) {
			X_Debug::f("Problem while getting provider obj: {$e->getMessage()}");
			throw $e;
		}
		
	}
	
	private function getBest($location, $provider) {
		
		$provider = X_VlcShares_Plugins::broker()->getPlugins($provider);
		$providerClass = get_class($provider);
		
		$codecCond = array();
		
		$this->helpers()->stream()->setLocation($location);
		
		if ( $this->helpers()->stream()->getVideoStreamsNumber() ) {
			$codecCond[] = $this->helpers()->stream()->getVideoCodecName();
		}
		
		if ( $this->helpers()->stream()->getVideoStreamsNumber() ) {
			$codecCond[] = $this->helpers()->stream()->getAudioCodecName();
		}

		$codecCond = implode('+', $codecCond);
		
		$profile = new Application_Model_Profile();
		
		Application_Model_ProfilesMapper::i()->findBest($codecCond, $providerClass, $profile);
		
		return $profile;
		
	}
}

