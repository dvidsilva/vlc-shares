<?php 

/**
 * Parse the string as a Json serialized string
 * @author ximarx
 *
 */
class X_PageParser_Parser_Json extends X_PageParser_Parser {
	
	private $flags = null;
	
	/**
	 * Get a JSon parser object initied
	 * @param int $flags
	 * @return X_PageParser_Parser_Json
	 */
	public static function factory($flags = Zend_Json::TYPE_ARRAY) {
		return new self($flags);
	}
	/* (non-PHPdoc)
	 * @see X_PageParser_Parser_Json::parse()
	*/
	public function __construct($flags = Zend_Json::TYPE_ARRAY) {
		$this->flags = $flags;
	}
	
	/* (non-PHPdoc)
	 * @see X_PageParser_Parser::parse()
	 */
	public function parse($string) {
		$parsed = array();
		$parsed = Zend_Json::decode($string, $this->flags);
		return $parsed;
		
	}
}
