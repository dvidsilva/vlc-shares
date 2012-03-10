<?php


class TmanagerController extends X_Controller_Action
{
	
	function indexAction() {
		
		$threads = X_Threads_Manager::instance()->getMonitor()->getThreads();
		
		$csrf = new Zend_Form_Element_Hash('csrf', array(
			'salt' => __CLASS__
		));
		$csrf->initCsrfToken();
		
		$this->view->threads = $threads;
		$this->view->csrf = $csrf->getHash();
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
		
	}

	
	function tasksAction() {

		$id = $this->getRequest()->getParam('id', false);
		if ( !$id ) {
			throw new Exception("Invalid thread");
		}
		
		$thread = X_Threads_Manager::instance()->getMonitor()->getThread($id);
		$queue = X_Threads_Manager::instance()->getMessenger()->showQueue($thread);
	
		$csrf = new Zend_Form_Element_Hash('csrf', array(
				'salt' => __CLASS__
		));
		$csrf->initCsrfToken();
	
		$this->view->thread = $thread;
		$this->view->queue = $queue;
		$this->view->csrf = $csrf->getHash();
		$this->view->messages = $this->_helper->flashMessenger->getMessages();
		
	
	}
	
	
	function startAction() {
		$id = $this->getRequest()->getParam('id', false);
		$csrf = $this->getRequest()->getParam('csrf', false);
		
		if ( !$id ) {
			throw new Exception("Thread id missing");
		}
		
		
		$hash = new Zend_Form_Element_Hash('csrf', array(
				'salt' => __CLASS__
		));
		
		if ( !$hash->isValid($csrf) ) {
			throw new Exception("Invalid token");
		}
		$hash->initCsrfToken();
		
		$thread = X_Threads_Manager::instance()->getMonitor()->getThread($id);
		X_Threads_Manager::instance()->wakeup($thread);
		
		$this->_helper->flashMessenger(array('type' => 'success', 'text' => X_Env::_('threads_done')));
		$this->_helper->redirector('index', 'tmanager');
		
		
	}

	function stopAction() {
		
		$id = $this->getRequest()->getParam('id', false);
		$csrf = $this->getRequest()->getParam('csrf', false);
	
		if ( !$id ) {
			throw new Exception("Thread id missing");
		}
	
	
		$hash = new Zend_Form_Element_Hash('csrf', array(
				'salt' => __CLASS__
		));
	
		if ( !$hash->isValid($csrf) ) {
			throw new Exception("Invalid token");
		}
		$hash->initCsrfToken();
	
		$thread = X_Threads_Manager::instance()->getMonitor()->getThread($id);
			if ( $thread->getId() == X_Streamer::THREAD_ID ) {
			X_Debug::i('Special stop');
			X_Streamer::i()->stop();
		} else {
			X_Threads_Manager::instance()->stop($thread);
		}
		
		$this->_helper->flashMessenger(array('type' => 'success', 'text' => X_Env::_('threads_done')));
		$this->_helper->redirector('index', 'tmanager');
		
	}
	
	
	
	function removeAction() {

		$id = $this->getRequest()->getParam('id', false);
		$csrf = $this->getRequest()->getParam('csrf', false);
	
		if ( !$id ) {
			throw new Exception("Thread id missing");
		}
	
	
		$hash = new Zend_Form_Element_Hash('csrf', array(
				'salt' => __CLASS__
		));
	
		if ( !$hash->isValid($csrf) ) {
			throw new Exception("Invalid token");
		}
		$hash->initCsrfToken();
	
		$thread = X_Threads_Manager::instance()->getMonitor()->getThread($id);

		// special case for streamer
		if ( $thread->getId() == X_Streamer::THREAD_ID ) {
			X_Debug::i('Special stop');
			X_Streamer::i()->stop();
		} else {
			X_Threads_Manager::instance()->halt($thread);
		}
		
		// wait 5 seconds
		sleep(5);
		
		X_Threads_Manager::instance()->getMessenger()->clearQueue($thread);
		X_Threads_Manager::instance()->getMonitor()->removeThread($thread, true);

		$this->_helper->flashMessenger(array('type' => 'success', 'text' => X_Env::_('threads_done')));
		$this->_helper->redirector('index', 'tmanager');
				
	}


	function clearAction() {
	
		$id = $this->getRequest()->getParam('id', false);
		$csrf = $this->getRequest()->getParam('csrf', false);
	
		if ( !$id ) {
			throw new Exception("Thread id missing");
		}
	
	
		$hash = new Zend_Form_Element_Hash('csrf', array(
				'salt' => __CLASS__
		));
	
		if ( !$hash->isValid($csrf) ) {
			throw new Exception("Invalid token");
		}
		$hash->initCsrfToken();
	
		$thread = X_Threads_Manager::instance()->getMonitor()->getThread($id);
	
		X_Threads_Manager::instance()->getMessenger()->clearQueue($thread);
	
		$this->_helper->flashMessenger(array('type' => 'success', 'text' => X_Env::_('threads_done')));
		$this->_helper->redirector('index', 'tmanager');
	
	}
	
	
}

