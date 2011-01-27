<?php 

class X_VlcShares_Elements_Block extends X_VlcShares_Elements_Container {
	
	/* @var $header X_VlcShares_Elements_Container */
	/* @var $footer X_VlcShares_Elements_Container */
	/* @var $title X_VlcShares_Elements_Container */
	
	/**
	 * @var X_VlcShares_Elements_Container
	 */
	private $_portion_header = null;
	
	/**
	 * @var X_VlcShares_Elements_Container
	 */
	private $_portion_footer = null;

	/**
	 * @var X_VlcShares_Elements_Container
	 */
	private $_portion_title = null;
	
	private $_extraPortions = array();
	
	
	public function __construct() {
		$this->_portion_header = new X_VlcShares_Elements_Portion();
		$this->_portion_footer = new X_VlcShares_Elements_Portion();
		$this->_portion_title = new X_VlcShares_Elements_Portion();
	}
	
	public function getDefaultDecorator() {
		return X_VlcShares_Skins_Manager::i()->getDecorator(X_VlcShares_Skins_Manager::BLOCK);
	}
	
	/**
	 * Allow to enable get portions
	 * if is a default portion return the standard reference, 
	 * if it's a custom one, it return the new reference
	 */
	public function __get($name) {
		$portionName = "_portion_$name";
		if ( !property_exists($this, $portionName) ) {
			if ( !array_key_exists($name, $this->_extraPortions) ) {
				$this->_extraPortions[$name] = new X_VlcShares_Elements_Portion();
			}
			$portion = $this->_extraPortions[$name];
		} else {
			$portion = $this->$portionName;
		}
		return $portion;
	}
	
	/**
	 * Override default render object to add portions inside decorator object,
	 * after that, call the overrided method
	 */
	public function render($content) {
		$this->setOption('block.header', (string) $this->_portion_header);
		$this->setOption('block.footer', (string) $this->_portion_footer);
		$this->setOption('block.title', (string) $this->_portion_title);
		foreach ($this->_extraPortions as $name => $portion) {
			$this->setOption("block.extra.$name", (string) $portion);
		}
		return parent::render($content);
	}
	
}
