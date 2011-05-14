<?php 


abstract class X_VlcShares_Plugins_Helper_Hoster_RealDebridAbstract implements X_VlcShares_Plugins_Helper_HostInterface {
	
	const EXCEPTION_NOPARENTHOSTER = 10000000;
	const EXCEPTION_NOPROPERTY = 10000001;
	
	/**
	 * @var X_VlcShares_Plugins_Helper_HostInterface
	 */
	protected $parentHoster = null;
	
	public function setParentHoster($hoster) {
		if ( $hoster instanceof X_VlcShares_Plugins_Helper_HostInterface ) {
			$this->parentHoster = $hoster;
		}
	}
	
	protected function getParentProperty($property, $throwException = true) {
		if ( $this->parentHoster !== null ) {
			$property = "get".ucfirst($property);
			if (method_exists($this->parentHoster, $property)) {
				return $this->parentHoster->$property();
			} else {
				throw new Exception("Invalid property", self::EXCEPTION_NOPROPERTY);
			}
		} else {
			if ( $throwException ) {
				throw new Exception("No parent hoster", self::EXCEPTION_NOPARENTHOSTER);
			} else {
				return null;
			}
		}
	}
	
	/**
	 * @return X_VlcShares_Plugins_Helper_HostInterface
	 * @throws Exception if no parent hoster is setted
	 */
	public function getParentHoster() {
		if ( $this->parentHoster === null ) {
			throw new Exception("No parent hoster", self::EXCEPTION_NOPARENTHOSTER);
		} else {
			return $this->parentHoster;
		}
	}
	
	// === INTERFACE
	
	/**
	 * get an array with standard information about the playable
	 * @param string $url the hoster page or resource ID
	 * @param boolean $isId
	 * @return array format:
	 * 		array(
	 * 			'title' => TITLE
	 * 			'description' => DESCRIPTION
	 * 			'length' => LENGTH
	 * 			...
	 * 		)
	 */
	function getPlayableInfos($url, $isId = true) {
		return $this->getParentHoster()->getPlayableInfos($url, $isId);
	}
	
	function getHosterUrl($playableId) {
		return $this->getParentHoster()->getHosterUrl($playableId);
	}
	
	/**
	 * get the resource ID for the hoster
	 * from an $url
	 * @param string $url the hoster page
	 * @return string the resource id
	 */
	function getResourceId($url) {
		return $this->getParentHoster()->getResourceId($url);
	}
	
	/**
	 * get a playable resource url
	 * from an $url (or a resource id if $isId = true)
	 * @param string $url the hoster page or resource ID
	 * @param boolean $isId
	 * @return string a playable url
	 */
	function getPlayable($url, $isId = true) {
		if ( $isId ) {
			$url = $this->getHosterUrl($url);
		}
		// $url is an URL for sure now
		/* @var $realdebridHelper X_VlcShares_Plugins_Helper_RealDebrid */
		$realdebridHelper = X_VlcShares_Plugins::helpers()->helper('realdebrid');
		if ( $realdebridHelper->setLocation($url)->isValid() ) {
			return $realdebridHelper->getUrl();
		}
		throw new Exception("Invalid video", self::E_ID_INVALID);
	}
	
	
}

