<?php 

class X_VlcShares_Elements_TableCell extends X_VlcShares_Elements_TableEntry {
	
	/**
	 * @see X_VlcShares_Elements_Element::getDefaultDecorator()
	 */
	public function getDefaultDecorator() {
		return X_VlcShares_Skins_Manager::i()->getDecorator(X_VlcShares_Skins_Manager::TABLECELL);
	}
	
	public function newMenu() {
		$menu = new X_VlcShares_Elements_Menu();
		$menu->setView($this->view);
		$decorator = $this->getDecorator();
		if ( $decorator instanceof X_VlcShares_Skins_ParentDecoratorInterface ) {
			$menu->setDecorator($decorator->getSubDecorator(X_VlcShares_Skins_Manager::MENU));
		}
		return $menu;
	}
	
	public function set($content) {
		$this->content = $content;
		return $this;
	}
	
}
