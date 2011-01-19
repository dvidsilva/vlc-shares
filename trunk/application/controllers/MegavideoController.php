<?php

require_once 'X/Controller/Action.php';
require_once 'X/VlcShares.php';
require_once 'X/Env.php';
require_once 'Zend/Http/CookieJar.php';
require_once 'Zend/Http/Cookie.php';


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
        
        $linkAll = X_Env::routeLink('megavideo', 'bookmarklets',array('type' => 'list'));
        $linkSingle = X_Env::routeLink('megavideo', 'bookmarklets',array('type' => 'video'));
        $linkSingleCategoryRequest = X_Env::_('megavideo_bookmarklets_category_selection');
        $paginationRequest = X_Env::_('megavideo_bookmarklets_lotoflinks');
        $allpagesearchRequest = X_Env::_('megavideo_bookmarklets_nolinksinselectioncontinue');
        $statusWindowLink = X_Env::routeLink('megavideo', 'status');
		$inlineJs = <<<INLINE
javascript:(function() {
	var url = location.toString();
	var reg = /(http:\/\/){0,1}(www\.){0,1}(megavideo\.com(.*)\/\?(v|d)=(.){8,8})/;
	var z,i,j;
	var collector = '';
	var oldTitle;
	var noLinksContinue = true;
	var direct = true;
	function urlencode(str) {
		return escape(str).replace(/\+/g,'%2B').replace(/%20/g, '+').replace(/\*/g, '%2A').replace(/\//g, '%2F').replace(/@/g, '%40');
	}
	function linkIsSafe(u) { 
		return (u.search(reg) != -1);
    }
	if (url.search(reg) != -1) {
		category = prompt(&quot;{$linkSingleCategoryRequest}&quot;,&quot;Default&quot;);
		if (category.trim() != &quot;&quot; ) {
			var code = url.match(/(v|d)=(.){8,8}$/)[0].substr(2,8);
			window.open().location = '{$linkSingle}' + '/category/'+urlencode(category)+'/link/'+code;
		}
	} else {
    	z = document.links;
		oldTitle = this.window.document.title;
		/* cerco solo nella selezione */ 
		if (window.getSelection &amp;&amp; window.getSelection().containsNode &amp;&amp; window.getSelection().toString() != &quot;&quot; ) {
			direct = false;
			for(i = 0; i &lt; z.length; ++i ) { 
				this.window.document.title = &quot;Scanning: &quot;+(((i/z.length*100) + '').substr(0,4)) + &quot;%&quot;;
				if (window.getSelection().containsNode(z[i], true) &amp;&amp; linkIsSafe(z[i].href)) {
					collector += z[i].href.match(/(v|d)=(.){8,8}$/)[0].substr(2,8) + &quot;|&quot;;
					noLinksContinue = false;
				}
			}
		}
		if ( direct || (noLinksContinue &amp;&amp; confirm(&quot;{$allpagesearchRequest}&quot;) )) {
			/* scansione normale */
			for (i = 0, j = 0; i &lt; z.length; ++i) {
				this.window.document.title = &quot;Scanning: &quot;+(((i/z.length*100) + '').substr(0,4)) + &quot;%&quot;;
				try {
					if ( z[i].href.search(reg) != -1 ) {
						collector += z[i].href.match(/(v|d)=(.){8,8}$/)[0].substr(2,8) + &quot;|&quot;;
						j++;
					}
				} catch (e) {}
				if ( j == 50 ) {
					if ( confirm(&quot;{$paginationRequest}&quot;.replace('{%percent%}', (((i/z.length*100) + '').substr(0,4)) + &quot;%&quot; ))) {
						window.open().location = '{$linkAll}' + '/links/'+urlencode(collector);
						collector = &quot;&quot;;
						j = 0;
					}
				}
			}
		}
		this.window.document.title = oldTitle;
		if ( collector != &quot;&quot; ) {
			window.open().location = '{$linkAll}' + '/links/'+urlencode(collector);
		}
	}
})();
INLINE;
		
		// rimuovo tabulazioni e ritorni a capo
		// in questo modo e' piu leggibile il codice
		$inlineJs = str_replace(array("\n", "\r", "\t"), array("", "", ""),$inlineJs);
        
        $this->view->categories = $categories;
        $this->view->bookmarkletsEnabled = true; 
        $this->view->inlineJs = $inlineJs;
    }

    public function addAction() {
        $request = $this->getRequest();
        $form    = new Application_Form_Megavideo();
		$form->setAction($this->_helper->url('add'));
		$isAjax = $request->getParam('isAjax', false);
        
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {
                $video = new Application_Model_Megavideo($form->getValues());
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
                	return $this->_helper->redirector('index');
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
				$mapper->delete($id);
			} elseif ($type == 'category' ) {
				$mapper->deleteCategory($id);
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
			$video = new Megavideo($link);
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
		$id = $request->getParam('idVideo');
		
		$megavideo = new Megavideo($id);
		if ( $megavideo->get('SERVER') ) {
			$title = urldecode($megavideo->get('TITLE'));
			$description = urldecode($megavideo->get('DESCRIPTION'));
			header('Content-Type:application/json');
			echo Zend_Json::encode(array('id' => $id,'title' => $title, 'description' => $description, 'isError' => false));
		} else {
			header('Content-Type:application/json');
			echo Zend_Json::encode(array('id' => $id, 'title' => X_Env::_('megavideo_manage_title_error'), 'description' => X_Env::_('megavideo_manager_bookmarklets_error_novalidvideo'), 'isError' => true));
		}
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	}
	
	// solo per caricare la pagina di stato
	public function statusAction() {
		$this->_helper->layout->disableLayout();
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
		
		if ( $videoId === false ) {
			// invalid request
			throw new Exception(X_Env::_('p_megavideo_err_invalidrequest'));
			return;
		}
		
		X_Debug::i("Video: $videoId");
		
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
		
		if ( !preg_match('/ downloadurl=\"([^\"]*)\" /', $htmlString, $match) ) {
			X_Debug::e('No download url');
			X_Debug::i($htmlString);
			throw new Exception(X_Env::_('p_megavideo_invalidserverresponse'));
		}
		
		// match[1] is the video link
		
		$videoUrl = urldecode($match[1]);
		
		X_Debug::i("VideoURL: $videoUrl");
		
		// this action is so special.... no layout or viewRenderer
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
		
		
		// if user abort request (vlc/wii stop playing), this process ends
		ignore_user_abort(false);
		
		// close and clean the output buffer, everything will be read and send to device
		ob_end_clean();
		
		//$userAgent = $this->plugin->config('hide.useragent', true) ? 'User-Agent: vlc-shares/'.X_VlcShares::VERSION : 'User-Agent: Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20101019 Firefox/4.0.1'; 
		
		$cookies = $http->getCookieJar()->getAllCookies(Zend_Http_CookieJar::COOKIE_STRING_CONCAT);
		
		$opts = array('http' =>
			array(
				'header'  => array(
					//"Referer: $refererUrl",
					"Cookie: $cookies"
				)
			)
		);

		$context  = stream_context_create($opts);
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
				'username' => urlencode($username),
				'password' => urlencode($password),
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

