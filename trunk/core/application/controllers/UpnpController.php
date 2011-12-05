<?php 

class UpnpController extends X_Controller_Action {
	
	function manifestAction() {
		
		$file = $this->getRequest()->getParam('file');
		
	}
	
	function indexAction() {
		
		$thread = X_Threads_Manager::instance()->getMonitor()->getThread('upnp-announcer');
		
		$this->view->thread = $thread;
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
		
	}
	
	function resumeAction() {
		
		$thread = X_Threads_Manager::instance()->getMonitor()->getThread('upnp-announcer');
		if ( $thread->getState() != X_Threads_Thread_Info::RUNNING ) {
			if ( X_Threads_Manager::instance()->getMessenger()->hasMessage($thread) ) {
				X_Threads_Manager::instance()->getMessenger()->clearQueue($thread);
			}
			X_Threads_Manager::instance()->appendJob('X_Upnp_Announcer', array(
				'url' => "http://{$_SERVER['SERVER_NAME']}/vlc-shares/xml/upnp/MediaServerServiceDesc.xml"
			), 'upnp-announcer');
		}
		
		$this->_helper->redirector('index');
	}
	
	function stopAction() {
		
		$thread = X_Threads_Manager::instance()->getMonitor()->getThread('upnp-announcer');
		if ( $thread->getState() != X_Threads_Thread_Info::STOPPED ) {
			X_Threads_Manager::instance()->halt($thread);
		}
		
		$this->_helper->redirector('index');
	}
	
}
