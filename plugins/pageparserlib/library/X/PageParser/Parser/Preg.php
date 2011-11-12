<?php 

/**
 * Execute a preg_* function agains the string with a submitted $pattern
 * @author ximarx
 *
 */
class X_PageParser_Parser_Preg extends X_PageParser_Parser {
	
	const PREG_MATCH_ALL = 0;
	const PREG_MATCH = 1;
	const PREG_SPLIT = 2;
	
	private $pattern = '';
	private $function = '';
	private $flags = null;
	
	/**
	 * Get a Preg parser object initied
	 * @param string $pattern
	 * @param int $function
	 * @param int $flags
	 * @return X_PageParser_Parser_Preg
	 */
	public static function factory($pattern, $function = self::PREG_MATCH_ALL, $flags = null) {
		return new self($pattern, $function, $flags);
	}
	
	/* (non-PHPdoc)
	 * @see X_PageParser_Parser::factory()
	 */
	public function __construct($pattern, $function = self::PREG_MATCH_ALL, $flags = null) {
		$this->pattern = $pattern;
		$this->function = $function;
		$this->flags = $flags;
	}
	
	/* (non-PHPdoc)
	 * @see X_PageParser_Parser::parse()
	 */
	public function parse($string) {
		$parsed = array();
		switch ( $this->function ) {
			case self::PREG_MATCH:
				if ( @preg_match($this->pattern, $string, $parsed, $this->flags) === false ) {
					X_Debug::w("Invalid pattern (". preg_last_error() ."): {$this->pattern}");
					$parsed = array();
				}
				break;
			case self::PREG_MATCH_ALL:
				if ( @preg_match_all($this->pattern, $string, $parsed, $this->flags) === false ) {
					X_Debug::w("Invalid pattern (". preg_last_error() ."): {$this->pattern}");
					$parsed = array();
				}
				break;
			case self::PREG_SPLIT:
				$parsed = @preg_split($this->pattern, $string, null, $this->flags);
				if ( $parsed === false ) {
					X_Debug::w("Invalid pattern (". preg_last_error() ."): {$this->pattern}");
					$parsed = array();
				}
				break;
			default: 
				X_Debug::e("Invalid function code provided: {$this->function}");
		}
		return $parsed;
		
	}
}
