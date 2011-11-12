<?php 

abstract class X_PageParser_Parser {
	
	/**
	 * Parse the $string with a unknown strategy and 
	 * return parsed data
	 * @param string $string
	 * @return mixed
	 */
	abstract public function parse($string);
	
}

