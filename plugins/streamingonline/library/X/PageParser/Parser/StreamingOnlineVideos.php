<?php 

class X_PageParser_Parser_StreamingOnlineVideos extends X_PageParser_Parser {
	
	const PATTERN_THUMBNAIL = '/<div class="item".*?<img.*?src="(?P<image>[^\"]*?)"/is';
	const PATTERN_LINKS = '/<a .*?href="(?P<href>.+?)".*?>(?P<label>.*?)<\/a>/is';
	
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
		
		$subparser = X_PageParser_Parser_HosterLinks::factory($hosterHelper,
				 X_PageParser_Parser_Preg::factory(self::PATTERN_LINKS, X_PageParser_Parser_Preg::PREG_MATCH_ALL, PREG_SET_ORDER)
		);
		$links = $subparser->parse($string);
		X_Debug::i("Valid hosters link found: ".count($links));
		// clean results and reformat them
		$cleanedLinks = array();
		foreach ( $links as $link ) {
			$cLink = array();
			$cLink['hosterId'] = $link['hoster']->getId();
			$cLink['videoId'] = $link['hoster']->getResourceId($link['url']);
			$cLink['label'] = strip_tags($link['label']);
			$cLink['link'] = "{$cLink['hosterId']}:{$cLink['videoId']}";
			//$cLink['thumbnail'] = $thumb;
			
			$cleanedLinks[] = $cLink;
		}
		
		return $cleanedLinks;
	}
}

