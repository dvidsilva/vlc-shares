<?php 

/**
 * Parse a string as a SimpleXmlElement object
 * @author ximarx
 *
 */
class X_PageParser_Parser_SimpleXml extends X_PageParser_Parser {
	
	private $namespace = null;
	
	/**
	 * Get a SimpleXml parser initied and ready
	 * @param string $namespace
	 */
	public static function factory( $namespace = null) {
		return new self($namespace);
	}
	
	/* (non-PHPdoc)
	 * @see X_PageParser_Parser_SimpleXml::factory()
	 */
	public function __construct($namespace = null) {
		$this->namespace = $namespace;
	}
	
	/* (non-PHPdoc)
	 * @see X_PageParser_Parser::parse()
	 */
	public function parse($string) {
		$parsed = array();
		if ( $this->namespace ) {
			$parsed = simplexml_load_string($string, "SimpleXMLElement", 0, $this->namespace);
		} else {
			$parsed = simplexml_load_string($string);
		}
		if ( $parsed === false ) {
			X_Debug::e("simplexml_load_string return error: invalid string");
			$parsed = array();
		}
		return $parsed;
		
	}
}
