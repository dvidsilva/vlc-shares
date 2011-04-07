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
    	$this->view->addSingleAction = X_Env::completeUrl($this->_helper->url->url(array(
    		'controller' => 'bookmarklets',
    		'action' => 'add',
    		'type' => 'single',
    		'id' => '_ID_',
    		'hoster' => '_HOSTER_',
    		'csrf' => $csrf->getHash(),
    	)));
    	
    	$this->view->addAllAction = X_Env::completeUrl($this->_helper->url->url(array(
    		'controller' => 'bookmarklets',
    		'action' => 'add',
    		'type' => 'multiple',
    		'csrf' => $csrf->getHash(),
    	)));

    	$this->view->checkAction = X_Env::completeUrl($this->_helper->url->url(array(
    		'controller' => 'bookmarklets',
    		'action' => 'check',
    		'id' => '_ID_',
    		'hoster' => '_HOSTER_'
    	 )));
    	
    	$this->view->cssUrl = X_Env::completeUrl($this->_helper->viewRenderer->view->baseUrl('/css/bookmarklets.css'));
    	 
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setViewSuffix('pjs');
    	$this->getResponse()->setHeader('Content-Type', 'application/javascript');
    	
    }
    
    public function addAction() {
    	
    	$redirect = $this->getRequest()->getParam("redirect", false);
    	$links = $this->getRequest()->getParam("links", false);
    	$category = $this->getRequest()->getParam("category", "Default");
    	$check = $this->getRequest()->getParam("check", false);
    	
    	
    	if ( $links === false || !is_array($links) || trim($category) == '' ) {
    		throw new Exception(X_Env::_('p_bookmarklets_err_invalidrequest'));
    	}

    	$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
    	
    	$invalid = array();
    	
		while (@ob_end_flush());
		
		echo '<div style="position: fixed; top: 0; left: 0; right: 0; background-color: black; background-color: rgba(255,255,255,0.5);"><h2>'.X_Env::_('p_bookmarklets_waitplease').'</h2></div><br /><br /><br />';
		
    	foreach ( $links as $link ) {
    		
    		echo str_pad(' ', 512);
    		
    		@ob_flush();
    		@flush();
    		
    		// set 30 seconds timeout for each link
    		set_time_limit(30);
    		
    		echo '<pre>';
    		
    		$title = trim(@$link['title']);
    		$enabled = @$link['enabled'];
    		$hoster = @$link['hoster'];
    		$id = @$link['id'];
    		
    		if ( !$enabled ) {
    			// just skip this, is not checked
    			continue;
    		}
    		
    		if ( !$hoster || !$id ) {
    			// hoster and id are required, otherwise get error
    			$link['error'] = X_Env::_('p_bookmarklets_err_invaliddata');
    			$invalid[] = $link;
    			echo X_Env::_('p_bookmarklets_video_notadded', $link['hoster'], $link['id'], $link['title'], $link['error']).PHP_EOL;
    			echo '</pre>';
    			continue;
    		}
    		
    		if ( !$title && !$check ) {
    			$link['error'] = X_Env::_('p_bookmarklets_err_missingtitle');
    			$invalid[] = $link;
    			echo X_Env::_('p_bookmarklets_video_notadded', $link['hoster'], $link['id'], $link['title'], $link['error']).PHP_EOL;
    			echo '</pre>';
    			continue;
    		}
    		
    		if ( !X_VlcShares_Plugins::helpers()->hoster()->isRegisteredHoster($hoster)  ) {
    			// hoster and id are required, otherwise get error
    			$link['error'] = X_Env::_('p_bookmarklets_err_invalidhoster');
    			$invalid[] = $link;
    			echo X_Env::_('p_bookmarklets_video_notadded', $link['hoster'], $link['id'], $link['title'], $link['error']).PHP_EOL;
    			echo '</pre>';
    			continue;
    		}
    		
    		$thumbnail = false;
    		$description = false;
    		
    		if ( $check ) {
    			$hosterHelper = X_VlcShares_Plugins::helpers()->hoster()->getHoster($hoster);
    			try {
    				$infos = $hosterHelper->getPlayableInfos($id, true);
    				if ( !$title ) {
    					$title = @$infos['title'];
    				}
		    		if ( !$title ) {
		    			// even after a request
		    			// there is no video title
		    			$link['error'] = X_Env::_('p_bookmarklets_err_missingtitle_aftercheck');
		    			$invalid[] = $link;
		    			echo X_Env::_('p_bookmarklets_video_notadded', $link['hoster'], $link['id'], $link['title'], $link['error']).PHP_EOL;
		    			echo '</pre>';
		    			continue;
		    		}
		    		
		    		if ( array_key_exists('description', $infos) ) {
		    			$description = $infos['description'];
		    		}
		    		if ( array_key_exists('thumbnail', $infos) ) {
		    			$thumbnail = $infos['thumbnail'];
		    		}
		    		
    			} catch (Exception $e) {
	    			$link['error'] = X_Env::_('p_bookmarklets_err_hostererror', $e->getMessage());
	    			$invalid[] = $link;
	    			echo X_Env::_('p_bookmarklets_video_notadded', $link['hoster'], $link['id'], $link['title'], $link['error']).PHP_EOL;
	    			echo '</pre>';
	    			continue;
    			}
    		}
    		
    		$video = new Application_Model_Video();
    		$video->setHoster($hoster)
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
    			echo X_Env::_('p_bookmarklets_video_added', $video->getHoster(), $video->getIdVideo(), $video->getTitle()).PHP_EOL;
    			echo '</pre>';
    		} catch (Exception $e ) {
    			$link['error'] = X_Env::_('p_bookmarklets_err_dberror', $e->getMessage());
    			$invalid[] = $link;
    			echo X_Env::_('p_bookmarklets_video_notadded', $link['hoster'], $link['id'], $link['title'], $link['error']).PHP_EOL;
    			echo '</pre>';
    			continue;
    		}
    		
    	}
    	
    	echo '<div style="position: fixed; top: 0; left: 0; right: 0; background-color: black;"><h2 style="color: white;">'.X_Env::_('p_bookmarklets_alldone').'</h2></div>';
    	
    }
    
}

