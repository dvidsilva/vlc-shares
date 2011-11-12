<?php 

/**
 * Search all valid hosters link inside a string. This parser search for 
 * href of a tag only. Return type of parse is a array of matches.
 * Matches format:
 * array(
 * 	array(
 * 		'hoster'  => instanceof X_VlcShares_Plugins_Helper_HostInterface,
 * 		'url' => href value matched by hoster,
 * 		'label' => a tag value (html tags stripped)
 *  ),...
 * )
 * 
 * @author ximarx
 *
 */
class X_PageParser_Parser_HosterLinks extends X_PageParser_Parser {
	
	const PREG_MATCH_ALL = 0;
	const PREG_MATCH = 1;
	const PREG_SPLIT = 2;
	
	const LINK_PATTERN = '/<a.*?href\=[\"\']?(?P<href>[^\"\']+)[\"\']?[^\>]*?>(?P<label>.*?)<\/a>/is';
	
	/**
	 * @var X_VlcShares_Plugins_Helper_Hoster
	 */
	private $helper = null;
	/**
	 * @var X_PageParser_Parser_Preg
	 */
	private $subparser = null;
	
	/**
	 * Get a Preg parser object initied
	 * @param X_VlcShares_Plugins_Helper_Hoster $hosterHelper
	 * @param X_PageParser_Parser_Preg $subparser a subparser which fetch link url=>label in format array(href => '', label => '')
	 * @return X_PageParser_Parser_HosterLinks
	 */
	public static function factory(X_VlcShares_Plugins_Helper_Hoster $hosterHelper,X_PageParser_Parser_Preg $subparser = null) {
		return new self($hosterHelper, $subparser);
	}
	
	/* (non-PHPdoc)
	 * @see X_PageParser_Parser::factory()
	 */
	public function __construct(X_VlcShares_Plugins_Helper_Hoster $hosterHelper,X_PageParser_Parser_Preg $subparser = null) {
		$this->helper = $hosterHelper;
		$this->subparser = ($subparser ? $subparser : X_PageParser_Parser_Preg::factory(self::LINK_PATTERN, X_PageParser_Parser_Preg::PREG_MATCH_ALL, PREG_SET_ORDER)); 
	}
	
	/* (non-PHPdoc)
	 * @see X_PageParser_Parser::parse()
	 */
	public function parse($string) {
		$parsed = array();
		$parsedClean = array();
		// first find all <a> tags
		$parsed = $this->subparser->parse($string);
		//X_Debug::i("Atoms found: ".count($parsed));
		foreach ( $parsed as $found ) {
			try {
				$hoster = $this->helper->findHoster($found['href']);
				$parsedClean[] = array(
					'hoster' => $hoster,
					'url' => $found['href'],
					'label' => strip_tags($found['label'])
				);
			} catch (Exception $e) {/* invalid href, ignored *//*X_Debug::i("No hoster for link: {$found['href']}");*/ }
		}
		return $parsedClean;
	}
}
