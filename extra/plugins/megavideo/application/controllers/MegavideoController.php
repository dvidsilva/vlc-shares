<?php

class MegavideoController extends X_Controller_Action
{
	
	/**
	 * @var X_VlcShares_Plugins_Megavideo
	 */
	private $plugin;
	
	/**
	 * @var Zend_Http_CookieJar
	 */
	private $jar = null;
	
    public function init()
    {
        parent::init();
		if ( !X_VlcShares_Plugins::broker()->isRegistered('megavideo') ) {
			/*
			$this->_helper->flashMessenger(X_Env::_('err_pluginnotregistered') . ": youtube");
			$this->_helper->redirector('index', 'manage');
			*/
			throw new Exception(X_Env::_('err_pluginnotregistered') . ": megavideo");
		} else {
			$this->plugin = X_VlcShares_Plugins::broker()->getPlugins('megavideo');
		}
    }

    public function indexAction()
    {
        // action bodys
        $mapper  = Application_Model_MegavideoMapper::i();
        $categories = $mapper->fetchCategories();
        
        foreach ($categories as &$category) {
        	$categoryEntries = $mapper->fetchByCategory($category['category']);
        	$category['entries'] = $categoryEntries;
        }
        

		
		$newBookmarkletLink = X_Env::routeLink('megavideo', 'bmscript'); 
		// nuova versione
		$inlineJs = <<<NEWINLINE
		javascript:var%20b=document.body;if(b&&!document.xmlVersion){void(z=document.createElement('script'));void(z.src='{$newBookmarkletLink}');void(b.appendChild(z));}else{}";
NEWINLINE;
		
		// rimuovo tabulazioni e ritorni a capo
		// in questo modo e' piu leggibile il codice
		$inlineJs = str_replace(array("\n", "\r", "\t"), array("", "", ""),$inlineJs);
        
        $this->view->categories = $categories;
        $this->view->bookmarkletsEnabled = true; 
        $this->view->inlineJs = $inlineJs;
        
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }

    public function bmscriptAction() {
    	
        $linkAll = X_Env::routeLink('megavideo', 'bookmarklets',array('type' => 'list'));
        $linkSingle = X_Env::routeLink('megavideo', 'bookmarklets',array('type' => 'video'));
        $linkSingleCategoryRequest = X_Env::_('megavideo_bookmarklets_category_selection');
        $paginationRequest = X_Env::_('megavideo_bookmarklets_lotoflinks');
        $allpagesearchRequest = X_Env::_('megavideo_bookmarklets_nolinksinselectioncontinue');
        $statusWindowLink = X_Env::routeLink('megavideo', 'status');
    	
    	
    	$this->getResponse()->setHeader('Content-Type', 'text/javascript');
    	$this->_helper->layout()->disableLayout();
    	
    	$this->view->linkAll = $linkAll;
    	$this->view->linkSingle = $linkSingle;
    	$this->view->linkSingleCategoryRequest = $linkSingleCategoryRequest;
    	$this->view->paginationRequest = $paginationRequest;
    	$this->view->allpagesearchRequest = $allpagesearchRequest;
    	$this->view->statusWindowLink = $statusWindowLink;
    	
    }
    
    public function addAction() {
        $request = $this->getRequest();
        $form    = new Application_Form_Megavideo();
		$form->setAction($this->_helper->url('add'));
		$isAjax = $request->getParam('isAjax', false);
        
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {

            	$idVideo = $form->getValue('idVideo');
				@list($idVideo, $type) = explode('_', $idVideo);
				if ( $type == 'd' ) {
					// this is a megaupload->megavideo file. I need to find the real id
					$idVideo = $this->_getRealMegavideoId($idVideo);
				}
            	
                $video = new Application_Model_Megavideo($form->getValues());
                $video->setIdVideo($idVideo);
                // Nel caso in cui sia un edit request
                if ( !is_null($request->getParam('id', null)) ) {
                	$video->setId($request->getParam('id', null));
                }
                $mapper  = Application_Model_MegavideoMapper::i();
                $mapper->save($video);
                if ( $isAjax) {
                	header('Content-Type:text/plain');
                	echo '1';
                	$this->_helper->viewRenderer->setNoRender(true);
                	$this->_helper->layout->disableLayout();
                	return;
                } else {
                	//return $this->_helper->redirector('index');
                	$this->_helper->redirector('category', 'megavideo', 'default', array(
                		'id' => urlencode($video->getCategory())
                	));
                }
            }
        }
 
        if ( $isAjax ) {
        	header('Content-Type:text/plain');
        	echo '0';
        	$this->_helper->viewRenderer->setNoRender(true);
        	$this->_helper->layout->disableLayout();
        } else {
        	$this->view->form = $form;
        }
    }
    
    public function modifyAction() {
        $request = $this->getRequest();
        $id = $request->getParam('id', null);

        if ( is_null($id) ) {
        	$this->_helper->redirector('index','megavideo');
        } else {
        	
        	$mapper  = Application_Model_MegavideoMapper::i();
        	$megavideo = new Application_Model_Megavideo();
        	$mapper->find($id, $megavideo);
        	if ( $megavideo->getId() == $id ) {
		        $form    = new Application_Form_Megavideo();
		        $form->setAction($this->_helper->url('add'));
		        $form->addElement('hidden', 'id');
		        $form->populate(array(
		        	'id' => $megavideo->getId(),
		        	'label' => $megavideo->getLabel(),
		        	'idVideo' => $megavideo->getIdVideo(),
		        	'category' => $megavideo->getCategory(),
		        	'description' => $megavideo->getDescription()
		        ));
		        $this->view->form = $form;
		        $this->render('add');
        	} else {
				$this->_helper->redirector('index','megavideo');        		
        	}
        }
    }
    
    public function categoryAction() {
    	
    	/* @var $request Zend_Controller_Request_Http */
    	$request = $this->getRequest();
    	$categoryName = $request->getParam('id', '');
    	$categoryName = urldecode($categoryName);
    	$videos = array();
    	if ( $categoryName != '') {
    		$mapper  = Application_Model_MegavideoMapper::i();
    		$videos = $mapper->fetchByCategory($categoryName);
    		if ( count($videos) == 0 ) {
    			$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_megavideo_invalidcategory')));
    			$this->_helper->redirector('index');
    		}
    	}
    	$this->view->category = $categoryName;
    	$this->view->videos = $videos;
    	
    	// if it's an ajax request, no layout is needed
    	if ( $request->isXmlHttpRequest() ) {
    		$this->_helper->layout()->disableLayout();
    	}
    	
    }
    
    public function renameAction() {
    	$request = $this->getRequest();
    	$categoryName = $request->getParam('id', '');
    	$newName = $request->getParam('name', 'Default');
    	if ( $categoryName != '') {
    		$mapper  = Application_Model_MegavideoMapper::i();
    		$mapper->renameCategory($categoryName, $newName);
    	}
    	$this->_helper->redirector('index','megavideo');
    }
    
    public function deleteAction() {
        $request = $this->getRequest();
        $id = $request->getParam('id', null);
        $type = $request->getParam('type', 'video');
		if ( !is_null($id) ) {
			$mapper  = Application_Model_MegavideoMapper::i();
			if ( $type == 'video' ) {
				
				$video = new Application_Model_Megavideo();
				$mapper->find($id, $video);
				
				if ( $video->getId() ) {
					$category = $video->getCategory();
					$mapper->delete($id);
                	$this->_helper->redirector('category', 'megavideo', 'default', array(
                		'id' => urlencode($category)
                	));
				}
				
			} elseif ($type == 'category' ) {
				$mapper->deleteCategory(urldecode($id));
			}
        }
        $this->_helper->redirector('index','megavideo');
	}
	
	public function bookmarkletsAction() {
		$request = $this->getRequest();
		$type = $request->getParam('type', '');
		$category = $request->getParam('category', 'Default');
		$link = $request->getParam('link', '');
		$links = $request->getParam('links', '');
		$links = explode('|', substr($links,-1) == '|' ? substr($links,0,-1) : $links );
		
		if ($type == 'video' && $link != '' ) {
			@list($link, $type) = explode('_', $link);
			if ( $type == 'd' ) {
				// this is a megaupload->megavideo file. I need to find the real id
				$link = $this->_getRealMegavideoId($link);
			}
			$video = new X_Megavideo($link);
			if ( $video->get('SERVER') ) {
				$this->view->confirm = true;
				$this->view->videoTitle = urldecode($video->get('TITLE'));
				$this->view->videoDescription = urldecode($video->get('DESCRIPTION'));
				$this->view->category = $category;
				$this->view->link = $link;
				$this->view->showCategoryPrompt = false;
			} else {
				$this->view->message = X_Env::_('megavideo_manager_bookmarklets_error_novalidvideo');
				$this->render('error');
			}
		} elseif ($type == 'list' ) {
			if ( count($links) > 0 ) {
				$this->view->showList = true;
				$this->view->links = $links;
				$this->view->showCategoryPrompt = true;
			} else {
				$this->view->message = X_Env::_('megavideo_manager_bookmarklets_error_nolinks');
				$this->render('error');
			}
		} else {
			$this->view->message = X_Env::_('megavideo_manager_bookmarklets_error_unknownbookmarklets');
			$this->render('error');
		}
	}
	
	public function infoAction() {
		$request = $this->getRequest();
		$oldId = $id = $request->getParam('idVideo');
		
		@list($id, $type) = @explode('_', $id);
		if ( $type == 'd' ) {
			// this is a megaupload->megavideo file. I need to find the real id
			$id = $this->_getRealMegavideoId($id);
		}
		
		if ( $id != null ) {
			$megavideo = new X_Megavideo($id);
		}
		if ( $id != null && $megavideo->get('SERVER') ) {
			$title = urldecode($megavideo->get('TITLE'));
			$description = urldecode($megavideo->get('DESCRIPTION'));
			header('Content-Type:application/json');
			echo Zend_Json::encode(array('id' => $id, 'oldId' => $oldId, 'title' => $title, 'description' => $description, 'isError' => false));
		} else {
			header('Content-Type:application/json');
			echo Zend_Json::encode(array('id' => $id, 'oldId' => $oldId, 'title' => X_Env::_('megavideo_manage_title_error'), 'description' => X_Env::_('megavideo_manager_bookmarklets_error_novalidvideo'), 'isError' => true));
		}
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	}
	
	// solo per caricare la pagina di stato
	public function statusAction() {
		$this->_helper->layout->disableLayout();
	}
	
	private function _getRealMegavideoId($megauploadId) {
		
		$http = new Zend_Http_Client("http://www.megavideo.com/?d=$megauploadId");
		$response = $http->request();
		
		$body = $response->getBody();
		$matches = array();
		if ( preg_match('/flashvars\.v \= \"([^\"]*)\";/', $body, $matches) ) {
			return $matches[1];
		} else {
			return null;	
		}
	}
	
	public function premiumAction() {
		
		// time to get params from get
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		
		if ( !$this->plugin->config('premium.enabled', true) || $this->plugin->config('premium.username', '') == '' || $this->plugin->config('premium.password', '') == '' ) {
			throw new Exception(X_Env::_('p_megavideo_err_premiumdisabled'));
		}
		
		X_Debug::i('Premium account support enabled');
		
		$videoId = $request->getParam('v', false); // video file url
		$qualityType = $request->getParam('q', X_VlcShares_Plugins_Helper_Megavideo::QUALITY_NORMAL); // video file url
		
		if ( $videoId === false ) {
			// invalid request
			throw new Exception(X_Env::_('p_megavideo_err_invalidrequest'));
			return;
		}
		
		X_Debug::i("Video: $videoId");
		
		// i check for NOPREMIUM quality: i don't need authentication in NOPREMIUM mode 
		if ( $qualityType != X_VlcShares_Plugins_Helper_Megavideo::QUALITY_NOPREMIUM ) {
		
			X_Debug::i('Premium features enabled');
			
			$http = new Zend_Http_Client('http://localhost/', array(
				'maxredirects'	=>  10,
				'timeout'		=>  10,
				'keepalive' 	=> true
			));
			
			
			$http->setHeaders(array(
				'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1',
				'Accept-Language:it-IT,it;q=0.8,en-US;q=0.6,en;q=0.4'
			));
			
			
			$jarFile = APPLICATION_PATH . '/../data/megavideo/cookie.jar';
			$ns = new Zend_Session_Namespace(__CLASS__);
			
			if ( $this->jar == null ) {
				if ( false && isset($ns->jar) && $ns->jar instanceof Zend_Http_CookieJar ) {
					$this->jar = $ns->jar;
					X_Debug::i('Loading stored authentication in Session');
				} elseif ( file_exists($jarFile) ) {
					$this->jar = new Zend_Http_CookieJar();
					$cookies = unserialize(file_get_contents($jarFile));
					foreach ($cookies as $c) {
						$_c = new Zend_Http_Cookie($c['name'], $c['value'], $c['domain'], $c['exp'], $c['path']);
						$this->jar->addCookie($_c);
					}
					X_Debug::i('Loading stored authentication in File');
				} else {
					$this->jar = new Zend_Http_CookieJar();
					//$this->jar->addCookie(new Zend_Http_Cookie('l', 'it', 'http://www.megavideo.com'));
				}
			}
			$http->setCookieJar($this->jar);
			
			$userId = false;
			
			if ( $http->getCookieJar() != null ) {
				//X_Debug::i(var_export($http->getCookieJar()->getAllCookies(Zend_Http_CookieJar::COOKIE_STRING_ARRAY), true));
				//$userId = $http->getCookieJar()->getCookie($cookieUri, 'user', Zend_Http_CookieJar::COOKIE_STRING_ARRAY);
				$userId = $this->_getMatchCookieValue('user', 'http://www.megavideo.com/', $http->getCookieJar());
				X_Debug::i("First check for userId: $userId");
			}
			
			if ( $userId == false ) {
				
				X_Debug::i("No valid userId found in Cookies");
				
				$this->_authenticateHttp($http, $this->plugin->config('premium.username', ''), $this->plugin->config('premium.password', ''));
	
				//X_Debug::i(var_export($http->getCookieJar()->getAllCookies(Zend_Http_CookieJar::COOKIE_STRING_ARRAY), true));
				
				//$userId = $http->getCookieJar()->getCookie($cookieUri, 'user', Zend_Http_CookieJar::COOKIE_STRING_ARRAY);
				$userId = $this->_getMatchCookieValue('user', 'http://www.megavideo.com/', $http->getCookieJar());
				
				if ( $userId == false ) {
					X_Debug::f("Invalid account given");
					throw new Exception(X_Env::_('p_megavideo_invalidaccount'));
				}
		
			}
			
			X_Debug::i("UserId in cookies: $userId");
			
			$uri = "http://www.megavideo.com/xml/player_login.php?u=$userId&v=$videoId";
			
			$http->setUri($uri);
			
			$response = $http->request();
			$htmlString = $response->getBody();
			
			if ( strpos($htmlString, 'type="premium"' ) === false ) {
				
				X_Debug::w("Account isn't premium or not authenticated");
				X_Debug::i(var_export($htmlString));
				
				// invalid cookies
				// need to re-authenticate
				$this->_authenticateHttp($http, $this->plugin->config('premium.username', ''), $this->plugin->config('premium.password', ''));
				
				$response = $http->request();
				
				$htmlString = $response->getBody();
				
				if ( strpos($htmlString, 'type="premium"' ) === false ) {
					X_Debug::f("Invalid premium account");
					X_Debug::i(var_export($htmlString));
					throw new Exception(X_Env::_('p_megavideo_invalidpremiumaccount'));
				}
			}
			
			// time to store the cookie
			
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
			
			
			// in htmlString we should have an xml like this one:
			/*
				<?xml version="1.0" encoding="UTF-8"?> 
				<user type="premium" user="XXXXX" downloadurl="http%3A%2F%2Fwww444.megavideo.com%2Ffiles%2Fd9ab7ef6313e55ab26240f2aac9dd74f%2FAmerican.Dad.-.1AJN08.-.Tutto.su.Steve.%28All.About.Steve%29.-.DVDMuX.BY.Pi3TRo.%26amp%3B.yodonvito.avi" />		
			*/
	
			
			// i create context here so i can use the same context 
			// for normal link quality video
			$cookies = $http->getCookieJar()->getAllCookies(Zend_Http_CookieJar::COOKIE_STRING_CONCAT);
			$opts = array('http' =>
				array(
					'header'  => array(
						//"Referer: $refererUrl",
						"Cookie: $cookies"
					)
				)
			);
		} else {

			X_Debug::i('Premium features NOT enabled');
			// if quality == NOPREMIUM i don't need authentication or context
			
			// no context needed
			$opts = array('http' =>
				array(
				)
			);
			
		}
		
		
		$context  = stream_context_create($opts);
		
		$videoUrl = null;
	
		switch ($qualityType) {
			
			case X_VlcShares_Plugins_Helper_Megavideo::QUALITY_NOPREMIUM:
				X_Debug::w("Premium proxy feature, but NOPREMIUM quality? O_o");
				$megavideo = new X_Megavideo($videoId);
				$videoUrl = $megavideo->get('URL');
				break;
				
			case X_VlcShares_Plugins_Helper_Megavideo::QUALITY_FULL:
				X_Debug::i("FULL quality selected");
				if ( preg_match('/ downloadurl=\"([^\"]*)\" /', $htmlString, $match) ) {
					// match[1] is the video link
					$videoUrl = urldecode(@$match[1]);
					// i break the case because 1 have a match
					break;
				} else {
					// no videoURL, fallback to normal
					X_Debug::e('No download url, fallback to NORMAL quality');
					//X_Debug::i($htmlString);
				}
				
			case X_VlcShares_Plugins_Helper_Megavideo::QUALITY_NORMAL:
			default:
				$megavideo = new X_Megavideo($videoId, $context, $userId);
				$videoUrl = $megavideo->get('URL');
				
		}
		
		X_Debug::i("VideoURL: $videoUrl");
		
		// this action is so special.... no layout or viewRenderer
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
		
		
		// if user abort request (vlc/wii stop playing), this process ends
		ignore_user_abort(false);
		
		// close and clean the output buffer, everything will be read and send to device
		ob_end_clean();
		
		header("Content-Type: video/mp4");
		
		// readfile open a file and send it directly to output buffer
		readfile($videoUrl, false, $context);
		
	}

	private function _authenticateHttp(Zend_Http_Client $http, $username, $password) {

		X_Debug::i("Authenticating HTTP Client");
		
		$oldUri = $http->getUri(true);
		
		$loginUri = "http://www.megavideo.com/?s=account";
		$http->setUri($loginUri);
		
		$response = $http->request();
		
		//X_Debug::i("Login response1: {$response->getHeadersAsString(true)}");
		
		$http->setMethod(Zend_Http_Client::POST)
			->setParameterPost(array(
				'username' => /*urlencode*/($username),
				'password' => /*urlencode*/($password),
				'login' => '1',
			));
			
		$response = $http->request(Zend_Http_Client::POST);
		
		//X_Debug::i("Login response2: {$response->getHeadersAsString(true)}");
		
		$http->setUri($oldUri)->setMethod(Zend_Http_Client::GET);
		
		return $response;
		
	}
	
	private function _getMatchCookieValue($name, $domain, Zend_Http_CookieJar $jar) {
		
		$cookies = $jar->getMatchingCookies($domain);
		
		foreach ($cookies as $cookie) {
			/* @var $cookie Zend_Http_Cookie */
			if ( $cookie->getName() == $name ) {
				return $cookie->getValue();
			}
		}
		
		return false;
		
	}
	
}

