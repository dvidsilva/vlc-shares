<?php 

/** Zend_View_Helper_Abstract.php */
require_once 'Abstract.php';
require_once 'X/VlcShares/Skins/Default/XButton.php';

class X_VlcShares_View_Helper_XButton extends X_VlcShares_View_Helper_Abstract {
	
	const VARIANT_NONE = false;
	const VARIANT_HIGHLIGHT = 'highlight';
	const VARIANT_DISABLED = 'disabled';
	
	const DIMENSION_NORMAL = 'normal';
	const DIMENSION_SMALL = 'small';
	const DIMENSION_BIG = 'big';
	
	protected $_defaultOptions = array(
		'variant'	=> self::VARIANT_NONE,
		'dimension' => self::DIMENSION_NORMAL,
	);
	
	protected $label = '';
	protected $url = '';
	
	protected function getDefaultDecorator() {
		return new X_VlcShares_Skins_Default_XButton();
	}
    
	protected function getDefaultOptions() {
		return $this->_defaultOptions;
	}
	
	/**
	 * @return X_VlcShares_View_Helper_XButton
	 */
	public function xButton($label, $url) {
		//return $this->decorate($manageLink);
		$this->label = $label;
		$this->url = $url;
		return $this;
	}

	/**
	 * @return X_VlcShares_View_Helper_XButton
	 */
	public function setVariant($variant = null) {
		if ( $variant === null ) $variant = $this->_defaultOptions['variant'];
		$this->setOption('variant', $variant);
		return $this;
	}
	
	/**
	 * @return X_VlcShares_View_Helper_XButton
	 */
	public function setDimension($value = null) {
		if ( $value === null ) $value = $this->_defaultOptions['dimension'];
		$this->setOption('dimension', $value);
		return $this;
	}
	
	function __toString() {
		$content = array(
			'label' => $this->label,
			'url'	=> $this->url
		);
		return $this->decorate((object) $content);
	}
	
}