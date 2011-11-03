<?php 

class X_VlcShares_Elements_Portion extends X_VlcShares_Elements_Container {
	
	public function getDefaultDecorator() {
		return X_VlcShares_Skins_Manager::i()->getDecorator(X_VlcShares_Skins_Manager::PORTION);
	}

	public function set($content) {
		$this->content = $content;
		return $this;
	}

	public function prepend($content) {
		$this->content = $content . $this->content;
		return $this;
	}
	
	public function append($content) {
		$this->content .= $content;
		return $this;
	}
	
}
