<?php 

abstract class X_VlcShares_Elements_TableEntry extends X_VlcShares_Elements_Container {

	
	private $_parent = null;
	
	
	public function __construct($parent = null) {
		if ( !is_null($parent) && $parent instanceof X_VlcShares_Elements_MenuEntry ) {
			$this->setParent($parent);
		}
	}
	
	public function setParent(X_VlcShares_Elements_Menu $parent = null) {
		$this->_parent = $parent;
		return $this;
	}
	
	public function endEntry() {
		if ( is_null($this->_parent) ) {
			throw new Exception("TableEntry has no parent");
		}
		return $this->_parent;
	}
}
