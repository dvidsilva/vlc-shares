<?php

abstract class X_Threads_Controller_Action extends Zend_Controller_Action {
	
	
	final public function startAction() {
		
		//X_Debug::e("Inside action");

		
		/* @var $request Zend_Controller_Request_Http */
		$request = $this->getRequest();
		
		// complete-hash
		$hash 			= $request->getPost('hash', false);
		// hash-salt
		$salt 			= $request->getPost('salt', false);
		// key-id
		$key 			= $request->getPost('key', false);
		
		
		$threadId		= $request->getParam('thread', false);
		/*
		$runnableClass	= $request->getPost('runnable', false);
		$params			= $request->getPost('params', array());
		*/

		$manager = X_Threads_Manager::instance();
		
		// check if hash_funct(value-of(key).$salt) == $hash
		if ( !$manager->getStarter()->isValid($key, $hash, $salt) ) {
			return $this->triggerError('403: Forbidden', X_Threads_Manager::ERROR_FORBIDDEN);
		}

		/*
		try {
			
			$thread = $manager->appendJob($runnableClass, $params, $threadId);
			
			$this->triggerOk("Thread started", $thread->getId());
	
			if ( !$thread->isRunning() ) {
				$manager->resume($thread);
			}
			
		} catch (Exception $e) {
			return $this->triggerError($e->getMessage(), $e->getCode());
		}
		*/
		
		
		
		$thread = $manager->newThread($threadId);
		
		$this->triggerOk("Thread started", $thread->getId());
		
		$thread->loop();
		
	}
	
	
	private function triggerError($message, $code) {
		return $this->_helper->json(array(
			'success' 	=> false,
			'message' 	=> $message,
			'code'		=> $code
		), false, true);
	}
	
	private function triggerOk($message, $threadId ) {
		
		$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout->disableLayout();
		
		$json = Zend_Json::encode(array(
			'success' 	=> true,
			'message' 	=> $message,
			'thread'	=> $threadId
		));
		
		/* @var $response Zend_Controller_Response_Http */
		$response = $this->getResponse();
		
		$response->setHeader('Content-Type', 'application/json', true);
		$response->setHeader('Content-Length', '0', true);
		$response->setHeader('Transfer-Encoding', 'identity', true);
		$response->setHeader('Connection', 'close', true);
		$response->setBody($json);
		
		ignore_user_abort(true);
		
		$size = strlen($json);
		
		$response->sendResponse();
		//echo $response;
		
	}
	
	/* (non-PHPdoc)
	 * @see Zend_Controller_Action::preDispatch()
	 */
	public function init() {

		$this->getFrontController()->setParam('disableOutputBuffering', true);
		$this->getFrontController()->returnResponse(false);
		
		parent::init();
		
	}

	
}

