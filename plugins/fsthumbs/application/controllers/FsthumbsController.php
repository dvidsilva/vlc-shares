<?php

class FsthumbsController extends X_Controller_Action
{

	/**
	 * @var X_VlcShares_Plugins_FsThumbs
	 */
	protected $plugin = null;
	
	/**
	 * @var X_VlcShares_Plugins_FileSystem
	 */
	protected $fsPlugin = null;
	
	function init() {
		// call parent init, always
		parent::init();
		if ( !X_VlcShares_Plugins::broker()->isRegistered('fsthumbs') ) {
			throw new Exception(X_Env::_('err_pluginnotregistered') . ": fsthumbs");
		} else {
			$this->plugin = X_VlcShares_Plugins::broker()->getPlugins('fsthumbs');
		}
		if ( !X_VlcShares_Plugins::broker()->isRegistered('fileSystem') ) {
			throw new Exception(X_Env::_('err_pluginnotregistered') . ": fileSystem");
		} else {
			$this->fsPlugin = X_VlcShares_Plugins::broker()->getPlugins('fileSystem');
		}
	}
	
	function indexAction() {
		
		$perpage = 25;
		
		$page = $this->getRequest()->getParam('page', 1);
		if ( $page < 1 ) {
			$page = 1;
		}
		
		$counts = Application_Model_FsThumbsMapper::i()->getCount();
		X_Debug::i("Thumbnails count: $counts");
		$totalSize = Application_Model_FsThumbsMapper::i()->getTotalSize();
		X_Debug::i("Thumbnails size: $totalSize");
		$pages = $counts / $perpage;
		X_Debug::i("Current page: $page");
		if ( $counts % $perpage > 0 || $pages == 0 ) $pages = (int) $pages + 1; // add an extra page
		X_Debug::i("Total pages: $pages");
		
		if ( $page > $pages ) {
			throw new Exception(X_Env::_('p_fsthumbs_invalidpage', $page, $pages));
		}
		
		$csrf = new Zend_Form_Element_Hash('csrf');
		$csrf->setSalt(__CLASS__)->initCsrfToken();
		
		$thumbs = Application_Model_FsThumbsMapper::i()->fetchPage($page - 1, $perpage);
		
		$this->view->thumbs = $thumbs;
		$this->view->counts = $counts;
		$this->view->page = $page;
		$this->view->pages = $pages;
		$this->view->perpage = $perpage;
		$this->view->totalSize = $totalSize;
		$this->view->csrf = $csrf->getHash();
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
		
	}
	
	function deleteAction() {
		
		$path = X_Env::decode($this->getRequest()->getParam('path', null));
		$hash = $this->getRequest()->getParam('csrf', false);
		$type = $this->getRequest()->getParam('type', null);

		$csrf = new X_Form_Element_Hash('csrf', array(
			'salt' => __CLASS__
		));
		//$csrf->setSalt(__CLASS__);
		
		if ( !$csrf->isValid($hash)  ) {
			//$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_fsthumbs_invalidhash', $hash, $csrf->getHash()) ));
			$this->_helper->redirector('confirm', 'fsthumbs', 'default', array(
				'type' => $type,
				'path' => $path == '' ? null : X_Env::encode($path),
			));
		}
		
		$error = false;
		
		if ( $type == null ) {
		
			// single removal
			
			$model = new Application_Model_FsThumb();
			Application_Model_FsThumbsMapper::i()->fetchByPath($path, $model);
			
			if ( $path == '' || $model->getPath() == '' || $model->isNew() || is_null($model->getPath()) ) {
				$this->_helper->flashMessenger(array('type' => 'error', 'text' => X_Env::_('p_fsthumbs_invalidpath') ));
				$this->_helper->redirector('index', 'fsthumbs');
			}
			
			@unlink(APPLICATION_PATH."/../public{$model->getUrl()}");
			
			Application_Model_FsThumbsMapper::i()->delete($model);
			
		} else {
			
			// multiple removal
			switch ($type) {
				
				case 'all': 
					// remove all thumbnails
					$toBeRemoved = Application_Model_FsThumbsMapper::i()->fetchAll();
					break;
				case 'over':
					// remove over max
					$toBeRemoved = Application_Model_FsThumbsMapper::i()->fetchOlderOffset($this->plugin->config('max.cached', 200));
					break;
				case 'orphan':
					// remove orphan thumbnail
					$toBeRemoved = Application_Model_FsThumbsMapper::i()->fetchAll();
					foreach ($toBeRemoved as $i => $thumb) {
						/* @var $thumb Application_Model_FsThumb */
						if ( file_exists($thumb->getPath()) ) {
							unset($toBeRemoved[$i]);
						} 
					}
					break;
			}
			
			// can't stop after start
			ignore_user_abort(true);
			
			foreach ($toBeRemoved as $thumb) {
				/* @var $thumb Application_Model_FsThumb */
				if ( file_exists(APPLICATION_PATH."/../public{$thumb->getUrl()}") ) {
					@unlink(APPLICATION_PATH."/../public{$thumb->getUrl()}");
				}
				try {
					Application_Model_FsThumbsMapper::i()->delete($thumb);
				} catch (Exception $e) {
					$error = true;
					X_Debug::e("Error removing {$thumb->getUrl()}: {$e->getMessage()}"); 
				}
			}
				
		}
		
		$this->_helper->flashMessenger(array('type' => ($error ? 'error' : 'success'), 'text' => X_Env::_(($error ? 'p_fsthumbs_operror' : 'p_fsthumbs_opok')) ));
		
		$this->_helper->redirector('index');
		
	}
	
	function confirmAction() {
		
		$path = X_Env::decode($this->getRequest()->getParam('path', null));
		$type = $this->getRequest()->getParam('type', null);

		$form = new X_Form();
		$form->setMethod(Zend_Form::METHOD_POST)->setAction($this->_helper->url('delete', 'fsthumbs'));
		$form->addElement('hash', 'csrf', array(
			'salt'  => __CLASS__,
			'ignore' => true,
			'required' => false
		));
		
		$form->addElement('hidden', 'path', array(
			'ignore' => true,
			'required' => false
		));
		
		$form->addElement('hidden', 'type', array(
			'ignore' => true,
			'required' => false
		));
		
		$form->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => X_Env::_('confirm'),
        ));
        
        $form->addDisplayGroup(array('submit', 'csrf', 'id'), 'buttons', array('decorators' => $form->getDefaultButtonsDisplayGroupDecorators()));
        
        $form->setDefault('path', X_Env::encode($path))
        	->setDefault('type', $type);
				
		$this->view->type = $type;
		$this->view->path = $path;
		$this->view->form = $form;
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
		
	}
	
	function thumbAction() {
		
		// time to get params from get
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();

		if ( !is_writable(APPLICATION_PATH."/../public/images/fsthumbs/thumbs/") ) {
			throw new Exception('"/public/images/fsthumbs/thumbs/" must be writable');
		}
		
		
		$l = $request->getParam('l', false);
		if ( $l == false ) {
			throw new Exception(X_Env::_('p_fsthumbs_invalidlocation'));
		}
		
		$ldec = X_Env::decode($l);
		
		$itemRealPath = $this->fsPlugin->resolveLocation($ldec);
		
		if ( $itemRealPath == false ) {
			throw new Exception(X_Env::_('p_fsthumbs_invalidlocation'));
		}
		
		
		// cache check and redirect if possible
		
		$imagePath = "/images/fsthumbs/thumbs/$l.jpg";
		
		// else thumb creation and then redirect
		if ( !file_exists(APPLICATION_PATH."/../public$imagePath") ) {
			if ( X_VlcShares_Plugins::helpers()->ffmpeg()->isEnabled() ) {
				
				// create the thumb
				$ffmpegPath = $this->options->helpers->ffmpeg->path;
				$destPath = APPLICATION_PATH."/../public$imagePath";
				$secOffset = intval($this->plugin->config('capture.seconds', '2')) + ((int) rand(0, 10)) - 5;
				if ( $secOffset < 1 ) $secOffset = 1; 
				$imageDim = $this->plugin->config('thumbs.size', '320x240');
				
				if ( !preg_match('/(\d{3})x(\d{3})/', $imageDim) ) {
					X_Debug::e("Thumb size is not a valid value (IIIxIII): $imageDim");
					$imageDim = '320x240';
				}

				// thumb creation should be always completed
				ignore_user_abort(true);
				
				
				// semaphore is needed for folders with lots files and low-end cpus
				$lockFile = fopen(__FILE__, 'r+');
				flock($lockFile, LOCK_EX);
				
				$exec = "$ffmpegPath -itsoffset -$secOffset -i \"$itemRealPath\" -vcodec mjpeg -vframes 1 -an -f rawvideo -s $imageDim \"$destPath\"";
				
				$return = X_Env::execute($exec);

				// remove lock
				flock($lockFile, LOCK_UN);
				fclose($lockFile);
				
				$filesize = @filesize($destPath);
				
				if ( $filesize == 0 ) {
					// it's better to replace the thumbs created with
					// a placeholder file to prevent a new creation
					@copy(APPLICATION_PATH."/../public/images/fsthumbs/nothumbs.jpg", $destPath);
					
					clearstatcache(true, $destPath);
					
					$filesize = @filesize($destPath);
				}
				
				$thumb = new Application_Model_FsThumb();
				// try to load an old entry in db, this prevent error when files are removed but not the reference in db
				Application_Model_FsThumbsMapper::i()->fetchByPath($itemRealPath, $thumb);
				$thumb->setCreated(time())
					->setPath($itemRealPath)
					->setUrl($imagePath)
					->setSize(@filesize($destPath));
					
				try {
					Application_Model_FsThumbsMapper::i()->save($thumb);
					X_Debug::i('Thumb stored');
				} catch (Exception $e) {
					X_Debug::e("Error while storing thumb data: {$e->getMessage()}");
				}
				
				//X_Debug::i(var_export($return, true));
				
				// -itsoffset -30  -i test.avi -vcodec mjpeg -vframes 1 -an -f rawvideo -s 320x240 test.jpg
				
			} else {
				$imagePath = "/images/fsthumbs/nothumbs.jpg";
			}
		}
		
		
		// if it is wiimc i have to return here the image directly, not a redirect
		if ( X_VlcShares_Plugins::helpers()->devices()->isWiimc() /*&& X_VlcShares_Plugins::helpers()->devices()->isWiimcBeforeVersion('1.0.9')*/ ) {

			$this->getResponse()->clearAllHeaders();
			ob_end_clean();
			
			$this->getResponse()->setHeader('Content-Type', 'image/jpeg')
				->setHeader('Content-Length', @filesize(APPLICATION_PATH."/../public$imagePath"));
			
			$this->getResponse()->sendHeaders();
			
			readfile(APPLICATION_PATH."/../public$imagePath");
			
		} else {
			$this->_helper->redirector->gotoUrlAndExit($imagePath, array('prependBase' => true, 'code' => 302));
		}
		
	}
}

