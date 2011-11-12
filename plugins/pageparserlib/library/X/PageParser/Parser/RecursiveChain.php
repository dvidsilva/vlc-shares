<?php 

/**
 * Create a chain of parser
 * @author ximarx
 * @ignore
 *
 */
class X_PageParser_Parser_RecursiveChain extends X_PageParser_Parser {
	
	private $list = array();
	
	/**
	 * Create a chain pattern
	 * @param array[X_PageParser_Parser] $list
	 */
	public static function factory($list) {
		return new self($list);
	}
	/* (non-PHPdoc)
	 * @see X_PageParser_Parser_Chain::parse()
	 */
	public function __construct($list) {
		$this->list = (is_array($list) ? $list : array());
	}
	
	/* (non-PHPdoc)
	 * @see X_PageParser_Parser::parse()
	 */
	public function parse($string) {
		foreach ($this->list as $parser ) {
			/* @var $parser X_PageParser_Parser */
			if ( is_array($string) ) {
				$dest = array();
				foreach ( $string as $atom ) {
					$dest[] = $parser->parse((string) $atom);
				}
				$string = dest;
			} else {
				$string = $parser->parse((string) $string);
			}
		}
		return $string;
	}
}
