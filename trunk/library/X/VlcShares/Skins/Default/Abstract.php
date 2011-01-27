<?php 

require_once 'X/VlcShares/Skins/DecoratorInterface.php';
require_once 'Zend/View.php';

abstract class X_VlcShares_Skins_Default_Abstract implements X_VlcShares_Skins_DecoratorInterface {

	/**
	 * @var Zend_View
	 */
	protected $view = null;

	protected $decoratorOptions = array();

	public function __construct($options = array()) {
		if ( is_array($options)) {
			$this->decoratorOptions = $options;
		}
	}
	
	public function setView($view) {
		$this->view = $view;
	}
	
	public function normalizeOptions($options) {
		if ( is_array($options) ) {
			$options = array_merge($this->getDefaultOptions(), $options);
		} else {
			$options = $this->getDefaultOptions();
		}
		return $options;
	}
	
	/**
	 * Called by normalizeOptions to sanitarize options array
	 * @return array
	 */
	/* abstract */protected function getDefaultOptions() {
		return array();
	}
	
	/**
	 * @param string $content
	 * @param string|array $params if string, it will be appended to tag definition.
	 * 			if array, it will imploded and appended to tag definition
	 * @return string;
	 */
	protected function wrap($content, $tag, $params = '') {
		if ( is_array($params) ) {
			$params = implode(' ', $params);
		}
		if ( $params != '' ) $params = ' '.$params;
		return "<{$tag}{$params}>\n\t{$content}\n</{$tag}>\n";
	}
	
	/**
	 * @param string $content
	 * @param array	$array a list of array($tag, $params) that will be forwarded to wrap
	 * @return string
	 */
	protected function recursiveWrap($content, $array) {
		$array = array_reverse($array);
		foreach ($array as $single) {
			if ( !is_array($single) ) continue;
			@list($tag, $params) = $single;
			$content = $this->wrap($content, $tag, $params);
		}
		return $content;
	}
	
	/**
	 * All method calls are redirected here and this method does nothing
	 * @return X_VlcShares_Skins_AltGui_Abstract
	 */
	function __call($method, $args) {
		X_Debug::i("Decorator method called: $method");
		return $this;
	}
}
