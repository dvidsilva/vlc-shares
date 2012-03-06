<?php

class OnlinelibraryController extends X_Controller_Action
{
	
	/**
	 * @var X_VlcShares_Plugins_OnlineLibrary
	 */
	private $pluginLibrary;
	/**
	 * @var X_VlcShares_Plugins_Bookmarklets
	 */
	private $pluginBookmarklets;
	
    public function init()
    {
        parent::init();
		if ( !X_VlcShares_Plugins::broker()->isRegistered('onlinelibrary') ) {
			throw new Exception(X_Env::_('err_pluginnotregistered') . ": onlinelibrary");
		} else {
			$this->pluginLibrary = X_VlcShares_Plugins::broker()->getPlugins('onlinelibrary');
		}
		if ( X_VlcShares_Plugins::broker()->isRegistered('bookmarklets') ) {
			$this->pluginBookmarklets = X_VlcShares_Plugins::broker()->getPlugins('bookmarklets');
		}
    }

    public function indexAction()
    {
    	
        $categories = Application_Model_VideosMapper::i()->fetchCategories();
        
        // PAGINATION
        $page = $this->getRequest()->getParam('page', 1);
        $paginatorHelper = $this->pluginLibrary->helpers()->paginator();
        $pages = $paginatorHelper->getPages($categories);
        $categories = $paginatorHelper->getPage($categories, $page);
        $this->view->pages = $pages;
		$this->view->page = $page;
		// END PAGINATION
		
		$this->view->rtmpEnabled = X_VlcShares_Plugins::helpers()->rtmpdump()->isEnabled();
		
        $this->view->hosters = array_merge(array('direct-url' => '*NONE*'), X_VlcShares_Plugins::helpers()->hoster()->getHosters());
        $this->view->categories = $categories;
        $this->view->bookmarkletsEnabled = ($this->pluginBookmarklets !== null ); 
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }
    
    public function addrtmpAction() {
    	
        $form    = new Application_Form_VideoRtmp();
		$form->setAction($this->_helper->url('savertmp'));
		
		$this->view->form = $form;
    	
    }
    
    public function savertmpAction() {

        $form    = new Application_Form_VideoRtmp();
		$form->setAction($this->_helper->url('savertmp'));
		
    	if (  $form->isValid($this->getRequest()->getPost()) ) {
    		
    		$values = $form->getValues();
    		$params = array();
    		foreach ($values as $key => $value) {
    			if ( $value == 'true') {
    				$params[$key] = true;
    			} elseif ( $value != '' ) {
    				$params[$key] = $value;
    			}
    		}
    		
    		$url = X_RtmpDump::buildUri($params);
    		
    		$this->_forward('add', 'onlinelibrary', 'default', array(
    			'rtmp' => $url
    		));
    		
    	} else {
			$this->view->form = $form;
    	}    	
    	
    }
    
    public function addAction() {
        $request = $this->getRequest();
        $form    = new Application_Form_Video();
		$form->setAction($this->_helper->url('add'));
		
		$rtmp = $this->getRequest()->getParam('rtmp', false);
		
		$category = $this->getRequest()->getParam('category', false);
		if ( $category ) $category = X_Env::decode($category);
		
		
		try {
			$_hosters = X_VlcShares_Plugins::helpers()->hoster()->getHosters();
			if ( $rtmp === false ) {
				$hosters = array('auto' => X_Env::_('p_onlinelibrary_hosterauto'), 'direct-url' => 'direct-url');
				foreach ($_hosters as $idHoster => $pattern) {
					$hosters[$idHoster] = $idHoster;
				}
			} else {
				$hosters = array('direct-url' => 'direct-url (rtmp)');
				$form->idVideo->setValue($rtmp);
			}
			$form->hoster->setMultiOptions($hosters);
			if ( $category ) {
				$form->setDefault('category', $category);
			}
		} catch (Exception $e) {}
		
		$isAjax = $request->getParam('isAjax', false);
        
        if ( $rtmp === false && $this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {

                $video = new Application_Model_Video();
                $id = $request->getParam('id', false);
                if ( $id !== false && !is_null($id) ) {
                	Application_Model_VideosMapper::i()->find($id, $video);
                	if ( $video->getId() != $id ) {
                		throw new Exception(X_Env::_('p_onlinelibrary_err_invalidid'));
                	}
                }

                
				$idVideo = $form->getValue('idVideo');
                $check = $form->getValue('check');
                $hoster = $form->getValue('hoster');
                $title = $form->getValue('title');
                $description = $form->getValue('description');
                $thumbnail = $form->getValue('thumbnail');
                $category = $form->getValue('category');
                
                // direct url is a special hoster
                // it's not inside the hoster helper and can be handler only
                // with manual insertion
                if ( $hoster !== 'direct-url' ) {
	               	try {
	               		if ( $hoster == 'auto' ) {
	               			$hosterObj = X_VlcShares_Plugins::helpers()->hoster()->findHoster($idVideo);
	               			$hoster = $hosterObj->getId();
	               		} else {
	               			$hosterObj = X_VlcShares_Plugins::helpers()->hoster()->getHoster($hoster);
	               		}
	               	} catch (Exception $e) {
	               		$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_onlinelibrary_invalidhoster', $hoster) ));
	               		$this->_helper->redirector('index', 'onlinelibrary');
	               		return;
	               	}
	               	try {
	               		$resourceId = $hosterObj->getResourceId($idVideo);
	               	} catch ( Exception $e ) {
	               		// if hoster can't strip video id from $idVideo, it means that $idVideo is
	               		// an id already
	               		$resourceId = $idVideo;
	               	}
	               	
	               	// check will be performed if title == null
	                if ( $check || trim($title) == '' ) {
	                	try {
	                		$infos = $hosterObj->getPlayableInfos($resourceId);
	                	} catch (Exception $e) {
		               		$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_onlinelibrary_invalidvideo', $e->getMessage()) ));
		               		$this->_helper->redirector('index', 'onlinelibrary');
		               		return;
	                	}
	                	
		                if ( trim($thumbnail) == "" && array_key_exists('thumbnail', $infos)  ) {
		                	$thumbnail = $infos['thumbnail'];
		                }
		                if ( trim($description) == "" && array_key_exists('description', $infos)  ) {
		                	$description = $infos['description'];
		                }
		                if ( trim($title) == "" && array_key_exists('title', $infos)  ) {
		                	$title = $infos['title'];
		                }
	                }
                } else {
                	$resourceId = $idVideo;
                }
				// title must be setted or fetched from internet
                if ( trim($title) == '' ) {
               		$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_onlinelibrary_invalidtitle') ));
               		$this->_helper->redirector('index', 'onlinelibrary');
               		return;
                }
                
                $video->setIdVideo($resourceId)
                	->setCategory($category)
                	->setTitle($title)
                	->setHoster($hoster)
                	->setThumbnail($thumbnail)
                	->setDescription($description);
                
                Application_Model_VideosMapper::i()->save($video);
                
                if ( $isAjax) {
                	header('Content-Type:text/plain');
                	echo '1';
                	$this->_helper->viewRenderer->setNoRender(true);
                	$this->_helper->layout->disableLayout();
                	return;
                } else {
                	//return $this->_helper->redirector('index');
                	$this->_helper->redirector('category', 'onlinelibrary', 'default', array(
                		'id' => X_Env::encode($video->getCategory())
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
        	$this->_helper->redirector('index','onlinelibrary');
        } else {
        	
        	$video = new Application_Model_Video();
        	Application_Model_VideosMapper::i()->find($id, $video);
        	if ( $video->getId() == $id ) {
		        $form    = new Application_Form_Video();
		        $form->setAction($this->_helper->url('add'));
				try {
					$_hosters = X_VlcShares_Plugins::helpers()->hoster()->getHosters();
					$hosters = array('direct-url' => 'direct-url');
					foreach ($_hosters as $idHoster => $pattern) {
						$hosters[$idHoster] = $idHoster;
					}
					$form->hoster->setMultiOptions($hosters);
				} catch (Exception $e) {}
				        
		        $form->addElement('hidden', 'id');
		        $form->setDefaults(array(
		        	'id' => $video->getId(),
		        	'title' => $video->getTitle(),
		        	'idVideo' => $video->getIdVideo(),
		        	'hoster' => $video->getHoster(),
		        	'category' => $video->getCategory(),
		        	'description' => $video->getDescription(),
		        	'thumbnail' => $video->getThumbnail(),
		        ));
		        $this->view->video = $video;
		        $this->view->form = $form;
		        $this->render('add');
        	} else {
				$this->_helper->redirector('index','onlinelibrary');        		
        	}
        }
    }
    
    public function categoryAction() {
    	
    	/* @var $request Zend_Controller_Request_Http */
    	$request = $this->getRequest();
    	$categoryName = $request->getParam('id', '');
    	$categoryName = X_Env::decode($categoryName);
    	$videos = array();
    	if ( $categoryName != '') {
    		$videos = Application_Model_VideosMapper::i()->fetchByCategory($categoryName);
    		if ( count($videos) == 0 ) {
    			$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_onlinelibrary_invalidcategory')));
    			$this->_helper->redirector('index');
    		}
    	}
    	
    	
        // PAGINATION
        $page = $this->getRequest()->getParam('page', 1);
        $paginatorHelper = $this->pluginLibrary->helpers()->paginator();
        $pages = $paginatorHelper->getPages($videos);
        $videos = $paginatorHelper->getPage($videos, $page);
        $this->view->pages = $pages;
		$this->view->page = $page;
		// END PAGINATION
    	
    	
    	
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
    	$categoryName = X_Env::decode($categoryName);
    	$newName = $request->getParam('name', 'Default');
    	if ( $categoryName != '') {
    		Application_Model_VideosMapper::i()->renameCategory($categoryName, $newName);
    	}
    	$this->_helper->redirector('index','onlinelibrary');
    }
    
    public function deleteAction() {
        $request = $this->getRequest();
        $id = $request->getParam('id', null);
        $type = $request->getParam('type', 'video');
		if ( !is_null($id) ) {
			if ( $type == 'video' ) {
				
				$video = new Application_Model_Video();
				Application_Model_VideosMapper::i()->find($id, $video);
				
				if ( $video->getId() ) {
					$category = $video->getCategory();
					Application_Model_VideosMapper::i()->delete($video);
                	$this->_helper->redirector('category', 'onlinelibrary', 'default', array(
                		'id' => X_Env::encode($category)
                	));
				}
				
			} elseif ($type == 'category' ) {
				Application_Model_VideosMapper::i()->deleteCategory(X_Env::decode($id));
			}
        }
        $this->_helper->redirector('index','onlinelibrary');
	}
	
}

