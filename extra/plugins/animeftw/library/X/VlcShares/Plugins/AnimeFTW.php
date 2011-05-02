<?php

require_once 'Zend/Http/CookieJar.php';
require_once 'Zend/Http/Cookie.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'Zend/Dom/Query.php';


/**
 * Add animeftw.tv site as a video source, using rest api
 * 
 * @version 0.3
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_AnimeFTW extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_ResolverInterface {
	
	const VERSION = '0.3';
	const VERSION_CLEAN = '0.3';
	
	const BASE_URL = 'http://www.animeftw.tv/';
	const PAGE_LOGIN = 'http://www.animeftw.tv/login';
	const PAGE_SERIES = 'http://www.animeftw.tv/videos';
	const PAGE_MOVIES = 'http://www.animeftw.tv/movies';
	const PAGE_OAV = 'http://www.animeftw.tv/ovas';
	
	const TYPE_SERIES_PERLETTER = 'videospletter';
	const TYPE_SERIES_PERGENRE = 'videospgenre';
	const TYPE_SERIES = 'videos';
	const TYPE_MOVIES = 'movies';
	const TYPE_OAV = 'ovas';
	
	/**
	 * @var Zend_Http_CookieJar
	 */
	private $jar = null;
	
	
	public function __construct() {
		$this->setPriority('gen_beforeInit')
			->setPriority('getCollectionsItems')
			->setPriority('preRegisterVlcArgs')
			->setPriority('getShareItems')
			->setPriority('preGetModeItems')
			->setPriority('getIndexManageLinks')
			->setPriority('getIndexMessages')
			->setPriority('getTestItems')
			->setPriority('prepareConfigElement')
			;
	}
	
	/**
	 * Inizialize translator for this plugin
	 * @param Zend_Controller_Action $controller
	 */
	function gen_beforeInit(Zend_Controller_Action $controller) {
		$this->helpers()->language()->addTranslation(__CLASS__);
		$this->helpers()->registerHelper('animeftw', new X_VlcShares_Plugins_Helper_AnimeFTW(array(
			'username' => $this->config('auth.username', ''),
			'password' => $this->config('auth.password', ''),
		)));
	}
	
	/**
	 * Add the main link for animeftw
	 * @param Zend_Controller_Action $controller
	 */
	public function getCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");
		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_animeftw_collectionindex'));
		$link->setIcon('/images/animeftw/logo.jpg')
			->setDescription(X_Env::_('p_animeftw_collectionindex_desc'))
			->setType(X_Page_Item_PItem::TYPE_CONTAINER)
			->setLink(
				array(
					'controller' => 'browse',
					'action' => 'share',
					'p' => $this->getId(),
				), 'default', true
			);
		return new X_Page_ItemList_PItem(array($link));
	}
	
	/**
	 * Get category/video list
	 * @param unknown_type $provider
	 * @param unknown_type $location
	 * @param Zend_Controller_Action $controller
	 */
	public function getShareItems($provider, $location, Zend_Controller_Action $controller) {
		
		if ( $provider != $this->getId() ) return;
		
		X_Debug::i('Plugin triggered');

		X_VlcShares_Plugins::broker()->unregisterPluginClass('X_VlcShares_Plugins_SortItems');
		
		$items = new X_Page_ItemList_PItem();
		
		X_Debug::i("Requested location: $location");
		
		$split = $location != '' ? @explode('/', $location, 5) : array();
		@list($type, $letter, $thread, $video) = $split;
		
		X_Debug::i("Exploded location: ".var_export($split, true));
		
		switch ( count($split) ) {
			// i should not be here, so i fallback to video case
			case 4:
			// show the list of video in the page
			case 3:
				$this->_fetchVideos($items, $type, $letter, $thread);
				break;
			// fetch the list of anime in group
			case 2:
				$this->_fetchThreads($items, $type, $letter);
				break;
			case 1:
				$this->_fetchClassification($items, $type);
				break;
			// fetch the list of groups
			default:
				$this->_fetchType($items);
		}
				
		return $items;
		
	}
	
	/**
	 * This hook can be used to add low priority args in vlc stack
	 * 
	 * @param X_Vlc $vlc vlc wrapper object
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to stream
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 */
	public function preRegisterVlcArgs(X_Vlc $vlc, $provider, $location, Zend_Controller_Action $controller) {
	
		// this plugin inject params only if this is the provider
		if ( $provider != $this->getId() ) return;

		// i need to register source as first, because subtitles plugin use source
		// for create subfile
		
		X_Debug::i('Plugin triggered');
		
		$location = $this->resolveLocation($location);
		
		if ( $location !== null ) {
			// TODO adapt to newer api when ready
			$vlc->registerArg('source', "\"$location\"");			
		} else {
			X_Debug::e("No source o_O");
		}
	
	}
	
	/**
	 *	Add button -watch megavideo stream directly-
	 * 
	 * @param string $provider
	 * @param string $location
	 * @param Zend_Controller_Action $controller
	 */
	public function preGetModeItems($provider, $location, Zend_Controller_Action $controller) {

		if ( $provider != $this->getId()) return;
		
		X_Debug::i("Plugin triggered");
		
		$url = $this->resolveLocation($location);
		
		if ( $url ) {
			$link = new X_Page_Item_PItem('core-directwatch', X_Env::_('p_animeftw_watchdirectly'));
			$link->setIcon('/images/icons/play.png')
				->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
				->setLink($url);
			return new X_Page_ItemList_PItem(array($link));
		} else {
			// if there is no link, i have to remove start-vlc button
			// and replace it with a Warning button
			
			X_Debug::i('Setting priority to filterModeItems');
			$this->setPriority('filterModeItems', 99);
			
			$link = new X_Page_Item_PItem('megavideo-warning', X_Env::_('p_animeftw_invalidlink'));
			$link->setIcon('/images/msg_error.png')
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink(array (
					'controller' => 'browse',
					'action' => 'share',
					'p'	=> $this->getId(),
					'l' => X_Env::encode($this->getParentLocation($location)),
				), 'default', true);
			return new X_Page_ItemList_PItem(array($link));

		}
	}
	
	/**
	 * Remove vlc-play button if location is invalid
	 * @param X_Page_Item_PItem $item,
	 * @param string $provider
	 * @param Zend_Controller_Action $controller
	 */
	public function filterModeItems(X_Page_Item_PItem $item, $provider,Zend_Controller_Action $controller) {
		if ( $item->getKey() == 'core-play') {
			X_Debug::i('plugin triggered');
			X_Debug::w('core-play flagged as invalid because the link is invalid');
			return false;
		}
	}
	
	
	/**
	 * animeftw tests:
	 * 	- check for username & password
	 *  - check for data/animeftw/ path writable
	 * @param Zend_Config $options
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_Message
	 */
	public function getTestItems(Zend_Config $options,Zend_Controller_Action $controller) {
		
		$tests = new X_Page_ItemList_Test(); 
		$test = new X_Page_Item_Test($this->getId().'-writeaccess', '[AnimeFTW] Checking for write access to /data/animeftw/ folder');
		if ( is_writable(APPLICATION_PATH . '/../data/animeftw/') ) {
			$test->setType(X_Page_Item_Message::TYPE_INFO);
			$test->setReason('Write access granted');
		} else {
			$test->setType(X_Page_Item_Message::TYPE_WARNING);
			$test->setReason("CookieJar file can't be stored, items fetch will be really slow");
		}
		$tests->append($test);
		
		$test = new X_Page_Item_Test($this->getId().'-credentials', '[AnimeFTW] Checking for authentication credentials');
		if ( $this->config('auth.username', '') != '' && $this->config('auth.password', '') != '' ) {
			$test->setType(X_Page_Item_Message::TYPE_INFO);
			$test->setReason('Credentials configurated');
		} else {
			$test->setType(X_Page_Item_Message::TYPE_FATAL);
			$test->setReason("Credentials not configurated. Contents cannot be viewed");
		}
		$tests->append($test);
		
		return $tests;
	}
	
	/**
	 * Show a warning message if username and password aren't specified yet
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Message
	 */
	public function getIndexMessages(Zend_Controller_Action $controller) {
		
		X_Debug::i('Plugin triggered');
		
		if ( $this->config('auth.username', '') == '' || $this->config('auth.password', '') == '' ) {
			$m = new X_Page_Item_Message($this->getId(), X_Env::_('p_animeftw_dashboardwarning'));
			$m->setType(X_Page_Item_Message::TYPE_ERROR);
			return new X_Page_ItemList_Message(array($m));
		}
		
	}
	
	
	private $cachedLocation = array();
	
	/**
	 * @see X_VlcShares_Plugins_ResolverInterface::resolveLocation
	 * @param string $location
	 * @return string real address of a resource
	 */
	function resolveLocation($location = null) {
		if ( $location == '' || $location == null ) return false;
		
		if ( array_key_exists($location, $this->cachedLocation) ) {
			return $this->cachedLocation[$location];
		}
		
		X_Debug::i("Requested location: $location");
		
		@list($type, $letter, $thread, $href) = explode('/', $location, 4);

		X_Debug::i("Type: $type, Letter: $letter, Thread: $thread, Ep: $href");
		
		if ( $href == null || $thread == null || $type == null ) {
			$this->cachedLocation[$location] = false;
			return false;	
		}
		
		$return = false;;
		
		if ( $type == self::TYPE_SERIES_PERGENRE || $type == self::TYPE_SERIES_PERLETTER ) {
			
			if ( $this->config('proxy.enabled', true) ) {
						
				// X_Env::routeLink should be deprecated, but now is the best option
				$return = X_Env::routeLink('animeftw','proxy2', array(
					'id' => X_Env::encode($href),
				));
			} else {
					
				
				// $href is the video id
				try {
					/* @var $helper X_VlcShares_Plugins_Helper_AnimeFTW */
					$helper = $this->helpers('animeftw');
					$episode = $helper->getEpisode($href);
				
					if ( @$episode['url'] != '' ) {
						$return = $episode['url'];
					}
					
				} catch (Exception $e) {
					
				}
			}
			
		} else {
		
			// i have to fetch the streaming page :(
			
			$baseUrl = $this->config('base.url', self::BASE_URL);
			$baseUrl .= "$type/$thread/$href";
			$htmlString = $this->_loadPage($baseUrl, true);
			
			
			
			// <param name=\"src\" value=\"([^\"]*)\" \/>
			
			if ( preg_match('/<param name=\"src\" value=\"([^\"]*)\" \/>/', $htmlString, $match) ) {
				// link = match[1]
				
				$linkUrl = $match[1];
				
				if ( strpos($linkUrl, 'megavideo') !== false ) {
				
					@list(, $megavideoID) = explode('/v/', $linkUrl, 2);
					
					X_Debug::i("Megavideo ID: $megavideoID");
					
					try {
						/* @var $megavideo X_VlcShares_Plugins_Helper_Megavideo */
						$megavideo = $this->helpers('megavideo');
						// if server isn't specified, there is no video
						//$megavideo->setLocation($megavideoID);
						if ( $megavideo->setLocation($megavideoID)->getServer() ) {
							$return = $megavideo->getUrl();
						}
					} catch (Exception $e) {
						X_Debug::e($e->getMessage());
					}
					
				} else {
					// files in videos2.animeftw.tv require a valid referer page to be watched
					// and animeftw controller does the magic trick
					if ( /*strpos($linkUrl, 'videos2.animeftw.tv') !== false && */ $this->config('proxy.enabled', true) ) {
						
						// X_Env::routeLink should be deprecated, but now is the best option
						$linkUrl = X_Env::routeLink('animeftw','proxy', array(
							'v' => X_Env::encode($linkUrl),
							'r' => X_Env::encode($baseUrl) // baseUrl is the page containing the link
						));
					}
					
					$return = $linkUrl;
				}
			}
		}
		
		$this->cachedLocation[$location] = $return;
		return $return;
	}
	
	/**
	 * Support for parent location
	 * @see X_VlcShares_Plugins_ResolverInterface::getParentLocation
	 * @param $location
	 */
	function getParentLocation($location = null) {
		if ( $location == '' || $location == null ) return false;
		
		//X_Debug::i($location);
		
		$exploded = @explode('/', $location);
		
		//X_Debug::i(var_export($exploded, true));
		
		$type = $exploded[0];
		
		if ( $type == self::TYPE_MOVIES && (count($exploded) == 4 || count($exploded) == 2)  ) {
			array_pop($exploded);
			// extra pop if in movies and location is complete or is letter
			// because 3rd and 4th position are linked in type movies
		}
		
		array_pop($exploded);
		
		//X_Debug::i(var_export($exploded, true));
		
		if ( count($exploded) >= 1 ) {
			return implode('/', $exploded);
		} else {
			return null;
		}			
		
		
		/*
		if ( $href != null ) {
			return $path;
		} else {
			$lStack = explode(':',$path);
			if ( count($lStack) > 1 ) {
				array_pop($lStack);
				return implode(':', $lStack);
			} else {
				return null;
			}
		}
		*/
	}
	
	
	/**
	 * Add the link for -manage-megavideo-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_animeftw_mlink'));
		$link->setTitle(X_Env::_('p_animeftw_managetitle'))
			->setIcon('/images/animeftw/logo.jpg')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	'animeftw'
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}
	

	/**
	 * Remove cookie.jar if configs change and convert form password to password element
	 * @param string $section
	 * @param string $namespace
	 * @param unknown_type $key
	 * @param Zend_Form_Element $element
	 * @param Zend_Form $form
	 * @param Zend_Controller_Action $controller
	 */
	public function prepareConfigElement($section, $namespace, $key, Zend_Form_Element $element, Zend_Form  $form, Zend_Controller_Action $controller) {
		// nothing to do if this isn't the right section
		if ( $namespace != $this->getId() ) return;
		
		switch ($key) {
			// i have to convert it to a password element
			case 'plugins_animeftw_auth_password':
				$password = $form->createElement('password', 'plugins_animeftw_auth_password', array(
					'label' => $element->getLabel(),
					'description' => $element->getDescription(),
					'renderPassword' => true,
				));
				$form->plugins_animeftw_auth_password = $password;
				break;
		}
		
		// remove cookie.jar if somethings has value
		if ( !$form->isErrors() && !is_null($element->getValue()) && file_exists(APPLICATION_PATH . '/../data/animeftw/cookie.jar') ) {
			if ( @!unlink(APPLICATION_PATH . '/../data/animeftw/cookie.jar') ) {
				X_Debug::e("Error removing cookie.jar");
			}
		}
	}	
	
	

	private function _fetchClassification(X_Page_ItemList_PItem $items, $type) {
		
		if ( $type == self::TYPE_SERIES || $type == self::TYPE_SERIES_PERLETTER ) {
		
			$lets = 'A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';
			$lets = explode(',', $lets);
			
			foreach ( $lets as $l ) {
				$item = new X_Page_Item_PItem($this->getId()."-$type-$l", $l);
				$item->setIcon('/images/icons/folder_32.png')
					->setType(X_Page_Item_PItem::TYPE_CONTAINER)
					->setCustom(__CLASS__.':location', "$type/$l")
					->setLink(array(
						'l'	=>	X_Env::encode("$type/$l")
					), 'default', false);
					
				$items->append($item);
			}
			
			
		} elseif ( $type == self::TYPE_SERIES_PERGENRE ) {
			
			/* @var $helper X_VlcShares_Plugins_Helper_AnimeFTW */
			$helper = $this->helpers('animeftw');
			$genres = $helper->getGenres();

			foreach ( $genres as $genre => $count ) {
				if ( $genre == '' ) continue;
				$item = new X_Page_Item_PItem($this->getId()."-$type-$genre", X_Env::_('p_animeftw_genre', $genre, $count ));
				$item->setIcon('/images/icons/folder_32.png')
					->setType(X_Page_Item_PItem::TYPE_CONTAINER)
					->setCustom(__CLASS__.':location', "$type/$genre")
					->setLink(array(
						'l'	=>	X_Env::encode("$type/$genre")
					), 'default', false);
					
				$items->append($item);
			}
			
		} else {
			$item = new X_Page_Item_PItem($this->getId()."-$type-_", '_');
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', "$type/_")
				->setLink(array(
					'l'	=>	X_Env::encode("$type/_")
				), 'default', false);
				
			$items->append($item);
		}
	}
	
	
	private function _fetchType(X_Page_ItemList_PItem $items) {
		
		
		$item = new X_Page_Item_PItem($this->getId()."-seriesperletter", X_Env::_('p_animeftw_typeseries_perletter'));
		$item->setIcon('/images/icons/folder_32.png')
			->setType(X_Page_Item_PItem::TYPE_CONTAINER)
			->setCustom(__CLASS__.':location', self::TYPE_SERIES_PERLETTER)
			->setLink(array(
				'l'	=>	X_Env::encode(self::TYPE_SERIES_PERLETTER)
			), 'default', false);
			
		$items->append($item);
		

		$item = new X_Page_Item_PItem($this->getId()."-seriespergenre", X_Env::_('p_animeftw_typeseries_pergenre'));
		$item->setIcon('/images/icons/folder_32.png')
			->setType(X_Page_Item_PItem::TYPE_CONTAINER)
			->setCustom(__CLASS__.':location', self::TYPE_SERIES_PERGENRE)
			->setLink(array(
				'l'	=>	X_Env::encode(self::TYPE_SERIES_PERGENRE)
			), 'default', false);
			
		$items->append($item);

		
		if ( $this->config('sitescraper.enabled', false) ) {
			
			$item = new X_Page_Item_PItem($this->getId()."-series", X_Env::_('p_animeftw_typeseries'));
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', self::TYPE_SERIES)
				->setLink(array(
					'l'	=>	X_Env::encode(self::TYPE_SERIES)
				), 'default', false);
				
			$items->append($item);
				
		
			// movies
			$item = new X_Page_Item_PItem($this->getId()."-movies", X_Env::_('p_animeftw_typemovies'));
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', self::TYPE_MOVIES.'/_')
				->setLink(array(
					'l'	=>	X_Env::encode(self::TYPE_MOVIES.'/_')
				), 'default', false);
				
			$items->append($item);
			
			// oav
			$item = new X_Page_Item_PItem($this->getId()."-oav", X_Env::_('p_animeftw_typeoav'));
			$item->setIcon('/images/icons/folder_32.png')
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setCustom(__CLASS__.':location', self::TYPE_OAV)
				->setLink(array(
					'l'	=>	X_Env::encode(self::TYPE_OAV)
				), 'default', false);
				
			$items->append($item);
			
		}
	}
	
	private function _fetchThreadsAPI(X_Page_ItemList_PItem $items, $type, $filter) {
		
		/* @var $helper X_VlcShares_Plugins_Helper_AnimeFTW */
		$helper = $this->helpers('animeftw');
		
		$series = $helper->getAnime($filter);
		
		
		foreach ($series as $serie) {
			
			/*
			
			$serie = array(
				'id' => trim((string) $serie->id),
				'label' => trim((string) $serie->seriesName),
				'romaji' => trim((string) $serie->romaji),
				'description' => trim(strip_tags((string) $series->description )),
				'thumbnail' => trim((string) $serie->image),
				'episodes' => trim((string) $serie->episodes),
				'movies' => trim((string) $serie->movies),
				'href' => value
			);
			 */
			
			$item = new X_Page_Item_PItem($this->getId()."-$type-{$serie['id']}", X_Env::_('p_animeftw_serie', $serie['label'], $serie['romaji'], $serie['episodes'], $serie['movies']  ));
			$item->setIcon('/images/icons/folder_32.png')
				->setThumbnail($serie['thumbnail'])
				->setType(X_Page_Item_PItem::TYPE_CONTAINER )
				->setCustom(__CLASS__.':location', "$type/$filter/{$serie['href']}")
				->setLink(array(
					'l'	=>	X_Env::encode("$type/$filter/{$serie['href']}"),
				), 'default', false);
				
			if ( APPLICATION_ENV == 'development' ) {
				$item->setDescription("$type/$filter/{$serie['href']}");
			} else {
				$item->setDescription($serie['description']);
			}
				
			$items->append($item);
			
			
		}
		
	}
	
	private function _fetchThreads(X_Page_ItemList_PItem $items, $type, $letter) {
		
		X_Debug::i("Fetching threads for $type/$letter");

		switch ($type) {
			case self::TYPE_SERIES_PERGENRE:
			case self::TYPE_SERIES_PERLETTER:
				$this->_fetchThreadsAPI($items, $type, $letter);
				return;
				
			case self::TYPE_SERIES:
				$indexUrl = $this->config('index.series.url', self::PAGE_SERIES);
				// more info for xpath: http://www.questionhub.com/StackOverflow/3428104
				$queryXpath = '//a[@name="'.$letter.'"]/following-sibling::div[count(.| //a[@name="'.$letter.'"]/following-sibling::a[1]/preceding-sibling::div)=count(//a[@name="'.$letter.'"]/following-sibling::a[1]/preceding-sibling::div)]/a';
				break;
			case self::TYPE_OAV:
				$indexUrl = $this->config('index.oav.url', self::PAGE_OAV);
				$queryXpath = '//a[@name="'.$letter.'"]/following-sibling::div[count(.| //a[@name="'.$letter.'"]/following-sibling::a[1]/preceding-sibling::div)=count(//a[@name="'.$letter.'"]/following-sibling::a[1]/preceding-sibling::div)]/a';
				break;
			case self::TYPE_MOVIES:
				$indexUrl = $this->config('index.movies.url', self::PAGE_MOVIES);
				$queryXpath = '//div[@class="mpart"]//a';
				break;
		}
		
		$htmlString = $this->_loadPage($indexUrl, true);
		$dom = new Zend_Dom_Query($htmlString);
		
		// fetch all threads inside the table
		//$results = $dom->queryXpath('//table[@id="streaming_elenco"]//a[@name="' . $letter . '"][text()!=""]/ancestor::table[@id="streaming_elenco"]/tr[@class!="header"]/td[@class="serie"]/a');
		$results = $dom->queryXpath($queryXpath);
		
		X_Debug::i("Threads found: ".$results->count());
		
		for ( $i = 0; $i < $results->count(); $i++, $results->next()) {
		
			$current = $results->current();
			 
			$label = $current->textContent;
			

			$href = $current->getAttribute('href');
			
			if ( $type == self::TYPE_MOVIES ) {
				if ( X_Env::startWith($href, $this->config('index.movie.url', self::PAGE_MOVIES) ) ) {
					$href = substr($href, strlen($this->config('index.movie.url', self::PAGE_MOVIES)));
				} 
				// now href has format: /anime/movies, and we need both
				// so replace / with a safer :
				//$href = str_replace('/', ':', trim($href, '/'));
				$href = trim($href, '/');
			} else {
				// href has format: /category/$NEEDEDVALUE/
				// so i trim / from the bounds and then i get the $NEEDEDVALUE
				@list(,$href) = explode('/', trim($href, '/'));
			}
			
			$item = new X_Page_Item_PItem($this->getId()."-$label", $label);
			$item->setIcon('/images/icons/folder_32.png')
				->setType($type == self::TYPE_MOVIES ? X_Page_Item_PItem::TYPE_ELEMENT : X_Page_Item_PItem::TYPE_CONTAINER )
				->setCustom(__CLASS__.':location', "$type/$letter/$href")
				->setLink(array(
					'l'	=>	X_Env::encode("$type/$letter/$href"),
					'action' => $type == self::TYPE_MOVIES ? 'mode' : 'share'
				), 'default', false);
				
			if ( APPLICATION_ENV == 'development' ) {
				$item->setDescription("$type/$letter/$href");
			}
				
			$items->append($item);
			
		}
		
	}	
	
	private function _fetchVideosAPI(X_Page_ItemList_PItem $items, $type, $filter, $href) {
		
		/* @var $helper X_VlcShares_Plugins_Helper_AnimeFTW */
		$helper = $this->helpers('animeftw');
		
		$episodes = $helper->getEpisodes($href);
		
		
		foreach ($episodes as $episode) {
			
			/*
			
			$episode = array(
				'id' => trim((string) $episodeNode->id),
				'epnumber' => trim((string) $episodeNode->epnumber),
				'label' => trim((string) $episodeNode->name),
				'movie' => false,
				'type' => trim((string) $episodeNode->type),
				'url' => trim((string) $episodeNode->videolink),
				'thumbnail' => ''
			);
			 */
			
			$item = new X_Page_Item_PItem($this->getId()."-$type-{$episode['id']}", X_Env::_('p_animeftw_episode', $episode['label'], $episode['epnumber'], ($episode['movie'] ? X_Env::_('p_animeftw_ismovie') : '' ) ) );
			$item->setIcon("/images/animeftw/file_{$episode['type']}.png")
				->setThumbnail($episode['thumbnail'] != '' ? $episode['thumbnail'] : null )
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setCustom(__CLASS__.':location', "$type/$filter/$href/{$episode['id']}")
				->setLink(array(
					'l'	=>	X_Env::encode("$type/$filter/$href/{$episode['id']}"),
					'action' => 'mode'
				), 'default', false);
				
			if ( APPLICATION_ENV == 'development' ) {
				$item->setDescription("$type/$filter/$href/{$episode['id']}");
			}
				
			$items->append($item);
			
			
		}
		
	}
	
	
	private function _fetchVideos(X_Page_ItemList_PItem $items, $type, $letter, $thread) {
		
		X_Debug::i("Fetching videos for $type/$letter/$thread");
		
		if ( $type == self::TYPE_SERIES_PERGENRE || $type == self::TYPE_SERIES_PERLETTER ) {
			return $this->_fetchVideosAPI($items, $type, $letter, $thread);
		}
		
		$baseUrl = $this->config('base.url', self::BASE_URL);
		
		// threads for movies have / -> : conversion
		$_thread = str_replace(':', '/', $thread);
		$baseUrl .= "$type/$_thread";
		
		$htmlString = $this->_loadPage($baseUrl, true);

		// XPATH doesn't work well. Maybe for kanji inside the page, so i use regex
		
		$cleaned = stristr(strstr($htmlString, 'Episodes:'), '</table>', true);
		
		$regexp = "<a\s[^>]*href=(\"??)([^\" >]*?)\\1[^>]*>(.*)<\/a>";
		if(preg_match_all("/$regexp/siU", $cleaned, $matches, PREG_SET_ORDER)) {
			
			//X_Debug::i(var_export($matches, true));
			
			foreach($matches as $match) {
				// $match[2] = link address
				// $match[3] = link text
				
				$label = $match[3];
				if ( $label == '') {
					$label = X_Env::_('p_animeftw_nonamevideo');
				}
				
				$href = $match[2];
				
				// href format: http://www.animeftw.tv/videos/baccano/ep-3
				if ( X_Env::startWith($href, $this->config('base.url', self::BASE_URL) ) ) {
					$href = substr($href, strlen($this->config('base.url', self::BASE_URL)));
				}
				
				// href format: videos/baccano/ep-3
				
				@list( , ,$epName) = explode('/', trim($href, '/'));
				// works even without the $epName
				$href=$epName;
				
				//$found = true;
				
				X_Debug::i("Valid link found: $href");
				
				$item = new X_Page_Item_PItem($this->getId()."-$label", $label);
				$item->setIcon('/images/icons/folder_32.png')
					->setType(X_Page_Item_PItem::TYPE_ELEMENT)
					->setCustom(__CLASS__.':location', "$type/$letter/$thread/$href")
					->setLink(array(
						'action'	=> 'mode',
						'l'	=>	X_Env::encode("$type/$letter/$thread/$href")
					), 'default', false);
					
				if ( APPLICATION_ENV == 'development' ) {
					$item->setDescription("$type/$letter/$thread/$href");
				}
					
				$items->append($item);
				
			}
		}
		
	}
	
	
	private function _loadPage($uri, $forceAuth = false) {

		X_Debug::i("Loading page $uri");
		
		$http = new Zend_Http_Client($uri, array(
			'maxredirects'	=> $this->config('request.maxredirects', 10),
			'timeout'		=> $this->config('request.timeout', 10),
			'keepalive' => true
		));
		
		$http->setHeaders(array(
			//$this->config('hide.useragent', true) ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1',
			//'Content-Type: application/x-www-form-urlencoded'
			'User-Agent: vlc-shares/'.X_VlcShares::VERSION.' animeftw/'.self::VERSION
		));
		
		$jarFile = APPLICATION_PATH . '/../data/animeftw/cookie.jar';
		$ns = new Zend_Session_Namespace(__CLASS__);
		
		if ( $this->jar == null ) {
			if ( false && isset($ns->jar) && $ns->jar instanceof Zend_Http_CookieJar ) {
				$this->jar = $ns->jar;
				X_Debug::i('Loading stored authentication in Session');
			} elseif ( file_exists($jarFile) ) {
				if ( filectime($jarFile) < (time() -  24 * 60 * 60) ) {
					X_Debug::i('Jarfile is old. Refreshing it');
					@unlink($jarFile);
				} else {
					$this->jar = new Zend_Http_CookieJar();
					$cookies = unserialize(file_get_contents($jarFile));
					foreach ($cookies as $c) {
						$_c = new Zend_Http_Cookie($c['name'], $c['value'], $c['domain'], $c['exp'], $c['path']);
						$this->jar->addCookie($_c);
					}
					X_Debug::i('Loading stored authentication in File');
				}
			}
		}
		
		$http->setCookieJar($this->jar);
		
		
		$response = $http->request();
		$htmlString = $response->getBody();
		
		if ( $forceAuth && $this->config('auth.username', '') != '' && $this->config('auth.password', '') != '' && !$this->_isAuthenticated($htmlString) ) {
			
			X_Debug::i("Autentication needed");
			
			
			$http->setCookieJar(true);
			$pageLogin = $this->config('login.url', self::PAGE_LOGIN);
			$http->setUri($pageLogin);
			$http->setParameterPost ( array (
				// TODO REMOVE AUTH
				'username' => (string) $this->config('auth.username', ''),
				'password' => (string) $this->config('auth.password', ''),
				'_submit_check' => '1',
				'submit' => 'Sign In',
				'remember' => 'on',
				'last_page'	=> 'https://www.animeftw.tv',
			 ) );
				
			// TODO remove this
			if ( APPLICATION_ENV == 'development' ) {
				$response = $http->request(Zend_Http_Client::POST);
				if ( !$this->_isAuthenticated($response->getBody())) {
					X_Debug::w('Wrong credentials or authentication procedure doesn\'t work');
				} else {
					X_Debug::w('Client authenticated. Full access granted');
				}
				//X_Debug::i($response->getBody());
			} else {
				$http->request(Zend_Http_Client::POST);
			}
			
			$this->jar = $http->getCookieJar();
			// store the cookiejar
			
			$cks = $this->jar->getAllCookies(Zend_Http_CookieJar::COOKIE_OBJECT);
			foreach ($cks as $i => $c) {
				/* @var $c Zend_Http_Cookie */
				$cks[$i] = array(
					'domain' => $c->getDomain(),
					'exp' => $c->getExpiryTime(),
					'name' => $c->getName(),
					'path' => $c->getPath(),
					'value' => $c->getValue()
				);
			} 
			if ( @file_put_contents($jarFile, serialize($cks), LOCK_EX) === false ) {
				X_Debug::e('Error while writing jar file. Check permissions. Everything will work, but much more slower');
			}
			
			//$ns->jar = $this->jar;
			
			// time to do a new old request
			//$http->resetParameters();
			$http->setUri($uri);
			$response = $http->request(Zend_Http_Client::GET);
			$htmlString = $response->getBody();
			
			
			
		}
		
		
		return $htmlString;
	}
	
	private function _isAuthenticated($htmlString, $pattern = '<a href="https://www.animeftw.tv/logout">Logout</a>') {
		
		return ( strpos($htmlString, $pattern) !== false );
		
	}
	
	
}
