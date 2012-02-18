<?php 


class X_VlcShares_Plugins_RtmpDump extends X_VlcShares_Plugins_Abstract {
	
	function __construct() {
		$this
			->setPriority('gen_afterPluginsInitialized')
			->setPriority('gen_beforeInit');
		
	}
	
	/**
	 * Register rtmpdump engine
	 * @see X_VlcShares_Plugins_Abstract::gen_afterPluginsInitialized()
	 */
	public function gen_afterPluginsInitialized(X_VlcShares_Plugins_Broker $broker) {
		$this->helpers()->streamer()->register(new X_Streamer_Engine_RtmpDump());
	}

	public function gen_beforeInit(Zend_Controller_Action $controller) {
		if ( $this->helpers()->rtmpdump()->isEnabled() ) {
			$this
				->setPriority('getStreamItems')
				->setPriority('preGetControlItems');
			
		}
	}

	/**
	 * Add the go to stream link (only if engine is rtmpdump)
	 * 
	 * @param X_Streamer_Engine $engine selected streamer engine
	 * @param string $uri
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to stream
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return X_Page_ItemList_PItem 
	 */
	public function getStreamItems(X_Streamer_Engine $engine, $uri, $provider, $location, Zend_Controller_Action $controller) {
	
		// ignore the call if streamer is not rtmpdump
		if ( !($engine instanceof X_Streamer_Engine_RtmpDump ) ) return;
		
		X_Debug::i('Plugin triggered');
		
		$return = new X_Page_ItemList_PItem();
		
		
		$outputLink = "http://{%SERVER_NAME%}:{$this->helpers()->rtmpdump()->getStreamPort()}/";
		$outputLink = str_replace(
			array(
				'{%SERVER_IP%}',
				'{%SERVER_NAME%}'
			),array(
				$_SERVER['SERVER_ADDR'],
				strstr($_SERVER['HTTP_HOST'], ':') ? strstr($_SERVER['HTTP_HOST'], ':') : $_SERVER['HTTP_HOST']
			), $outputLink
		);
		
		$item = new X_Page_Item_PItem($this->getId(), X_Env::_('p_outputs_gotostream'));
		$item->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
			->setIcon('/images/icons/play.png')
			->setLink($outputLink);
		$return->append($item);		

		/*

		$item = new X_Page_Item_PItem('controls-stop', X_Env::_('p_controls_stop'));
		$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setIcon('/images/icons/stop.png')
			->setLink(array(
				'controller'		=>	'controls',
				'action'	=>	'execute',
				'a'			=>	'stop',
				'pid'		=>	$this->getId(),
			), 'default', false);
		$return->append($item);
		
		*/
		
		
		return $return;
		
	}
	
	/**
	 * Add the button BackToStream in controls page
	 *
	 * @param X_Streamer_Engine $engine
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array
	 */
	public function preGetControlItems(X_Streamer_Engine $engine, Zend_Controller_Action $controller) {
	
		// ignore if the streamer is not vlc
		if ( !($engine instanceof X_Streamer_Engine_RtmpDump ) ) return;

		$outputLink = "http://{%SERVER_NAME%}:{$this->helpers()->rtmpdump()->getStreamPort()}/";
		$outputLink = str_replace(
				array(
						'{%SERVER_IP%}',
						'{%SERVER_NAME%}'
				),array(
						$_SERVER['SERVER_ADDR'],
						strstr($_SERVER['HTTP_HOST'], ':') ? strstr($_SERVER['HTTP_HOST'], ':') : $_SERVER['HTTP_HOST']
				), $outputLink
		);
		
		$item = new X_Page_Item_PItem($this->getId(), X_Env::_('p_profiles_backstream'));
		$item->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
		->setIcon('/images/icons/play.png')
		->setLink($outputLink);
		return new X_Page_ItemList_PItem(array($item));
		
	}
	
}
