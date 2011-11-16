<?php 

class X_PageParser_Parser_YouPorn extends X_PageParser_Parser {

	const MODE_VIDEOS = 0;
	const MODE_NEXTPAGE = 99;
	
	private $mode = self::MODE_VIDEOS;
	
	const PATTERN_VIDEOS = '%<img id="thumb[0-9]+" src="(?P<thumbnail>[^\"]+?)" .*?<h1><a href="/watch/(?P<id>[0-9]+)/[^\?]+?">(?P<label>.*?)</a></h1>%is';
	
	const PATTERN_NEXTPAGE = '%<a href="\/.*?\?page\=[0-9]+">.+?&#187;</a>%i';
	
	public function __construct($mode = self::MODE_VIDEOS) {
		$this->mode = $mode;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see X_PageParser_Parser::parse()
	 */
	public function parse($string) {
		switch ($this->mode) {
			case self::MODE_VIDEOS: return $this->parseVideos($string);
			case self::MODE_NEXTPAGE: return $this->parseNextPage($string);
			default: throw new Exception("Invalid mode: {".$this->mode."}");
		}
		
	}

	private function parseVideos($string) {
		$matches = array();
		$links = array();
		preg_match_all(self::PATTERN_VIDEOS, $string, $matches, PREG_SET_ORDER);
		X_Debug::i(sprintf("Links found: %s", count($matches)));
		
		// process links
		foreach ($matches as $match ) {
			
			$label = $match['label'];
			$id = $match['id'];
			$thumbnail = $match['thumbnail'];
			
			$link = array();
			$link['label'] = $label;
			$link['thumbnail'] = $thumbnail;
			$link['id'] = $id;
			$links[] = $link;
			
		}
		
		return $links;
	}
	
	private function parseNextPage($string) {
		return ( preg_match(self::PATTERN_NEXTPAGE, $string) != 0 );
	}
	
}

