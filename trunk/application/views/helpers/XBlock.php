<?php 

/** Zend_View_Helper_Abstract.php */
require_once 'Abstract.php';
require_once 'X/VlcShares/Skins/Default/XBlock.php';

class X_VlcShares_View_Helper_XBlock extends X_VlcShares_View_Helper_Abstract {
 
	const VARIANT_NONE = false;
	const VARIANT_HIGHLIGHT = 'highlight';
	const VARIANT_DISABLED = 'disabled';
	
	protected $_defaultOptions = array(
		'title' => false,
		'variant' => false,
		'header' => false,
	);
	
	protected function getDefaultDecorator() {
		return new X_VlcShares_Skins_Default_XBlock();
	}
    
	protected function getDefaultOptions() {
		return $this->_defaultOptions;
	}
	
	/**
	 * @return X_VlcShares_View_Helper_XBlock
	 */
	public function xBlock($newInstance = true) {
		if ($newInstance) {
			// create a new instance with standard options
			$this->newInstance(
				$this->_defaultOptions
			);
		}
		return $this;
	}
	
	public function setTitle($title = null) {
		if ( $title === null ) $title = $this->_defaultOptions['title'];
		$this->setOption('title', $title);
		return $this;
	}
	
	public function setVariant($variant = null) {
		if ( $variant === null ) $variant = $this->_defaultOptions['variant'];
		$this->setOption('variant', $variant);
		return $this;
	}
	
	public function setHeader($header = null) {
		if ( $header === null ) $header = $this->_defaultOptions['header'];
		$this->setOption('header', $header);
		return $this;
	}
}