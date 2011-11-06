<?php

require_once 'X/Controller/Action.php';

class ManageController extends X_Controller_Action
{
	protected $vlc = null;

    public function init()
    {
        /* Initialize action controller here */
    	parent::init();
    	$this->vlc = new X_Vlc($this->options->vlc);
    }
    
    public function applicationsAction() {
    	
    	$manageLinks = new X_Page_ItemList_ManageLink();
    	$item = new X_Page_Item_ManageLink('core-manage-home', X_Env::_('Dashboard'));
    	$item
    		->setTitle(X_Env::_('Dashboard'))
    		->setIcon('/images/altgui/dashboard.png')
    		->setLink(array(
    			'controller'	=> 'manage',
    			'action'		=> 'index'
    		), 'default', true);
    	$manageLinks->append($item);
    	$item = new X_Page_Item_ManageLink('core-manage-browse', X_Env::_('manage_goto_browsetitle'));
    	$item
    		->setTitle(X_Env::_('manage_goto_browse'))
    		->setIcon('/images/manage/browse.png')
    		->setLink(array(
    			'controller'	=> 'index',
    			'action'		=> 'collections'
    		), 'default', true);
    	$manageLinks->append($item);
    	/*
    	$item = new X_Page_Item_ManageLink('core-manage-test', X_Env::_('manage_goto_testtitle'));
    	$item
    		->setTitle(X_Env::_('manage_goto_test'))
    		->setIcon('/images/manage/test.png')
    		->setLink(array(
    			'controller'	=> 'test',
    			'action'		=> 'index'
    		), 'default', true);
    	$manageLinks->append($item);
    	*/
    	$item = new X_Page_Item_ManageLink('core-manage-configs', X_Env::_('manage_goto_configstitle'));
    	$item
    		->setTitle(X_Env::_('manage_goto_configs'))
    		->setIcon('/images/manage/configs.png')
    		->setLink(array(
    			'controller'	=> 'configs',
    			'action'		=> 'index'
    		), 'default', true);
    	$manageLinks->append($item);
    	
    	$manageLinks->merge(X_VlcShares_Plugins::broker()->getIndexManageLinks($this));
    	
    	$actualController = $this->getFrontController()->getRequest()->getControllerName();
    	
    	$this->view->manageLinks = $manageLinks;
    	$this->view->actualController = $actualController;
    	
    }

    public function statusAction() {
    	$statusLinks = new X_Page_ItemList_StatusLink();
		$statusLinks->merge(X_VlcShares_Plugins::broker()->preGetStatusLinks($this));    	
		$statusLinks->merge(X_VlcShares_Plugins::broker()->getStatusLinks($this));
		$statusLinks->merge(X_VlcShares_Plugins::broker()->postGetStatusLinks($this));
		
		$this->view->statusLinks = $statusLinks;
		
    }
    
    /**
     * Show the dashboard
     */
    public function indexAction() {
    	
    	$coreLinks = array(
    		array(
    			'label'		=>	X_Env::_('manage_goto_browse'),
    			'link'		=>	$this->_helper->url('collections', 'index'),
    			'icon'		=>	'/images/manage/browse.png',
    			'title'		=>	X_Env::_('manage_goto_browsetitle'),
    		),
    		array(
    			'label'		=>	X_Env::_('manage_goto_test'),
    			'link'		=>	$this->_helper->url('index', 'test'),
    			'icon'		=>	'/images/manage/test.png',
    			'title'		=>	X_Env::_('manage_goto_testtitle'),
    		),
    		array(
    			'label'		=>	X_Env::_('manage_goto_configs'),
    			'link'		=>	$this->_helper->url('configs', 'manage'),
    			'icon'		=>	'/images/manage/configs.png',
    			'title'		=>	X_Env::_('manage_goto_configstitle'),
    		),
    	);

    	/* @var $messages X_Page_ItemList_Message */
    	$messages = new X_Page_ItemList_Message();
    	$messages->merge(X_VlcShares_Plugins::broker()->getIndexMessages($this));
    	$fm = $this->_helper->flashMessenger->getMessages();
    	foreach ($fm as $i => $m) {
    		$_m = new X_Page_Item_Message("fm-$i", '');
    		if ( is_array($m) ) { 
    			if ( array_key_exists('type', $m) ) {
    				$_m->setType($m['type']);
    			}
    			if ( array_key_exists('text', $m) ) {
    				$_m->setLabel($m['text']);
    			}
    		} else {
    			$_m->setType(X_Page_Item_Message::TYPE_WARNING)
    				->setLabel((string) $m);
    		}
    		$messages->append($_m);
    	}
    	
    	$news = X_VlcShares_Plugins::broker()->getIndexNews($this);

    	$manageLinks = new X_Page_ItemList_ManageLink();
    	$item = new X_Page_Item_ManageLink('core-manage-browse', X_Env::_('manage_goto_browsetitle'));
    	$item
    		->setTitle(X_Env::_('manage_goto_browse'))
    		->setIcon('/images/manage/browse.png')
    		->setLink(array(
    			'controller'	=> 'index',
    			'action'		=> 'collections'
    		), 'default', true);
    	$manageLinks->append($item);
    	$item = new X_Page_Item_ManageLink('core-manage-test', X_Env::_('manage_goto_testtitle'));
    	$item
    		->setTitle(X_Env::_('manage_goto_test'))
    		->setIcon('/images/manage/test.png')
    		->setLink(array(
    			'controller'	=> 'test',
    			'action'		=> 'index'
    		), 'default', true);
    	$manageLinks->append($item);
    	$item = new X_Page_Item_ManageLink('core-manage-configs', X_Env::_('manage_goto_configstitle'));
    	$item
    		->setTitle(X_Env::_('manage_goto_configs'))
    		->setIcon('/images/manage/configs.png')
    		->setLink(array(
    			'controller'	=> 'configs',
    			'action'		=> 'index'
    		), 'default', true);
    	$manageLinks->append($item);
    	$manageLinks->merge(X_VlcShares_Plugins::broker()->getIndexManageLinks($this));
    	
    	// fetch links from plugins
		$actionLinks = X_VlcShares_Plugins::broker()->getIndexActionLinks($this);
		
		$statistics = X_VlcShares_Plugins::broker()->getIndexStatistics($this);
		
		$this->view->version = X_VlcShares::VERSION;
		//$this->view->configPath = X_VlcShares::config();
		//$this->view->test = $this->_helper->url('index', 'test');
		//$this->view->pcstream = $this->_helper->url('pcstream', 'index');
		//$this->view->pcstream_enabled = ($this->options->pcstream->get('commanderEnabled', false) && $this->vlc->isRunning());
		$this->view->coreLinks = $coreLinks;
		$this->view->actionLinks = $actionLinks;
		$this->view->manageLinks = $manageLinks;
		$this->view->statistics = $statistics;
		$this->view->news = $news;
		$this->view->messages = $messages;
    	
		
		// i need to get first class links
		
    }    
}

