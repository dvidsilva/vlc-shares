<?php 

class X_VlcShares_Elements_MenuEntry extends X_VlcShares_Elements_Element {

	const SUBMENU = 'submenu';
	const LABEL = 'label';
	const LINK = 'link';
	const BUTTON = 'button';
	
	private $_parent = null;
	
	
	public function __construct($parent = null) {
		if ( !is_null($parent) && $parent instanceof X_VlcShares_Elements_MenuEntry ) {
			$this->setParent($parent);
		}
	}
	
	
	public function getDefaultDecorator() {
		return X_VlcShares_Skins_Manager::i()->getDecorator(X_VlcShares_Skins_Manager::MENUENTRY_LINK);
	}
	
	/**
	 * Override default render object to add portions inside decorator object,
	 * after that, call the overrided method
	 * @return string
	 */
	/*
	public function render($content) {
		foreach ($this->_sectionsOrder as $name) {
			$content .= (string) $this->_sections[$name];
		}
		return parent::render($content);
	}
	*/
	
	public function setParent(X_VlcShares_Elements_Menu $parent = null) {
		$this->_parent = $parent;
		return $this;
	}
	
	public function setLabel($label) {
		$this->setOption('menuentry.label', $label);
		return $this;
	}

	public function setHref($href) {
		$this->setOption('menuentry.href', $href);
		return $this;
	}
	
	public function endEntry() {
		if ( is_null($this->_parent) ) {
			throw new Exception("MenuEntry has no parent");
		}
		return $this->_parent;
	}
}
