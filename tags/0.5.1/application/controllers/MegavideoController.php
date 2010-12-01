<?php

require_once 'X/Controller/Action.php';
require_once 'X/VlcShares.php';
require_once 'X/Env.php';

class MegavideoController extends X_Controller_Action
{

    public function init()
    {
        parent::init();
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

}

