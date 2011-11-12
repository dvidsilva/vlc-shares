<?php 


class X_PageParser_Page {
	
	/**
	 * @var string
	 */
	private $uri = '';
	/**
	 * @var X_PageParser_Parser
	 */
	private $parser = null;
	/**
	 * @var X_PageParser_Loader
	 */
	private $loader = null;
	/**
	 * @var array|mixed
	 */
	private $parsed = null;
	
	protected function __construct($uri, X_PageParser_Parser $parser) {
		$this->changeUri($uri, $parser);
	}
	
	/**
	 * Get a Page object for the $uri and setup it to be parsed by $parser 
	 * @param string $uri
	 * @param X_PageParser_Parser $parser
	 * @return X_PageParser_Page
	 */
	static public function getPage($uri, X_PageParser_Parser $parser) {
		return new self($uri, $parser);
	}
	
	/**
	 * Setup a Page object for a protected $uri to be parser
	 * @param string $uri
	 * @param X_PageParser_Parser $parser
	 * @param X_PageParser_AuthStrategy $authstrategy
	 * @param X_PageParser_AuthDeposit $authdeposit
	 * @return X_PageParser_Page
	 */
	static public function getProtectedPage($uri, X_PageParser_Parser $parser, X_PageParser_AuthStrategy $authstrategy, X_PageParser_AuthDeposit $authdeposit ) {
		$page = self::getPage($uri, $parser);
		return $page->setLoader(
				X_PageParser_Loader_HttpAuthRequired::instance()
					->setAuthStrategy($authstrategy)
					->setAuthDeposit($authdeposit)
		);
	} 
	
	/**
	 * Get things found by the $parser
	 * @see X_PageParser_Parser
	 * @return mixed
	 */
	public function getParsed(X_PageParser_Parser $parser = null) {
		if ( !is_null($parser) ) {
			X_Debug::i("Temp pattern");
			$loaded = $this->getLoader()->getPage($this->uri);
			return $parser->parse($loaded);
		} elseif ( $this->parsed === null ) {
			$loaded = $this->getLoader()->getPage($this->uri);
			$this->parsed = $this->getParser()->parse($loaded);
		}
		return $this->parsed;
	}
	
	/**
	 * @return X_PageParser_Parser
	 */
	public function getParser() {
		return $this->parser;
	}

	/**
	 * Change the Page object url (resetting eventually parsed data)
	 * and changing the parser (if $newParser provided)
	 * @param string $newUri
	 * @param X_PageParser_Parser $newParser
	 */
	public function changeUri($newUri, $newParser = null) {
		$this->uri = $newUri;
		if ( !is_null($newParser) && $newParser instanceof X_PageParser_Parser ) {
			$this->parser = $newParser;
		}
		$this->parsed = null;
		return $this;
	}
	
	/**
	 * @return X_PageParser_Loader
	 */
	public function getLoader() {
		if ( $this->loader === null ) {
			$this->loader = new X_PageParser_Loader_Http();
		}
		return $this->loader;
	}
	
	/**
	 * Set the page loader
	 * @param X_PageParser_Loader $loader
	 * @return X_PageParser_Page
	 */
	public function setLoader(X_PageParser_Loader $loader) {
		$this->loader = $loader;
		return $this;
	}
	
	
}

