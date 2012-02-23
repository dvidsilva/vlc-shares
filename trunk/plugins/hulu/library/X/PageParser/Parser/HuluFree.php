<?php 

class X_PageParser_Parser_HuluFree extends X_PageParser_Parser {
	
	const PATTERN_MAIN = '/^Element\.(update\("show_list|replace\("browse-lazy-load)", "(?P<main>.*?)"\);$/sm';
	const PATTERN_ITEMS = '/<a href="http:\/\/www\.hulu\.com\/(?P<href>[^"]+)" class="info_hover".*?<img alt="(?P<label>[^"]+)" border="0" class="thumbnail" (data-|height="80" )src="(?P<thumbnail>[^"]+)".*?<br \/>\s+(?P<description>.*?)\s+<(\/font|img)/';

	/**
	 * (non-PHPdoc)
	 * @see X_PageParser_Parser::parse()
	 */
	public function parse($string) {

		$matches = array();
		if ( !preg_match(self::PATTERN_MAIN	, $string, $matches) ) {
			X_Debug::e('Main pattern failed');
			return array();
		}
		
		$string = $matches['main'];
		
		$string = str_replace(array(
				'\n',
				'\u003c',
				'\u003e'
			), array(
				'',
				'<',
				'>'
			), $string);
		
		$string = stripslashes($string);
		
		//X_Debug::i("Decoded string: {$string}");
		
		$matches = array();
		if ( !preg_match_all(self::PATTERN_ITEMS, $string, $matches, PREG_SET_ORDER) ) {
			X_Debug::e('Items pattern failed: {'.preg_last_error().'}');
			return array();
		}
		
		return $matches;
	}

	
}

