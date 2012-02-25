<?php 


class X_VlcShares_Plugins_Helper_AnimeFTW implements X_VlcShares_Plugins_Helper_Interface {
	
	const VERSION = '0.3.1';
	const VERSION_CLEAN = '0.3.1';
	
	const API_KEY = 'gh7h-9b4k-2ofr-5o8l';
	
	const API_URL = 'https://www.animeftw.tv/api/v1/show?did=%1$s&username=%2$s&password=%3$s&%4$s';
	
	/**
	 * @var Zend_Config
	 */
	private $options = null;
	
	private $perpage = 25;
	
	public function __construct($options) {
		
		if ( is_array($options) ) {
			$options = new Zend_Config($options);
		}
		
		if ( $options instanceof Zend_Config ) {
			$this->options = $options;
		} else {
			$this->options = new Zend_Config(array());
		}
	}
	
	
	public function getAnime($filter = '') {
		$url = $this->buildUrl("show=anime", 0, 1000, $filter);
		
		$xml = $this->loadUrl($url);
		
		$dom = new SimpleXMLElement($xml);
		
		$series = array();
		
		foreach ($dom->series as $serie) {
			
			$aSerie = array(
				'id' => trim((string) $serie->id),
				'label' => trim((string) $serie->seriesName),
				'romaji' => trim((string) $serie->romaji),
				'description' => trim(strip_tags((string) $serie->description )),
				'thumbnail' => trim((string) $serie->image),
				'episodes' => trim((string) $serie->episodes),
				'movies' => trim((string) $serie->movies),
			);
			
			$href = $serie['href'];
			
			parse_url($href, PHP_URL_QUERY);
			
			$params = array();
			parse_str($href, $params);
			
			$aSerie['href'] = $params['title'];

			$series[] = $aSerie;
		}
		
		return $series;
	}

	public function getEpisodes($title) {
		
		$url = $this->buildUrl("&show=series&title=$title", 0, 1000);
		
		$xml = $this->loadUrl($url);
		
		$dom = new SimpleXMLElement($xml);
		
		$episodes = array();
		
		foreach ($dom->episodes->episode as $episode) {
			
			$episodes[] = $this->parseEpisode($episode);
			
		}
		
		if ( isset($dom->movies) ) {
			foreach ($dom->movies->movie as $movie) {
				
				$aMovie = $this->parseEpisode($movie);
				$aMovie['movie'] = true;
				
				$episodes[] = $aMovie;
			}
		}
		
		X_Debug::i(print_r($episodes, true));
		
		return $episodes;		
	}
	
	public function getEpisode($epId) {
		
		$url = $this->buildUrl("&show=episode&id=$epId");
		
		$xml = $this->loadUrl($url);
		
		$dom = new SimpleXMLElement($xml);
		
		return $this->parseEpisode($dom->episode[0]);
		
	}
	
	protected function parseEpisode($episodeNode) {
		
		/*
		<id>7018</id>
		<name>The Future Of Painful Thoughts Is</name>
		<epnumber>1</epnumber>
		<height>396</height>
		<width>704</width>
		<fansub>Dattebayo</fansub>
		<movie>No</movie>
		<type>mkv</type>
		<added format="gmt -6">1272218361</added>
		<videolink>
		http://static.ftw-cdn.com/videos2/07ghost/07ghost_1_ns.mkv
		</videolink>
		</episode>		
		*/
		
		$episode = array(
			'id' => trim((string) $episodeNode->id),
			'epnumber' => trim((string) $episodeNode->epnumber),
			'label' => trim((string) $episodeNode->name),
			'movie' => false,
			'type' => trim((string) $episodeNode->type),
			'url' => trim((string) $episodeNode->videolink),
			'thumbnail' => ''
		);
		
		return $episode;
		
	}
	
	public function getGenres() {
		
		$url = $this->buildUrl("show=anime", 0, 1000);
		
		$xml = $this->loadUrl($url, 60);
		
		$dom = new SimpleXMLElement($xml);
		
		$categories = array();
		
		foreach ($dom->series as $serie) {
			$category = (string) $serie->category;
			
			$exploded = explode(',', $category);
			
			$exploded = array_values(array_map('ucfirst', array_map('trim', $exploded)));
			
			foreach ($exploded as $cat) {
				if ( array_key_exists($cat, $categories) ) {
					$categories[$cat]++; 
				} else {
					$categories[$cat] = 1;
				}
			}
			
		}
		return $categories;
	}
	
	
	protected function buildUrl($queryType, $page = 0, $perpage = 100, $filter = '' ) {
		
		$page = $page * $perpage;
		$filter = urlencode($filter);
		
		return sprintf(self::API_URL,
			 self::API_KEY,
			 $this->options->get('username', ''),
			 md5($this->options->get('password', '')),
			 "$queryType&start=$page&count=$perpage" . ($filter != '' || true ? "&filter=$filter" : '')
		);
		
	}
	
	protected function loadUrl($url, $validity = 5) {
		
		X_Debug::i("Loading: $url");
		
		try {
			/* @var $cache X_VlcShares_Plugins_Helper_Cache */
			$cache = X_VlcShares_Plugins::helpers()->helper('cache');
			return $cache->retrieveItem($url);
		} catch (Exception $e) {
			$http = new Zend_Http_Client($url);
			$http->setHeaders(
				'User-Agent: vlc-shares/'.X_VlcShares::VERSION.' animeftw/'.self::VERSION
			);
			
			$string = $http->request()->getBody();
			
			$string = $this->fixXml($string);
			
			try {
				/* @var $cache X_VlcShares_Plugins_Helper_Cache */
				$cache = X_VlcShares_Plugins::helpers()->helper('cache');
				$cache->storeItem($url, $string, $validity);
			} catch (Exception $e) {
				// no cache at all
			}
			return $string;
		}
	}
	
	protected function fixXml($string) {
		return str_replace('<![CDATA[]]>', '', $string);
	}
	
}
