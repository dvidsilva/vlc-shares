<?php 

class X_VlcShares_Elements_TableRow extends X_VlcShares_Elements_TableEntry {
	
	private $_cells = array();
	
	/**
	 * @see X_VlcShares_Elements_Element::getDefaultDecorator()
	 */
	public function getDefaultDecorator() {
		return X_VlcShares_Skins_Manager::i()->getDecorator(X_VlcShares_Skins_Manager::TABLEROW);
	}
		
	
	public function newCell() {
		$cell = new X_VlcShares_Elements_TableCell($this);
		$cell->setView($this->view);
		$decorator = $this->getDecorator();
		if ( $decorator instanceof X_VlcShares_Skins_ParentDecoratorInterface ) {
			$cell->setDecorator($decorator->getSubDecorator(X_VlcShares_Skins_Manager::TABLECELL));
		}
		$this->_cells[] = $cell;
		return $cell;
	}
	
	/**
	 * Override default render object to add entries inside content,
	 * after that, call the overrided method
	 * @return string
	 */
	public function render($content) {
		foreach ($this->_cells as $entry) {
			$content .= (string) $entry;
		}
		return parent::render($content);
	}
}
