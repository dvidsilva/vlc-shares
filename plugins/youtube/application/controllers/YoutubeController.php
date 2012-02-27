<?php

require_once 'X/Controller/Action.php';

class YoutubeController extends X_Controller_Action
{

	private $cFormats = array(
		'file.srt' => '_convertoToSrt',
	);
	
	function init() {
		parent::init();
		if ( !X_VlcShares_Plugins::broker()->isRegistered('youtube') ) {
			$this->_helper->flashMessenger(X_Env::_('err_pluginnotregistered') . ": youtube");
			$this->_helper->redirector('index', 'manage');
		}
	}
	
	function indexAction() {
		
		$categories = Application_Model_YoutubeCategoriesMapper::i()->fetchAll();
		$accounts = Application_Model_YoutubeAccountsMapper::i()->fetchAll();
		
		$this->view->categories = $categories;
		$this->view->accounts = $accounts;
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
	}
	
	function categoryAction() {
		
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		if ( $request->isXmlHttpRequest() ) {
			$this->_helper->layout->disableLayout();
		}
		
		$idCategory = $request->getParam('idCategory', false);
		
		$videos = array();
		$category = new Application_Model_YoutubeCategory();

		try {
			$form = new Application_Form_YoutubeCategory();
			$form->setAction($this->_helper->url('scategory', 'youtube'));
			$form->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
		} catch (Exception $e) {
			//X_Debug::w($e->getMessage());
			$form = '<p>'.X_Env::_('p_youtube_form_err_noupload').': ' . $e->getMessage() . '</p>';
		}
		
		if ( /*$form instanceof Application_Form_YoutubeCategory &&*/ $idCategory !== false && $idCategory !== '') {
			Application_Model_YoutubeCategoriesMapper::i()->find($idCategory, $category);
			
			if ( $idCategory == $category->getId() ) {
				$videos = Application_Model_YoutubeVideosMapper::i()->fetchByCategory($category->getId());

				if ( $form instanceof Application_Form_YoutubeCategory ) {
					$form
						->id
							->setValue(
								$category->getId()
								);
					$form->label->setValue($category->getLabel());
					$form->thumbselect->setValue(pathinfo($category->getThumbnail(), PATHINFO_BASENAME));
				}
			}
		}
		
		$this->view->form = $form;
		$this->view->category = $category;
		$this->view->videos = $videos;
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
	}
	
	function videoAction() {
		
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		if ( $request->isXmlHttpRequest() ) {
			$this->_helper->layout->disableLayout();
		}
		
		try {
			$form = new Application_Form_YoutubeVideo();
			$form->setAction($this->_helper->url('svideo', 'youtube'));
			$form->setEnctype(Zend_Form::ENCTYPE_MULTIPART);
		} catch (Exception $e) {
			$form = '<p>'.X_Env::_('p_youtube_form_err_noupload').': ' . $e->getMessage() . '</p>';
		}
		
		$this->view->form = $form;
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
		
	}
		
	function accountAction() {
		
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		if ( $request->isXmlHttpRequest() ) {
			$this->_helper->layout->disableLayout();
		}
		
		try {
			$form = new Application_Form_YoutubeAccount();
			$form->setAction($this->_helper->url('saccount', 'youtube'));
		} catch (Exception $e) {
			$form = '<p>'.X_Env::_('p_youtube_form_err_noupload').': ' . $e->getMessage() . '</p>';
		}
		
		$this->view->form = $form;
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
		
	}
	
	function saccountAction() {
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		if ( !$request->isPost() ) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_save_invalidrequest'), 'type' => 'error'));
			$this->_helper->redirector('index', 'youtube');
		}
		try {
			$form = new Application_Form_YoutubeAccount();
		} catch (Exception $e) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_save_formerror').": {$e->getMessage()}", 'type' => 'error'));
			$this->_helper->redirector('index', 'youtube');
		}
		$post = $request->getPost();
		X_Debug::i("Post = '" . var_export($post, true) . "'");
		$valid = $form->isValid($post);
		if ( $valid != true ) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_save_invaliddata'), 'type' => 'error'));
			//$this->_helper->flashMessenger(array('text' => '<pre>'.var_export($form, true).'</pre>', 'type' => 'error'));
			foreach ($form->getErrorMessages() as $error) {
				$this->_helper->flashMessenger(array('text' => $error, 'type' => 'error'));
			}
			$this->_helper->redirector('index', 'youtube');
		}
		try {
			$accountName = $form->getValue('label');
			
			// check if $accountName is a valid account and if it has a thumbnail image
			/* @var $helper X_VlcShares_Plugins_Helper_Youtube */
			$helper = X_VlcShares_Plugins::helpers('youtube');
			
			$accountProfile = $helper->getAccountInfo($accountName);
			$thumb = $accountProfile->getThumbnail()->getUrl();
			
			$account = new Application_Model_YoutubeAccount();
			$account->setLabel($accountName)
				->setThumbnail($thumb);
			Application_Model_YoutubeAccountsMapper::i()->save($account);
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_save_completed_account'), 'type' => 'info'));
			$this->_helper->redirector('index', 'youtube');
		} catch (Zend_Gdata_App_HttpException $e) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_save_err_invalidaccount'), 'type' => 'error'));
			$this->_helper->redirector('index', 'youtube');
		} catch (Exception $e) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_save_err_dberror').": {$e->getMessage()}", 'type' => 'error'));
			$this->_helper->redirector('index', 'youtube');
		}
	}

	function svideoAction() {
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		if ( !$request->isPost() ) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_save_invalidrequest'), 'type' => 'error'));
			$this->_helper->redirector('index', 'youtube');
		}
		try {
			$form = new Application_Form_YoutubeVideo();
		} catch (Exception $e) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_save_formerror').": {$e->getMessage()}", 'type' => 'error'));
			$this->_helper->redirector('index', 'youtube');
		}
		$post = $request->getPost();
		X_Debug::i("Post = '" . var_export($post, true) . "'");
		$valid = $form->isValid($post);
		if ( $valid != true ) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_save_invaliddata'), 'type' => 'error'));
			//$this->_helper->flashMessenger(array('text' => '<pre>'.var_export($form, true).'</pre>', 'type' => 'error'));
			foreach ($form->getErrorMessages() as $error) {
				$this->_helper->flashMessenger(array('text' => $error, 'type' => 'error'));
			}
			$this->_helper->redirector('index', 'youtube');
		}
		try {
			$idYoutube = $form->getValue('idYoutube');
			
			if ( X_Env::startWith($idYoutube, 'http') ) {
				$start = strpos($idYoutube, 'v=');
				$end = strpos($idYoutube, '&', $start);
				if ( $end !== false ) {
					$idYoutube = substr($idYoutube, $start + 2, $end - $start - 2);
				} else {
					$idYoutube = substr($idYoutube, $start + 2);
				}
				X_Debug::i("Found ID: $idYoutube");
			}
			
			$idCategory = $form->getValue('idCategory');
			// check if $accountName is a valid account and if it has a thumbnail image
			/* @var $helper X_VlcShares_Plugins_Helper_Youtube */
			$helper = X_VlcShares_Plugins::helpers('youtube');
			
			$videoEntry = $helper->getVideo($idYoutube);
			$thumb = $videoEntry->getVideoThumbnails();
			$thumb = @$thumb[0]['url'];
			$thumb = str_replace('default', '0', $thumb);
			
			$video = new Application_Model_YoutubeVideo();
			$video->setLabel($videoEntry->getVideoTitle())
				->setDescription($videoEntry->getVideoDescription())
				->setIdYoutube($idYoutube)
				->setIdCategory($idCategory)
				->setThumbnail($thumb);
			Application_Model_YoutubeVideosMapper::i()->save($video);
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_save_completed_video'), 'type' => 'info'));
			$this->_helper->redirector('category', 'youtube','default',array('idCategory'=>$idCategory));
		} catch (Zend_Gdata_App_HttpException $e) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_save_err_invalidvideo'), 'type' => 'error'));
			$this->_helper->redirector('index', 'youtube');
		} catch (Exception $e) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_save_err_dberror').": {$e->getMessage()}", 'type' => 'error'));
			$this->_helper->redirector('index', 'youtube');
		}
	}
	
	
	function scategoryAction() {
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		if ( !$request->isPost() ) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_save_invalidrequest'), 'type' => 'error'));
			$this->_helper->redirector('index', 'youtube');
		}
		try {
			$form = new Application_Form_YoutubeCategory();
		} catch (Exception $e) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_save_formerror').": {$e->getMessage()}", 'type' => 'error'));
			$this->_helper->redirector('index', 'youtube');
		}
		$post = $request->getPost();
		X_Debug::i("Post = '" . var_export($post, true) . "'");
		$valid = $form->isValid($post);
		$thumbselectValue = $form->getValue('thumbselect');
		X_Debug::i("Valid = '$valid'");
		X_Debug::i("Thumbselect = '$thumbselectValue'");
		//X_Debug::i("Error messages = '" . var_export($form->getErrorMessages(), true) . "'");
		//X_Debug::i("Errors = '" . var_export($form->getErrors(), true) . "'");
		if ( $thumbselectValue == 'upload' ) {
			// if thumbselectvalue == '', i have to read the file content
			// as required
			$form->thumbnail->setRequired(true);
			// i need to check it again if thumbnail upload is required
			$valid = $form->isValid($post);
			X_Debug::i("Valid = '$valid'");
		}
		if ( $valid != true ) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_save_invaliddata'), 'type' => 'error'));
			//$this->_helper->flashMessenger(array('text' => '<pre>'.var_export($form, true).'</pre>', 'type' => 'error'));
			foreach ($form->getErrorMessages() as $error) {
				$this->_helper->flashMessenger(array('text' => $error, 'type' => 'error'));
			}
			$this->_helper->redirector('index', 'youtube');
		}
		if ( $thumbselectValue == 'upload' && $form->thumbnail->isUploaded() ) {
			try {
				$form->thumbnail->receive();
			} catch ( Exception $e) {
				$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_save_err_receivedata'), 'type' => 'error'));
				$this->_helper->redirector('index', 'youtube');
			}
			$thumbselectValue = '/images/youtube/uploads/'.pathinfo($form->thumbnail->getFileName(), PATHINFO_BASENAME);
		} else {
			$thumbselectValue = '/images/youtube/uploads/'.$thumbselectValue;
		}
		try {
			$category = new Application_Model_YoutubeCategory();
			$categoryId = $form->getValue('id');
			if ( $categoryId ) {
				Application_Model_YoutubeCategoriesMapper::i()->find($categoryId, $category);
			}
			$category->setLabel($form->getValue('label'))
				->setThumbnail($thumbselectValue);
			Application_Model_YoutubeCategoriesMapper::i()->save($category);
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_save_completed_category'), 'type' => 'info'));
			$this->_helper->redirector('index', 'youtube');
		} catch (Exception $e) {
			$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_save_err_dberror').": {$e->getMessage()}", 'type' => 'error'));
			$this->_helper->redirector('index', 'youtube');
		}
	}

	function dcategoryAction() {
		$categoryId = $this->getRequest()->getParam('idCategory', false);
		if ( $categoryId != false && $categoryId != null && $categoryId != '' ) {
			$category = new Application_Model_YoutubeCategory();
			Application_Model_YoutubeCategoriesMapper::i()->find($categoryId, $category);
			if ( $categoryId == $category->getId() ) {
				try {
					Application_Model_YoutubeVideosMapper::i()->deleteByCategory($category);
					Application_Model_YoutubeCategoriesMapper::i()->delete($category);
					$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_delete_completed_category'), 'type' => 'info'));
				} catch (Exception $e) {
					$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_delete_err_dberror').": {$e->getMessage()}", 'type' => 'error'));
				}
			} else {
				$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_delete_invaliddata'), 'type' => 'error'));
			}
		}
		$this->_helper->redirector('index', 'youtube');
	}
	
	function dvideoAction() {
		$videoId = $this->getRequest()->getParam('idVideo', false);
		if ( $videoId != false && $videoId != null && $videoId != '' ) {
			$video = new Application_Model_YoutubeVideo();
			Application_Model_YoutubeVideosMapper::i()->find($videoId, $video);
			if ( $videoId == $video->getId() ) {
				$idCategory = $video->getIdCategory();
				try {
					Application_Model_YoutubeVideosMapper::i()->delete($video);
					$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_delete_completed_video'), 'type' => 'info'));
				} catch (Exception $e) {
					$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_delete_err_dberror').": {$e->getMessage()}", 'type' => 'error'));
				}
				$this->_helper->redirector('category', 'youtube', 'default', array('idCategory' => $idCategory));
			} else {
				$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_delete_invaliddata'), 'type' => 'error'));
			}
		}
		$this->_helper->redirector('index', 'youtube');
	}
	
	
	function daccountAction() {
		$accountId = $this->getRequest()->getParam('idAccount', false);
		if ( $accountId != false && $accountId != null && $accountId != '' ) {
			$account = new Application_Model_YoutubeAccount();
			Application_Model_YoutubeAccountsMapper::i()->find($accountId, $account);
			if ( $accountId == $account->getId() ) {
				try {
					Application_Model_YoutubeAccountsMapper::i()->delete($account);
					$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_delete_completed_account'), 'type' => 'info'));
				} catch (Exception $e) {
					$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_delete_err_dberror').": {$e->getMessage()}", 'type' => 'error'));
				}
			} else {
				$this->_helper->flashMessenger(array('text' => X_Env::_('p_youtube_delete_invaliddata'), 'type' => 'error'));
			}
		}
		$this->_helper->redirector('index', 'youtube');
	}
	
	function convertAction() {
		
		// time to get params from get
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();

		// this action is so special.... no layout or viewRenderer
		$this->_helper->viewRenderer->setNoRender();
		$this->_helper->layout->disableLayout();
		
		$videoId = $request->getParam('v', false); // youtube video id
		$format = $request->getParam('f', false); // convert to format
		$lcode = $request->getParam('l', false); // language code
		
		if ( $videoId === false || $format === false || !array_key_exists($format, $this->cFormats) || $lcode === false ) {
			// invalid request
			echo 'Invalid Request';
			return;
		}
		
		// $lcode need to be decoded
		$lcode = X_Env::decode($lcode); 
		
		/* @var $helper X_VlcShares_Plugins_Helper_Youtube */
		$helper = X_VlcShares_Plugins::helpers('youtube');
		
		try {
			$sub = $helper->getSubtitleNOAPI($videoId, $lcode);
		} catch (Exception $e) {
			echo 'Invalid Language Code';
			return;
		}
		
		// $sub format:
		/*
		$sub = array(
			'id' => $current->getAttribute('id'),
			'name' => $current->getAttribute('name'),
			'lang_code' => $current->getAttribute('lang_code'),
			'lang_original' => $current->getAttribute('lang_original'),
			'lang_translated' => $current->getAttribute('lang_translated'),
			'lang_default' => $current->getAttribute('lang_default'),
			'xml_url'	=> 'http://video.google.com/timedtext?type=track&' 
								.'lang='.utf8_encode($sub['lang_code']).'&'
								.'name='.utf8_encode($sub['name']).'&'
								.'v='.$this->_location;
		);
		*/	
		
		$uri = $sub['xml_url'];
		
		$http = $helper->getHttpClient($uri);
		
		$xml = $http->request(Zend_Http_Client::GET)->getBody();
		
		$dom = new Zend_Dom_Query($xml);
		
		$method = $this->cFormats[$format];

		$string = $this->$method($dom);
		
		echo $string;
		
		
	}
	
	protected function _convertoToSrt(Zend_Dom_Query $dom) {
		
		$this->getResponse()->setHeader('Content-type', 'application/x-subrip', true);
		//$this->getResponse()->setHeader('Content-type', 'text/plain', true);
		
		$results = $dom->queryXpath('//text');
		
		$i = 1;
		$string = '';
		while ( $results->valid() ) {
			$current = $results->current();
			
			$from = $current->getAttribute('start');
			$dur = $current->getAttribute('dur');
			$text = $current->nodeValue;
			
			$end = (double) $from + (double) $dur;
			
			$from = explode('.', (string) $from);
			$from = X_Env::formatTime($from[0]) . ',' . str_pad(substr($from[1], 0, 3), 3, '0', STR_PAD_RIGHT);

			$end = explode('.', (string) $end);
			$end = X_Env::formatTime($end[0]) . ',' . str_pad(substr($end[1], 0, 3), 3, '0', STR_PAD_RIGHT);
			
			$text = str_replace(array('&quot;', '&amp;', '&#39;' ), array("\"", "&", "'" ), utf8_decode($text));
			
			$string .= "$i\r\n";
			$string .= "$from --> $end\r\n";
			$string .= "$text\r\n";
			$string .= "\r\n";
			$results->next();
			$i++;
		}
		return $string;
	}
	
}

