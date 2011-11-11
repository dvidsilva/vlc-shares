<?php 

/**
 * Create a chain of parser, result will be aggregate using array_merge
 * @author ximarx
 *
 */
class X_PageParser_Parser_AggregateChain extends X_PageParser_Parser {
	
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
		$parsed = array();
		foreach ($this->list as $parser ) {
			/* @var $parser X_PageParser_Parser */
			$parsedNow = $parser->parse($string);
			if ( !is_array($parsedNow) ) {
				$parsed[] = $parsedNow;
			} else {
				$parsed = array_merge($parsed, $parsedNow);
			}
		}
		return $parsed;
	}
}
