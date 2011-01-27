<?php 


abstract class X_VlcShares_Elements_Element {
	
	/**
	 * @var Zend_View
	 */
	protected $view;
	/**
	 * @var X_VlcShares_Skins_DecoratorInterface
	 */
	protected $decorator;
	
	/**
	 * @var array of key => value options 
	 */
	private $options = array();
	
	abstract function getDefaultDecorator();
	
	public function setDecorator(X_VlcShares_Skins_DecoratorInterface $decorator = null) {
		$this->decorator = $decorator;
		return $this;
	}
	
	protected function getDecorator() {
		if ( is_null($this->decorator) ) {
			$this->decorator = $this->getDefaultDecorator();
		}
		return $this->decorator;
	}
	
	public function setView(Zend_View $view) {
		$this->view = $view;
		return $this;
	}
	
	public function setOption($name, $value) {
		$this->options[$name] = $value;
		return $this;
	}
	
	public function getOption($name) {
		return @$this->options[$name];
	}
	
	public function getOptions() {
		return $this->options;
	}
	
	public function render($content) {
		$this->getDecorator()->setView($this->view);
		return $this->getDecorator()->decorate($content, $this->options);
	}
	
	public function __toString() {
		return $this->render('');
	}
	
} 
