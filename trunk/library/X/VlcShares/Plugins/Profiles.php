<?php 

require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/VlcShares/Plugins/BackuppableInterface.php';

/**
 * Expose and allow the selection of different transcoding profiles
 * for vlc
 * 
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_Profiles extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_BackuppableInterface {
	
	public function __construct() {
		$this->setPriority('getModeItems')
			->setPriority('preGetSelectionItems')
			->setPriority('getSelectionItems')
			->setPriority('getIndexManageLinks')
			->setPriority('registerVlcArgs');
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
		
		X_Debug::i('Plugin triggered');
		
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
				),
				'icon'		=>	'/images/manage/plugin.png',
				//'desc'		=>	X_Env::_('p_profiles_profilemode_desc')
			)
		);
		
	}

	public function preGetSelectionItems($provider, $location, $pid, Zend_Controller_Action $controller) {
		// we want to expose items only if pid is this plugin
		if ( $this->getId() != $pid) return;
		
		X_Debug::i('Plugin triggered');
		
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
		
		X_Debug::i('Plugin triggered');
		
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

			$deviceCond = $this->helpers()->devices()->getDeviceType();
			
			$profiles = Application_Model_ProfilesMapper::i()->fetchByConds($codecCond, $deviceCond, $providerClass);

			
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
				X_Debug::i("Valid profiles for $location ($codecCond / $deviceCond): ".count($profiles));
			} else {
				X_Debug::e("No valid profiles for $location ($codecCond / $deviceCond): i need at least a profile");
			}
			
			// the best is the first
			foreach ($profiles as $profile) {
				/* @var $profile Application_Model_Profile */
				X_Debug::i("Valid profile: [{$profile->getId()}] {$profile->getLabel()} ({$profile->getCondFormats()} / {$profile->getCondDevices()})");
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
	

	/**
	 * This hook can be used to add normal priority args in vlc stack
	 * 
	 * @param X_Vlc $vlc vlc wrapper object
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to stream
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 */
	public function registerVlcArgs(X_Vlc $vlc, $provider, $location, Zend_Controller_Action $controller) {

		X_Debug::i('Plugin triggered');
		
		$profileId = $controller->getRequest()->getParam($this->getId(), false);
		
		if ( $profileId !== false ) {
			$profile = new Application_Model_Profile();
			Application_Model_ProfilesMapper::i()->find($profileId, $profile);
		} else {
			// if no params is provided, i will try to
			// get the best profile for this condition
			
			$profile = $this->getBest($location, $this->helpers()->devices()->getDeviceType(), $provider);
		}
		
		if ( $profile->getArg() !== null ) {

			$vlc->registerArg('profile', $profile->getArg());			
			
			if ( $this->config('store.session', false) ) {
				// store the link in session for future use
			}
			
		} else {
			X_Debug::e("No profile arg for vlc");
		}
	
	}
	
	/**
	 * Add the link for -manage-output-
	 * @param Zend_Controller_Action $this
	 * @return array The format of the array should be:
	 * 		array(
	 * 			array(
	 * 				'title' => ITEM TITLE,
	 * 				'label' => ITEM LABEL,
	 * 				'link'	=> HREF,
	 * 				'highlight'	=> true|false,
	 * 				'icon'	=> ICON_HREF,
	 * 				'subinfos' => array(INFO, INFO, INFO)
	 * 			), ...
	 * 		)
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$urlHelper = $controller->getHelper('url');
		
		return array(
			array(
				'title'		=>	X_Env::_('p_profiles_managetitle'),
				'label'		=>	X_Env::_('p_profiles_mlink'),
				'link'		=>	$urlHelper->url(array(
					'controller'	=>	'profiles',
					'action'		=>	'index'
				)),
				//'icon'		=>	'/images/outputs/logo.png',
				'subinfos'	=> array()
			),
		);
	
	}
	
	
	private function getBest($location, $device, $provider) {
		
		$provider = X_VlcShares_Plugins::broker()->getPlugins($provider);
		$providerClass = get_class($provider);
		
		$codecCond = array();
		
		if ( $provider instanceof X_VlcShares_Plugins_ResolverInterface ) {
			
			// location param come in a plugin encoded way
			$location = $provider->resolveLocation($location);
		
			$this->helpers()->stream()->setLocation($location);
			
			if ( $this->helpers()->stream()->getVideoStreamsNumber() ) {
				$codecCond[] = $this->helpers()->stream()->getVideoCodecName();
			}
			
			if ( $this->helpers()->stream()->getVideoStreamsNumber() ) {
				$codecCond[] = $this->helpers()->stream()->getAudioCodecName();
			}
	
			$codecCond = implode('+', $codecCond);
		}
		
		$profile = new Application_Model_Profile();
		
		Application_Model_ProfilesMapper::i()->findBest($codecCond, $device, $providerClass, $profile);
		
		return $profile;
		
	}
	
	
	/**
	 * Backup profiles
	 * This is not a trigger of plugin API. It's called by Backupper plugin
	 */
	function getBackupItems() {
		
		$return = array();
		$models = Application_Model_ProfilesMapper::i()->fetchAll();
		
		foreach ($models as $model) {
			/* @var $model Application_Model_Profile */
			$return['profiles'][] = array(
				'id'			=> $model->getId(), 
	            'arg'   		=> $model->getArg(),
	            'cond_providers' => $model->getCondProviders(),
	        	'cond_formats' 	=> $model->getCondFormats(),
	        	'cond_devices'	=> $model->getCondDevices(),
	        	'label' 		=> $model->getLabel(),
	        	'weight'		=> $model->getWeight()
			);
		}
		
		return $return;
	}
	
	/**
	 * Restore backupped profiles
	 * This is not a trigger of plugin API. It's called by Backupper plugin
	 */
	function restoreItems($items) {
		
		$models = Application_Model_ProfilesMapper::i()->fetchAll();
		// cleaning up all shares
		foreach ($models as $model) {
			Application_Model_ProfilesMapper::i()->delete($model);
		}
	
		foreach (@$items['profiles'] as $modelInfo) {
			$model = new Application_Model_Profile();
			$model->setArg(@$modelInfo['arg'])
				->setCondProviders(@$modelInfo['cond_providers'])
				->setCondFormats(@$modelInfo['cond_formats'])
				->setCondDevices(@$modelInfo['cond_devices'])
				->setLabel(@$modelInfo['label'])
				->setWeight(@$modelInfo['weight'])
				;
			// i don't set id, or db adapter will try to update old data that i cleaned
			Application_Model_ProfilesMapper::i()->save($model);
		}
		
		return X_Env::_('p_profiles_backupper_restoreditems'). ": " .count($items['profiles']);
		
	}
}

