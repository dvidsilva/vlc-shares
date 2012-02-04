<?php 

class X_PageParser_Parser_TvLinks extends X_PageParser_Parser {

	const MODE_TITLES = 0;
	const MODE_EPISODES = 1;
	const MODE_LINKS = 2;
	const MODE_NEXTPAGE = 99;
	
	private $mode = self::MODE_TITLES;
	
	private $type = '';
	private $filter = '';
	private $title = '';
	private $season = '';
	private $episode = '';
	
	const PATTERN_TITLES = '#<li> <a href="/[^\/]+/(?P<title>[^\/]+?)/" class="list cfix"> <span class="c1">(?P<label>.*?)</?span.*?</a> </li>#i';
	
	const PATTERN_SEASONS = '#<div class="bg_imp biggest.*? id=".*?">((<a.*?>)?)Season (?P<season>.+?)((<em.*?/a>)?)</div> <ul (?P<episodes>.*?)</ul>#si';
	const PATTERN_EPISODES = '#<li> <a href="/.*?/.*?/season_(?P<season>[0-9]+)/episode_(?P<episode>[0-9]+)/" class="list cfix".*?<span class="c1">Episode(?P<labelPrefix>.+?)</span> <span class="c2">(?P<label>.+?)<.*?</li>#i';
	
	const PATTERN_THUMBNAIL = '#<img src="(?P<thumb>[^\"]+?)" class="img_mov#';
	
	const PATTERN_LINKS = '%onclick="return frameLink\(\'(?P<link>.*?)\'\);">.*?<span class="bold">(?P<hoster>.+?)</span>%i';
	
	const PATTERN_NEXTPAGE = '%<a href="#" onclick="return chPage(.*?)">next</a>%i';
	
	public function __construct($mode = self::MODE_TITLES, $type = '', $filter = '', $title = '', $season = '', $episode = '') {
		$this->mode = $mode;
		$this->type = $type;
		$this->filter = $filter;
		$this->title = $title;
		$this->season = $season;
		$this->episode = $episode;
	}
	

	/**
	 * (non-PHPdoc)
	 * @see X_PageParser_Parser::parse()
	 */
	public function parse($string) {
		switch ($this->mode) {
			case self::MODE_TITLES: return $this->parseTitles($string);
			case self::MODE_EPISODES: return $this->parseEpisodes($string);
			case self::MODE_LINKS: return $this->parseLinks($string);
			case self::MODE_NEXTPAGE: return $this->parseNextPage($string);
			default: throw new Exception("Invalid mode: {".$this->mode."}");
		}
		
		// dead code 

		
		// first, find the thumb url:
		$match = array();
		$thumb = false;
		preg_match(self::PATTERN_THUMBNAIL, $string, $match);
		// avoid relative thumbnails
		if ( count($match) > 0 && X_Env::startWith($match['image'], 'http://') ) {
			$thumb = $match['image'];
			X_Debug::i("Thumbnail found: {$thumb}");
		}
		
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
			$cLink['thumbnail'] = $thumb;
			
			$cleanedLinks[] = $cLink;
		}
		
		return $cleanedLinks;
	}
	
	
	private function parseTitles($string) {
		$matches = array();
		preg_match_all(self::PATTERN_TITLES, $string, $matches, PREG_SET_ORDER);
		//X_Debug::i(print_r($matches, true));
		X_Debug::i(sprintf("Titles found: %s", count($matches)));
		return $matches;
	}

	private function parseEpisodes($string) {
		
		preg_match(self::PATTERN_THUMBNAIL, $string, $thumbMatch);
		$thumbnail = @$thumbMatch['thumb'];
		
		$episodes = array();
		
		//$dumpOne = true;
		//$dumpOne2 = true;
		
		$seasonsMatches = array();
		preg_match_all(self::PATTERN_SEASONS, $string, $seasonsMatches, PREG_SET_ORDER);
		X_Debug::i(sprintf("Seasons found: %s", count($seasonsMatches)));
		foreach ($seasonsMatches as $seasonMatch) {
			//if ( $dumpOne2 ) { X_Debug::i("Dump one: ".print_r($seasonMatch, true)); $dumpOne2 = false; }
			$seasonLabel = $seasonMatch['season'];
			$episodesMatches = array();
			preg_match_all(self::PATTERN_EPISODES, $seasonMatch['episodes'], $episodesMatches, PREG_SET_ORDER);
			//X_Debug::i(print_r($episodesMatches, true));
			X_Debug::i(sprintf("Episodes in season {%s} found: %s", $seasonLabel, count($episodesMatches)));
			foreach($episodesMatches as $episodeMatch) {
				
				//if ( $dumpOne ) { X_Debug::i("Dump one: ".print_r($episodeMatch, true)); $dumpOne = false;}
				
				$labelPrefix = $episodeMatch['labelPrefix'];
				$label = $episodeMatch['label'];
				$episodeNumber = $episodeMatch['episode'];
				$seasonNumber = $episodeMatch['season'];
				
				$episode = array();
				$episode['episode'] = $episodeNumber;
				$episode['season'] = $seasonNumber;
				$seasonNumber = str_pad($seasonNumber, 2, "0", STR_PAD_LEFT );
				$episodeNumber = str_pad($episodeNumber, 2, "0", STR_PAD_LEFT );
				
				$episode['label'] = "[{$seasonNumber}x{$episodeNumber}] {$label}";
				if ( $thumbnail ) $episode['thumbnail'] = $thumbnail;
				$episodes[] = $episode;
			}
		}
		
		// process links
	
		return $episodes;
	}
	
	private function parseLinks($string) {
		$matches = array();
		$links = array();
		preg_match_all(self::PATTERN_LINKS, $string, $matches, PREG_SET_ORDER);
		X_Debug::i(sprintf("Links found: %s", count($matches)));
		
		// process links
		foreach ($matches as $match ) {
			
			//$label = $match['label'];
			//$num = $match['number'];
			$id = $match['link'];
			$hoster = $match['hoster'];
			
			$link = array();
			//$link['label'] = "#{$num}: {$label} [{$hoster}]";
			$link['hoster'] = $hoster;
			$link['id'] = base64_decode($id);
			$links[] = $link;
			
		}
		
		return $links;
	}
	
	private function parseNextPage($string) {
		return ( preg_match(self::PATTERN_NEXTPAGE, $string) != 0 );
	}
	
	
}

