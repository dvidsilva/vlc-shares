<?php 


class X_VlcShares_Plugins_StreamSeeker extends X_VlcShares_Plugins_Abstract {
	
    const VERSION = '0.1alpha';
    const VERSION_CLEAN = '0.1';
	
	function __construct() {
		if ( class_exists('X_VlcShares_Plugins_Utils') && X_VlcShares_Plugins::broker()->isRegistered('cache') ) {
			// plugin functionality work only if Utils available
			//  Utils available if VLCShares > 0.5.5 or PageParserLib installed
			$this
				->setPriority('getModeItems')
				->setPriority('preGetSelectionItems')
				->setPriority('getSelectionItems')
			;
		} else {
			$this
				->setPriority('getIndexMessages')
			;
		}
		$this->setPriority('gen_beforeInit');
	}
	
	/**
	 * Registers streamseeker helper inside the broker
	 */
	public function gen_beforeInit(Zend_Controller_Action $controller) {
		$this->helpers()->language()->addTranslation(__CLASS__);
		$this->helpers()->registerHelper('streamseeker', new X_VlcShares_Plugins_Helper_StreamSeeker(
			new Zend_Config(array(
				'samplesize' => $this->config('sample.size', 100), // samplesize meter is kb
				'mindelta' => $this->config('min.delta', 5), // mindelta meter is minutes
				'cachevalidity' => $this->config('cache.validity', 10) // cachevalidity meter is minutes
			))
		));
		
		// replace hoster helper with the special wrapper
		$this->helpers()->registerHelper('hoster', new X_VlcShares_Plugins_Helper_HosterSSWrapper($this->helpers()->hoster()));
	}
	
	public function getModeItems($provider, $location, Zend_Controller_Action $controller) {
		// if provider is fileSystem, this doesn't work for sure 
		if ( $provider == "fileSystem") return;
		
		$hosterWrapper = $this->helpers()->hoster();
		if ( !($hosterWrapper instanceof X_VlcShares_Plugins_Helper_HosterSSWrapper) ) {
			X_Debug::w("Hoster replacement failed, can't handler this");
			return;
		}

		/* @var $providerObj X_VlcShares_Plugins_Abstract */
		$providerObj = X_VlcShares_Plugins::broker()->getPlugins($provider);
		// force resolve location
		if ( !($providerObj instanceof X_VlcShares_Plugins_ResolverInterface ) ) {
			X_Debug::i("Provider not a resolver. Ignoring this");
			return;
		}  
		
		/* @var $providerObj X_VlcShares_Plugins_ResolverInterface */
		$resolvedLocation = $providerObj->resolveLocation($location);
		if ( !$resolvedLocation ) {
			X_Debug::i("Invalid location. Ignoring this");
		}
		
		// now we know that $location is valid and
		// that last positive is setted in the hoster wrapper
		
		try {
			
			$hoster = $hosterWrapper->getLastPositiveMatch();

			/* @var $ssHelper X_VlcShares_Plugins_Helper_StreamSeeker */
			$ssHelper = $this->helpers('streamseeker');

			// if valid hoster...
			if ( !$ssHelper->isSeekableHoster($hoster) ) {
				$hosterClass = get_class($hoster);
				X_Debug::i("Unseekable hoster: {id: {$hoster->getId()}, class: {$hosterClass}}");
				return;
			}

			
			// Prepare the menu
			
			$startLabel = X_Env::_("p_streamseeker_begin_start");
			
			// check if param set and prepare the label
			$startValue = $controller->getRequest()->getParam($this->getId(), 0);
			if ( $startValue > 0 ) {

				// ADD priority to filterModeItems to allow url changes on direct-play values
				$this->setPriority('filterModeItems');
				
				$positions = $ssHelper->getPositions("$provider::$location", $resolvedLocation);
				if ( array_key_exists($startValue, $positions) ) {
					$startLabel = $positions[$startValue];
				} else {
					$startLabel = X_Env::_('p_streamseeker_begin_unknown', $startValue);
				}
			}
			
			
			$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_streamseeker_startfrom', $startLabel));
			$link->setIcon('/images/streamseeker/logo.png')
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(array(
						'action'	=>	'selection',
						'pid'		=>	$this->getId()
					), 'default', false);
	
			return new X_Page_ItemList_PItem(array($link));
			
		} catch (Exception $e) {
			X_Debug::i("Hoster API not used or not positive match: {$e->getMessage()}");
			return;
		}
	}

	/**
	 * Change the url of directplay link to add seeked value
	 * @see X_VlcShares_Plugins_Abstract::filterModeItems()
	 */
	public function filterModeItems(X_Page_Item_PItem $item, $provider, Zend_Controller_Action $controller) {
		if ( $item->getKey() == 'core-directwatch' && $item->isUrl() ) {
			$ssValue = $controller->getRequest()->getParam($this->getId(), 0);
			if ( $ssValue > 0 ) {
				// need to change the value
				$link = $item->getLink();
				
				/* @var $hosterHelper X_VlcShares_Plugins_Helper_HosterSSWrapper */
				$hosterHelper = $this->helpers()->hoster();
				
				/* @var $hoster X_VlcShares_Plugins_Helper_HostInterface */
				$hoster = $hosterHelper->getLastPositiveMatch();
				
				/* @var $ssHelper X_VlcShares_Plugins_Helper_StreamSeeker */
				$ssHelper = $this->helpers('streamseeker');
				
				if ( $ssHelper->isSeekableHoster($hoster) ) {
					$newLink = $ssHelper->getSeekedUrl($link, $ssValue, $hoster );
					
					X_Debug::i("Changing link to {$newLink}");
					$item->setLink($newLink);
					
				} else {
					X_Debug::f("A seekable hoster reported as unseekable on last check: ".get_class($hoster));
				}
			}
		}
	}
	

	/**
	 * Set the header of position selection
	 * @param string $provider
	 * @param string $location
	 * @param string $pid
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function preGetSelectionItems($provider, $location, $pid, Zend_Controller_Action $controller) {
		// we want to expose items only if pid is this plugin
		if ( $this->getId() != $pid ) return;
	
		X_Debug::i('Plugin triggered');
	
		$urlHelper = $controller->getHelper('url');
		$link = new X_Page_Item_PItem($this->getId().'-header', X_Env::_('p_streamseeker_seekposition'));
		$link->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setLink(X_Env::completeUrl($urlHelper->url()));
		
		return new X_Page_ItemList_PItem(array($link));
	
	}
	
	
	/**
	 * Show the list of valid positions
	 * @param string $provider
	 * @param string $location
	 * @param string $pid
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function getSelectionItems($provider, $location, $pid, Zend_Controller_Action $controller) {
		// we want to expose items only if pid is this plugin
		if ( $this->getId() != $pid ) return;
	
		X_Debug::i('Plugin triggered');
	
		$urlHelper = $controller->getHelper('url');
	
	
		// i try to mark current selected sub based on $this->getId() param
		// in $currentSub i get the name of the current profile
		$startValue = $controller->getRequest()->getParam($this->getId(), false);
	
		$return = new X_Page_ItemList_PItem();
		
		$item = new X_Page_Item_PItem("{$this->getId()}-positions-begin", X_Env::_('p_streamseeker_begin_start_label'));
		$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setHighlight(($startValue == false || $startValue == 0 || $startValue == null))
			->setLink(array(
				'action'					=> 'mode',
				"{$this->getId()}"			=> null,
				'pid'						=> null
			), 'default', false);
		$return->append($item);
		
		$hosterWrapper = $this->helpers()->hoster();
		if ( !($hosterWrapper instanceof X_VlcShares_Plugins_Helper_HosterSSWrapper) ) {
			X_Debug::w("Hoster replacement failed, can't handler this");
			return $return;
		}
		
		/* @var $providerObj X_VlcShares_Plugins_ResolverInterface */
		$providerObj = X_VlcShares_Plugins::broker()->getPlugins($provider);
		if ( !($providerObj instanceof X_VlcShares_Plugins_ResolverInterface ) ) {
			X_Debug::e("Provider is not a resolver. Can't analyze without a location");
			return $return;
		}
		
		$resolvedLocation = $providerObj->resolveLocation($location);
		if ( !$resolvedLocation ) {
			X_Debug::e("Invalid stream or invalid location");
			return $return;
		}
	
		$hoster = $hosterWrapper->getLastPositiveMatch();
		
		/* @var $ssHelper X_VlcShares_Plugins_Helper_StreamSeeker */
		$ssHelper = $this->helpers('streamseeker');
		
		// if valid hoster...
		if ( !$ssHelper->isSeekableHoster($hoster) ) {
			$hosterClass = get_class($hoster);
			X_Debug::i("Unseekable hoster: {id: {$hoster->getId()}, class: {$hosterClass}}");
			return $return;
		}
		
		$positions = $ssHelper->getPositions("$provider::$location", $resolvedLocation);
		
		foreach ($positions as $posValue => $posLabel) {

			$item = new X_Page_Item_PItem("{$this->getId()}-positions-{$posValue}", $posLabel );
			$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setHighlight(($startValue == $posValue ))
				->setLink(array(
					'action'					=> 'mode',
					"{$this->getId()}"			=> $posValue,
					'pid'						=> null
				), 'default', false);
			$return->append($item);
		}
		
		
		return $return;
	}
	
	

	/**
	 * Show an error message if one of the plugin dependencies is missing
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Message
	 */
	public function getIndexMessages(Zend_Controller_Action $controller) {
		$messages = new X_Page_ItemList_Message();
		if( !class_exists('X_VlcShares_Plugins_Utils') ) {
			$message1 = new X_Page_Item_Message($this->getId(),X_Env::_("p_streamseeker_err_pageparserlibrequired"));
			$message1->setType(X_Page_Item_Message::TYPE_FATAL);
			$messages->append($message1);
		}
		if ( !X_VlcShares_Plugins::broker()->isRegistered('cache') ) {
			$message2 = new X_Page_Item_Message($this->getId(),X_Env::_("p_streamseeker_err_cachedisabled"));
			$message2->setType(X_Page_Item_Message::TYPE_FATAL);
			$messages->append($message2);
		}
		
		return $messages;
	}
		
	
}
