<?php

class BookmarksController extends X_Controller_Action
{
	
	/**
	 * @var X_VlcShares_Plugins_Bookmarks
	 */
	private $pluginBookmarks;
	/**
	 * @var X_VlcShares_Plugins_Bookmarklets
	 */
	private $pluginBookmarklets;
	
    public function init()
    {
        parent::init();
		if ( !X_VlcShares_Plugins::broker()->isRegistered('bookmarks') ) {
			throw new Exception(X_Env::_('err_pluginnotregistered') . ": bookmarks");
		} else {
			$this->pluginLibrary = X_VlcShares_Plugins::broker()->getPlugins('bookmarks');
		}
		if ( X_VlcShares_Plugins::broker()->isRegistered('bookmarklets') ) {
			$this->pluginBookmarklets = X_VlcShares_Plugins::broker()->getPlugins('bookmarklets');
		}
    }

    public function indexAction()
    {
    	
        $bookmarks = Application_Model_BookmarksMapper::i()->fetchAll();
        
        // PAGINATION
        $page = $this->getRequest()->getParam('page', 1);
        $paginatorHelper = $this->pluginLibrary->helpers()->paginator();
        $pages = $paginatorHelper->getPages($bookmarks);
        $bookmarks = $paginatorHelper->getPage($bookmarks, $page);
        $this->view->pages = $pages;
		$this->view->page = $page;
		// END PAGINATION

		$this->view->bookmarks = $bookmarks;
        $this->view->bookmarkletsEnabled = ($this->pluginBookmarklets !== null ); 
        $this->view->messages = $this->_helper->flashMessenger->getMessages();
    }
    
    public function addAction() {
        $request = $this->getRequest();
        $form    = new Application_Form_Bookmark();
		$form->setAction($this->_helper->url('add'));
        
        if ( $this->getRequest()->isPost()) {
            if ($form->isValid($request->getPost())) {

                $bookmark = new Application_Model_Bookmark();
                $id = $request->getParam('id', false);
                if ( $id !== false && !is_null($id) ) {
                	Application_Model_BookmarksMapper::i()->find($id, $bookmark);
                	if ( $bookmark->isNew() || $bookmark->getId() != $id ) {
                		throw new Exception(X_Env::_('p_bookmarks_err_invalidid'));
                	}
                }

				$url = $form->getValue('url');
                $title = $form->getValue('title');
                $description = $form->getValue('description');
                $thumbnail = $form->getValue('thumbnail');
                
                $bookmark->setUrl($url);
                $bookmark->setTitle($title);
                $bookmark->setThumbnail($thumbnail);
                $bookmark->setDescription($description);
                
                Application_Model_BookmarksMapper::i()->save($bookmark);
                
               	//return $this->_helper->redirector('index');
              	$this->_helper->redirector('index', 'bookmarks');
            } 
        }
        $this->view->form = $form;
    }
    
    public function editAction() {
        $request = $this->getRequest();
        $id = $request->getParam('id', null);

        if ( is_null($id) ) {
        	$this->_helper->redirector('index','bookmarks');
        } else {
        	$bookmark = new Application_Model_Bookmark();
        	Application_Model_BookmarksMapper::i()->find($id, $bookmark);
        	if ( !$bookmark->isNew() && $bookmark->getId() == $id ) {
		        $form    = new Application_Form_Bookmark();
		        $form->setAction($this->_helper->url('add'));
				        
		        $form->addElement('hidden', 'id');
		        $form->setDefaults(array(
		        	'id' => $bookmark->getId(),
		        	'title' => $bookmark->getTitle(),
		        	'url' => $bookmark->getUrl(),
		        	'description' => $bookmark->getDescription(),
		        	'thumbnail' => $bookmark->getThumbnail(),
		        ));
		        $this->view->bookmark = $bookmark;
		        $this->view->form = $form;
		        $this->render('add');
        	} else {
				$this->_helper->redirector('index','bookmarks');        		
        	}
        }
    }
    
    public function deleteAction() {
        $request = $this->getRequest();
        $id = $request->getParam('id', null);
		if ( !is_null($id) ) {
			$bookmark = new Application_Model_Bookmark();
			Application_Model_BookmarksMapper::i()->find($id, $bookmark);
			
			if ( !$bookmark->isNew() ) {
				Application_Model_BookmarksMapper::i()->delete($bookmark);
			}
        }
        $this->_helper->redirector('index','bookmarks');
	}
	
}

