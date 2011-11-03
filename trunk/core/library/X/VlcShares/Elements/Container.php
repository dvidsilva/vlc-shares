<?php 


class X_VlcShares_Elements_Container extends X_VlcShares_Elements_Element {
	
	protected $content = '';
	
	private $_started = false;
	
	public function getDefaultDecorator() {
		return X_VlcShares_Skins_Manager::i()->getDecorator(X_VlcShares_Skins_Manager::CONTAINER);
	}
	
	/**
	 * Start a new output buffer
	 * @return X_VlcShares_Elements_Container
	 */
	public function start() {
		$this->_started = true;
		ob_start();
		return $this;
	}
	
	public function end() {
		if ( !$this->_started ) {
			throw new Exception('Cannot end before start');
		}
		$this->_started = false;
		$this->content = ob_get_clean();
		return $this;
	}
	
	public function __toString() {
		return $this->render($this->content);
	}
	
	public function setId($id) {
		$this->setOption('container.args', "id=\"$id\"");
		return $this;
	}
	public function setClasses($classes) {
		$this->setOption('container.classes', $classes);
		return $this;
	}
}

