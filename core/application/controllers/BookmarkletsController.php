<?php

/**
 * Manage bookmarklets
 */
class BookmarkletsController extends X_Controller_Action
{

    public function init()
    {
        parent::init();
		if ( !X_VlcShares_Plugins::broker()->isRegistered('bookmarklets') ) {
			throw new Exception(X_Env::_('err_pluginnotregistered') . ": bookmarklets");
		}
    }
	
    public function indexAction() {
    	
    	$newBookmarkletLink = X_Env::completeUrl($this->_helper->url->url(array(
    		'controller' => 'bookmarklets',
    		'action' => 'script',
    	)));

    	$regexLink = X_Env::completeUrl($this->_helper->viewRenderer->view->baseUrl('/js/xregexp-min.js'));
    	
    	
		$inlineJs = "javascript:var%20b=document.body;if(b&&!document.xmlVersion){void(z=document.createElement('script'));void(z.src='{$regexLink}');void(b.appendChild(z));void(y=document.createElement('script'));void(y.src='{$newBookmarkletLink}');void(b.appendChild(y));}else{}";
		
		// rimuovo tabulazioni e ritorni a capo
		// in questo modo e' piu leggibile il codice
		$inlineJs = str_replace(array("\n", "\r", "\t"), array("", "", ""), $inlineJs);

		$this->view->inlineJs = $inlineJs;
    }
	

    
    public function scriptAction() {
    	 
    	$hosters = X_VlcShares_Plugins::helpers()->hoster()->getHosters();
    
    	$csrf = new Zend_Form_Element_Hash('csrf', array(
    			'salt' => __CLASS__
    	));
    	$csrf->initCsrfToken();
    	 
    	 
    	$this->view->hosters = $hosters;

    	$this->view->frameUrl = X_Env::completeUrl($this->_helper->url->url(array(
    			'controller' => 'bookmarklets',
    			'action' => 'frame',
    	)));
    	
    	$this->view->cssUrl = X_Env::completeUrl($this->_helper->viewRenderer->view->baseUrl('/css/bookmarklets/injected.css'));
    	$this->view->xfcUrl = X_Env::completeUrl($this->_helper->viewRenderer->view->baseUrl('/js/jquery.xfc.js'));
    
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setViewSuffix('pjs');
    	$this->getResponse()->setHeader('Content-Type', 'application/javascript');
    	 
    }    
    
    public function frameAction() {
    	$this->_helper->layout->disableLayout();
    	$csrf = new Zend_Form_Element_Hash('csrf', array(
    			'salt' => __CLASS__
    	));
    	$csrf->initCsrfToken();
    	
    	$this->view->bookmarks_enabled = X_VlcShares_Plugins::broker()->isRegistered('bookmarks');
    	$this->view->onlinelibrary_enabled = X_VlcShares_Plugins::broker()->isRegistered('onlinelibrary');
    	$this->view->categories = Application_Model_VideosMapper::i()->fetchCategories(); 
    	$this->view->csrf = $csrf->getHash();
    	$this->view->hosters = X_VlcShares_Plugins::helpers()->hoster()->getHosters();
    	
    }
    
    public function resolverAction() {
    	
    	$csrf = new Zend_Form_Element_Hash('csrf', array(
    			'salt' => __CLASS__
    	));
    	
    	$validCheck = $csrf->isValid($this->getRequest()->getParam('csrf', false));
    	
    	$csrf->initCsrfToken();
    	$hash = $csrf->getHash();
    	$return = array(
    		'success' => true,
    		'api' => array( 
    			'resolver' => $this->_helper->url->url(array('controller' => 'bookmarklets', 'action' => 'resolver', 'csrf' => $hash)),
    			'adder' => $this->_helper->url->url(array('controller' => 'bookmarklets', 'action' => 'add', 'csrf' => $hash)),
    			'bookmark' => $this->_helper->url->url(array('controller' => 'bookmarklets', 'action' => 'bookmark', 'csrf' => $hash)),
    		),
    		'links' => array()
    	);
    	
    	if ( $validCheck ) {
    	
	    	$links = $this->getRequest()->getParam('links', array());
	    	$hosterHelper = X_VlcShares_Plugins::helpers()->hoster();
	    	
	    	foreach ($links as $link) {
	    		$index = $link['index'];
	    		$href = $link['href'];
	    		
	    		$response = array('index' => $index, 'valid' => false);
	    		
	    		try {
	    			$hoster = $hosterHelper->findHoster($href);
	    			$infos = $hoster->getPlayableInfos($href, false);
	    			$response['valid'] = true;
	    			$response['label'] = (isset($infos['title']) ? $infos['title'] : '');
	    			$response['description'] = (isset($infos['description']) ? $infos['description'] : '');
	    			$response['thumbnail'] = (isset($infos['thumbnail']) ? $infos['thumbnail'] : '');
	    			$response['extra'] = $infos;
	    			$response['errorMessage'] = "";
	    		} catch (Exception $e) {
	    			// not valid, setting default
	    			$response['errorMessage'] = $e->getMessage();
	    			$response['label'] = "";
	    			$response['description'] = "";
	    			$response['thumbnail'] = "";
	    			$response['extra'] = array();
	    		}
	    		$return['links'][] = $response;
	    	}
	    	
    	} else {
    		X_Debug::e("Invalid CSRF");
    		$return['success'] = false;
    	}
    	
    	$this->_helper->json($return, true, false);
    }
    
    public function addAction() {
    	
    	$csrf = new Zend_Form_Element_Hash('csrf', array(
    			'salt' => __CLASS__
    	));
    	 
    	$validCheck = $csrf->isValid($this->getRequest()->getParam('csrf', false));
    	 
    	$csrf->initCsrfToken();
    	$hash = $csrf->getHash();
    	$return = array(
    			'success' => true,
    			'api' => array(
    					'resolver' => $this->_helper->url->url(array('controller' => 'bookmarklets', 'action' => 'resolver', 'csrf' => $hash)),
    					'adder' => $this->_helper->url->url(array('controller' => 'bookmarklets', 'action' => 'add', 'csrf' => $hash)),
    					'bookmark' => $this->_helper->url->url(array('controller' => 'bookmarklets', 'action' => 'bookmark', 'csrf' => $hash)),
    			),
    			'links' => array()
    	);
    	 
    	if ( $validCheck ) {

    		$links = $this->getRequest()->getParam("links", array());
    		$category = $this->getRequest()->getParam("category", false);
    		
    		$added = 0;
    		$ignored = 0;
    		$total = count($links);
    		
    		if ( $category ) {
    			
    			$hosterHelper = X_VlcShares_Plugins::helpers()->hoster();
    			
    			foreach ($links as $link) {
    				
    				$href = isset($link['href']) ? $link['href'] : false;
    				$title = isset($link['title']) ? $link['title'] : false;
    				$description = isset($link['description']) ? $link['description'] : false;
    				$thumbnail = isset($link['thumbnail']) ? $link['thumbnail'] : false;

    				if ( !$href || !$title ) {
    					$ignored++;
    					continue;
    				}

    				try  {
    				
	    				$hoster = $hosterHelper->findHoster($href);
	    				$id = $hoster->getResourceId($href);
	    				
		    			$video = new Application_Model_Video();
		    			$video->setHoster($hoster->getId())
			    			->setIdVideo($id)
			    			->setTitle($title)
			    			->setCategory($category);
		    			
		    			if ( $thumbnail ) {
		    				$video->setThumbnail($thumbnail);
		    			}
		    			if ( $description ) {
		    				$video->setDescription($description);
		    			}
		    			
		    			try {
		    				Application_Model_VideosMapper::i()->save($video);
		    				$added++;
		    			} catch (Exception $e ) {
		    				$ignored++;
		    			}
    				} catch (Exception $e) {
    					X_Debug::w("No hoster found: {{$e->getMessage()}}");
    					$ignored++;
    				}
    			}
    			
    			$return['links'] = array(
    					'total' => $total,
    					'ignored' => $ignored,
    					'added' => $added
    				);
    			
    		} else {
    			X_Debug::e("No category selected");
    			$return['success'] = false;
    		}
    		
   		} else {
   			X_Debug::e("Invalid CSRF");
   			$return['success'] = false;
   		}
   		 
   		$this->_helper->json($return, true, false);
    		
    }
    
    
    public function bookmarkAction() {
    	 
    	$csrf = new Zend_Form_Element_Hash('csrf', array(
    			'salt' => __CLASS__
    	));
    
    	$validCheck = $csrf->isValid($this->getRequest()->getParam('csrf', false));
    
    	$csrf->initCsrfToken();
    	$hash = $csrf->getHash();
    	$return = array(
    			'success' => true,
    			'api' => array(
    					'resolver' => $this->_helper->url->url(array('controller' => 'bookmarklets', 'action' => 'resolver', 'csrf' => $hash)),
    					'adder' => $this->_helper->url->url(array('controller' => 'bookmarklets', 'action' => 'add', 'csrf' => $hash)),
    					'bookmark' => $this->_helper->url->url(array('controller' => 'bookmarklets', 'action' => 'bookmark', 'csrf' => $hash)),
    			),
    	);
    
    	if ( $validCheck ) {
    
    		$url = $this->getRequest()->getParam("url", false);
    		$title = strip_tags($this->getRequest()->getParam("title", false));
    		$description = strip_tags($this->getRequest()->getParam("description", false));
    		$thumbnail = $this->getRequest()->getParam("thumbnail", false);
    		$ua = $this->getRequest()->getParam("ua", false);
    		$cookies = $this->getRequest()->getParam("cookies", false);
    
    		if ( $url && $title ) {
   
    			$model = new Application_Model_Bookmark();
    			$model->setUrl($url);
    			$model->setTitle($title);
   				 
   				if ( $thumbnail ) {
   					$model->setThumbnail($thumbnail);
   				}
   				if ( $description ) {
   					$model->setDescription($description);
   				}
   				if ( $ua ) {
   					$model->setUa($ua);
   				}
   				if ( $cookies ) {
   					$model->setCookies($cookies);
   				}
   					
   				try {
   					Application_Model_BookmarksMapper::i()->save($model);
   				} catch (Exception $e ) {
	    			X_Debug::e("DB Error: {$e->getMessage()}");
	    			$return['success'] = false;
   				}
    		} else {
    			X_Debug::e("Missing data");
    			$return['success'] = false;
    		}
    
    	} else {
    		X_Debug::e("Invalid CSRF");
    		$return['success'] = false;
    	}
    
    	$this->_helper->json($return, true, false);
    
    }    
    
}

