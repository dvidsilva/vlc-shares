<?php 

class X_VlcShares_Elements_SectionContainer extends X_VlcShares_Elements_Container {
	
	private $_sections = array();
	private $_sectionsOrder = array();
	
	
	public function getDefaultDecorator() {
		return X_VlcShares_Skins_Manager::i()->getDecorator(X_VlcShares_Skins_Manager::SECTIONCONTAINER);
	}
	
	/**
	 * Allow to enable get section
	 * if is a default portion return the standard reference, 
	 * if it's a custom one, it return the new reference
	 * @param string $name section name
	 * @return X_VlcShares_Elements_Section
	 */
	public function __get($name) {
		if ( !array_key_exists($name, $this->_sections) ) {
			$this->_sections[$name] = new X_VlcShares_Elements_Section();
			$this->_sectionsOrder[] = $name;
		}
		return $this->_sections[$name];
	}
	
	/**
	 * Allow to set custom section inside a section container
	 */
	public function __set($name, $value) {
		if ( !array_key_exists($name, $this->_sections) ) {
			$this->_sectionsOrder[] = $name;
		}
		$this->_sections[$name] = $value;
	}
	
	
	/**
	 * Override default render object to add portions inside decorator object,
	 * after that, call the overrided method
	 * @return string
	 */
	public function render($content) {
		foreach ($this->_sectionsOrder as $name) {
			$content .= (string) $this->_sections[$name];
		}
		return parent::render($content);
	}
	
	/**
	 * Set the section number per row
	 * @param int $numColumn
	 * @return X_VlcShares_Elements_SectionContainer fluent interface
	 */
	public function setSectionsPerRow($numColumn) {
		$this->setOption('sectioncontainer.per-row', $numColumn);
		return $this;
	}
	
	/**
	 * Set if all section inside the container must have the same height
	 * @param boolean $isSameHeight
	 * @return X_VlcShares_Elements_SectionContainer fluent interface
	 */
	public function setSectionsSameHeight($isSameHeight) {
		$this->setOption('sectioncontainer.same-height', (bool) $isSameHeight);
		return $this;
	}
	
}
