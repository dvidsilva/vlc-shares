<?php 

class X_VlcShares_Elements_Table extends X_VlcShares_Elements_Element {

	private $_rows = array();
	private $_headers = null;
	private $_footer = null;
	
	/**
	 * @see X_VlcShares_Elements_Element::getDefaultDecorator()
	 */
	public function getDefaultDecorator() {
		return X_VlcShares_Skins_Manager::i()->getDecorator(X_VlcShares_Skins_Manager::TABLE);
	}
	
	/**
	 * Override default render object to add entries inside content,
	 * after that, call the overrided method
	 * @return string
	 */
	public function render($content) {
		// add the headers row
		$content .= (string) $this->_headers;
		
		foreach ($this->_rows as $entry) {
			$content .= (string) $entry;
		}
		return parent::render($content) . (string) $this->_footer;
	}

	protected function getHeadersRow() {
		if ( is_null($this->_headers) ) {
			$this->_headers = new X_VlcShares_Elements_TableRow($this);
			$this->_headers->setView($this->view);
			$decorator = $this->getDecorator();
			if ( $decorator instanceof X_VlcShares_Skins_ParentDecoratorInterface ) {
				$this->_headers->setDecorator($decorator->getSubDecorator(X_VlcShares_Skins_Manager::TABLEROW_HEADER));
			} else {
				$this->_headers->setDecorator(X_VlcShares_Skins_Manager::i()->getDecorator(X_VlcShares_Skins_Manager::TABLEROW_HEADER));
			}
		}
		return $this->_headers;
	}
	
	public function addHeader($label) {
		$cell = $this->getHeadersRow()->newCell();
		$cell->setView($this->view);
		$cell->set($label);
		return $this;
	}
	
	public function newRow() {
		$row = new X_VlcShares_Elements_TableRow($this);
		$row->setView($this->view);
		$decorator = $this->getDecorator();
		if ( $decorator instanceof X_VlcShares_Skins_ParentDecoratorInterface ) {
			if ( count($this->_rows) % 2 ) {
				$row->setDecorator($decorator->getSubDecorator(X_VlcShares_Skins_Manager::TABLEROW));
			} else {
				$row->setDecorator($decorator->getSubDecorator(X_VlcShares_Skins_Manager::TABLEROW_ALTERNATE));
			}
		} else {
			if ( count($this->_rows) % 2 ) {
				$row->setDecorator(X_VlcShares_Skins_Manager::i()->getDecorator(X_VlcShares_Skins_Manager::TABLEROW));
			} else {
				$row->setDecorator(X_VlcShares_Skins_Manager::i()->getDecorator(X_VlcShares_Skins_Manager::TABLEROW_ALTERNATE));
			}
		}
		$this->_rows[] = $row;
		return $row;
	}
	
	public function setFooter(X_VlcShares_Elements_Element $footer) {
		$this->_footer = $footer;
		return $this;
	}
	
}
