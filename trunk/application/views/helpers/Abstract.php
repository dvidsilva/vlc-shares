<?php 

/** Zend_View_Helper_Abstract.php */
require_once 'Zend/View/Helper/Abstract.php';
/** X_VlcShares_Skins_DecoratorInterface.php */
require_once 'X/VlcShares/Skins/DecoratorInterface.php';

abstract class X_VlcShares_View_Helper_Abstract extends Zend_View_Helper_Abstract {

	/**
	 * @var X_VlcShares_Skins_DecoratorInterface
	 */
	protected $_decorator = null;
	
	protected $_options = array();
	
	protected $_optionStack = array();
	
	/**
	 * @return X_VlcShares_View_Helper_Abstract
	 */
	public function setDecorator(X_VlcShares_Skins_DecoratorInterface $decorator) {

		$this->_decorator = $decorator;
		$this->_decorator->setView($this->view);
		
		return $this;
	}
	
	public function getDecorator() {
		if ( is_null($this->_decorator) ) {
			$this->setDecorator($this->getDefaultDecorator());
		}
		return $this->_decorator;
	}
	
	public function resetDecorator() {
		$this->_decorator = null;
	}
	
	abstract protected function getDefaultDecorator();
	
	/**
	 * @param string $name
	 * @param string $value
	 * @return X_VlcShares_View_Helper_Abstract
	 */
	public function setOption($name, $value) {
		$this->_options[$name] = $value;
		return $this;
	}
	
	/**
	 * @param array $options
	 * @return X_VlcShares_View_Helper_Abstract
	 */
	public function setOptions($options) {
		if ( is_array($options) ) {
			$this->_options = array_merge($this->_options, $options);
			return $this;
		} else {
			throw new Exception('Invalid argument to setOptions, array expected');
		}
	}
	
	/**
	 * @return stdClass
	 */
	protected function getOptions() {
		$defaultOptions = $this->getDefaultOptions();
		return ((object) array_merge($defaultOptions, $this->_options));
	}
	
	/**
	 * @return array
	 */
	abstract protected function getDefaultOptions();
	
	public function newInstance($options = array()) {
		array_push($this->_optionStack, $this->_options);
		$this->_options = is_array($options) ? $options : array();
	}
	
	public function freeInstance() {
		$options = array_pop($this->_optionStack);
		if ( $options == null ) {
			$this->_options = array();
			$this->_optionStack = array();
		} else {
			$this->_options = $options;
		}
	}
	
	
    /**
     * Start capture action
     *
     * @param  mixed $captureType
     * @param  string $typeOrAttrs
     * @return void
     */
    public function captureStart($options = array())
    {
    	if ( is_array($options) && !empty($options) ) {
    		$this->setOptions($options);
    	}
        ob_start();
    }

    /**
     * End capture action and store
     *
     * @return void
     */
    public function captureEnd($echo = false)
    {
    	// get content inside the capture
        $content = ob_get_clean();
        
        // send content and options to decorator
        $content = $this->decorate($content);
        
        // reset option to previous instance
        $this->freeInstance();
        
        if ( $echo ) {
        	echo $content;
        }
        // return content
        return $content;
    }
    
    protected function decorate($content) {
        // get the decorator instance
        $decorator = $this->getDecorator();
        
        // get options
        $options = $this->getOptions();
        
        // send content and options to decorator
        $content = $decorator->decorate($content, $options);
        
        // return content
        return $content;
    }
	
}