<?php 

class X_PageParser_Parser_StreamingOnlineVideos extends X_PageParser_Parser {
	
	const PATTERN_THUMBNAIL = '/<div class="item".*?<img.*?src="(?P<image>[^\"]*?)"/is';
	const PATTERN_LINKS_OLD = '/((<p>(?P<pre>[^<]*?))?)<a.*?href="(?P<href>.+?)".*?>(?P<label>.*?)<\/a>/is';
	const PATTERN_LINKS = '/((?P<startline><p>(?P<pre>[^<]*?))?)<a.*?href="(?P<href>.+?)".*?>(?P<label>.*?)<\/a>/is';
	
	private static $instance = null;
	
	static public function instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	protected function __construct() {}
	

	/**
	 * (non-PHPdoc)
	 * @see X_PageParser_Parser::parse()
	 */
	public function parse($string) {
		
		// first, find the thumb url:
		$match = array();
		$thumb = false;
		/*
		preg_match(self::PATTERN_THUMBNAIL, $string, $match);
		// avoid relative thumbnails
		if ( count($match) > 0 && X_Env::startWith($match['image'], 'http://') ) {
			$thumb = $match['image'];
			X_Debug::i("Thumbnail found: {$thumb}");
		}
		*/
		
		/* @var $hosterHelper X_VlcShares_Plugins_Helper_Hoster */
		$hosterHelper = X_VlcShares_Plugins::helpers()->hoster();
		
		/*
		$subparser = X_PageParser_Parser_HosterLinks::factory($hosterHelper,
				 X_PageParser_Parser_Preg::factory(self::PATTERN_LINKS, X_PageParser_Parser_Preg::PREG_MATCH_ALL, PREG_SET_ORDER)
		);
		$links = $subparser->parse($string);
		X_Debug::i("Valid hosters link found: ".count($links));
		*/
		
		$subparser = X_PageParser_Parser_Preg::factory(self::PATTERN_LINKS, X_PageParser_Parser_Preg::PREG_MATCH_ALL, PREG_SET_ORDER);
		$links = $subparser->parse($string);
		X_Debug::i("Step1 links: ".count($links));
		
		// clean results and reformat them
		$cleanedLinks = array();
		
		$pre = false;
		$startLine = false;
		
		foreach ( $links as $link ) {
			$cLink = array();
			
			// first check if valid
			try {
				$hoster = $hosterHelper->findHoster($link['href']);

				// link is valid
				if ( $link['startline'] ) {
					if ( $link['pre'] ) {
						$pre = $link['pre'];
					} else {
						$pre = false;
					}
				}
				
				$cLink['hosterId'] = $hoster->getId();
				$cLink['videoId'] = $hoster->getResourceId($link['href']);
				$cLink['label'] = $pre." ".trim(strip_tags($link['label']));
				$cLink['link'] = "{$cLink['hosterId']}:{$cLink['videoId']}";
				//$cLink['thumbnail'] = $thumb;
				
				$cleanedLinks[] = $cLink;
			} catch (Exception $e) {
				
				// the link is invalid
				// so if it's a startline, the label has to be added to the eventual "pre" (if any).
				if ( $link['startline'] ) {
					if ( $link['pre'] ) { 
						$pre = $link['pre'];
					} else {
						$pre = $link['label'];
					}
				}
			}
		}
		
		X_Debug::i("Step2 links: ".count($cleanedLinks));
		
		return $cleanedLinks;
	}
}

