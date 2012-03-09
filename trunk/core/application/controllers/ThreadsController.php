<?php 

class ThreadsController extends X_Threads_Controller_Action {
	
	
	public function checkAction() {
		
		$pings = $this->getRequest()->getParam('pings', array());
		
		$client = new Zend_Http_Client();
		$client->setConfig(array(
				'timeout' => 3,
				'keepalive' => false
			));
		
		foreach ( $pings as $ping ) {
			try {
				$client->setUri($ping.'ping');
				$json = Zend_Json::decode( $client->request()->getBody() );
				if ( isset($json['success']) && $json['success'] ) {
					$this->_helper->json(array(
								'success' => true,
								'valid' => $ping.'start'
							), true, false);
					return;
				} 
			} catch (Exception $e) {
				// invalid http/invalid response/timeout
			}			
		}
		
		$this->_helper->json(array(
				'success' => false,
				'valid' => false
		), true, false);
		
	}
		
	public function pingAction() {
		$this->_helper->json(array(
				'success' => true,
		), true, false);		
	}
	
}
