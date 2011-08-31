<?php 

class X_VlcShares_Plugins_Helper_Megavideo extends X_VlcShares_Plugins_Helper_Abstract {

	const QUALITY_FULL = 'full';
	const QUALITY_NORMAL = 'normal';
	const QUALITY_NOPREMIUM = 'disabled';
	
	const VERSION_CLEAN = '0.3.1';
	const VERSION = '0.3.1';
	
	private $_cachedSearch = array();
	private $_location = null;
	/**
	 * @var Megavideo
	 */
	private $_fetched = false;
	
	private $options;
	
	function __construct(Zend_Config $options = null) {
		
		if ( $options == null ) {
			$options = new Zend_Config(array('username' => '', 'password' => '', 'premium' => false));
		}
		$this->options = $options;
	}
	
	
	/**
	 * Set source location
	 * 
	 * @param $location Megavideo ID or URL
	 * @return X_VlcShares_Plugins_Helper_Megavideo
	 */
	function setLocation($location) {
		if ( $this->_location != $location ) {
			$this->_location = $location;
			if ( array_key_exists($location, $this->_cachedSearch) ) {
				$this->_fetched = $this->_cachedSearch[$location];
			} else {
				$this->_fetched = false;
			}
		}
		return $this;
	}
	
	/*
      URL.............Url with you can download the flv video 
      SIZE............The size (MB) of video 
      TITLE...........Title of video 
      DURATION........Duration of video in minutes 
      SERVER..........Server of video 
      DESCRIPTION.....Description of video 
      ADDED...........Date of added 
      USERNAME........Username of uploader 
      CATEGORY........Category of video 
      VIEWS...........Number of views 
      COMMENTS........Number of comments 
      FAVORITED.......Number of favorites by users 
      RATING..........Rate of video 
	 */
	
	public function getUrl() {
		$this->_fetch();
		if ( $this->options->premium && $this->options->username != '' && $this->options->password != '' ) {
			// i have to check here for quality mode
			$quality = Zend_Controller_Front::getInstance()->getRequest()->getParam('megavideo:quality', 'normal');
			
			switch ($quality) {
				case self::QUALITY_NOPREMIUM :
					return $this->_fetched->get('URL');
				case self::QUALITY_NORMAL:
					return X_Env::routeLink('megavideo', 'premium', array('v' => $this->_fetched->id ));
				case self::QUALITY_FULL:
				default: 
					return X_Env::routeLink('megavideo', 'premium', array('v' => $this->_fetched->id, 'q' => $quality ));
			}
			
		} else {
			return $this->_fetched->get('URL');
		}
	}
	public function getId() {
		$this->_fetch();
		return $this->_fetched->id;
	}
	public function getSize() {
		$this->_fetch();
		return $this->_fetched->get('SIZE');
	}
	public function getTitle() {
		$this->_fetch();
		return urldecode($this->_fetched->get('TITLE'));
	}
	public function getDuration() {
		$this->_fetch();
		return $this->_fetched->get('DURATION');
	}
	public function getServer() {
		$this->_fetch();
		return $this->_fetched->get('SERVER');
	}
	public function getDescription() {
		$this->_fetch();
		return urldecode($this->_fetched->get('DESCRIPTION'));
	}
	public function getAdded() {
		$this->_fetch();
		return $this->_fetched->get('ADDED');
	}
	public function getUsername() {
		$this->_fetch();
		return $this->_fetched->get('USERNAME');
	}
	public function getCategory() {
		$this->_fetch();
		return $this->_fetched->get('CATEGORY');
	}
	public function getViews() {
		$this->_fetch();
		return $this->_fetched->get('VIEWS');
	}
	public function getComments() {
		$this->_fetch();
		return $this->_fetched->get('COMMENTS');
	}
	public function getFavorited() {
		$this->_fetch();
		return $this->_fetched->get('FAVORITED');
	}
	public function getRating() {
		$this->_fetch();
		return $this->_fetched->get('RATING');
	}
	
	protected function _fetch() {
		if ( $this->_location == null ) {
			X_Debug::w('Trying to fetch a megavideo location without a location');
			throw new Exception('Trying to fetch a megavideo location without a location');
		}
		if ( $this->_fetched === false ) {
			$this->_fetched = new X_Megavideo($this->_location);
			$this->_cachedSearch[$this->_location] = $this->_fetched;
		}
	}
}
