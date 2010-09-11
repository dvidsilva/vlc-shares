<?php

require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'X/Plx.php';
require_once 'X/Env.php';
require_once 'X/Plx/Item.php';
require_once 'Zend/Config.php';
require_once 'Megavideo.php';
require_once 'Zend/Controller/Request/Abstract.php';

class X_VlcShares_Plugins_Megavideo extends X_VlcShares_Plugins_Abstract {

	/**
	 * 
	 * @var Zend_Config
	 */
	private $options;
	
	private $_registeredEvents = array(
		X_VlcShares::TRG_COLLECTIONS_INDEX	=>	'getLibraryLink',
		X_VlcShares::TRG_PLUGIN_PAGE		=>	'getMegavideoLinks',
		X_VlcShares::TRG_MANAGE_PLUGINS_CONFS => 'getConfLink'
	);
	
	public function __construct(Zend_Config $options) {
		$this->options = $options;
		$this->id = $this->options->id;
		$this->registerEvents($this->_registeredEvents);
	}	
	
	public function getConfLink() {
		return array(
			'pluginName' => $this->getId().' (X_VlcShares_Plugins_Megavideo)',
			'pluginDesc' => X_Env::_('megavideo_plugin_desc'),
			'pluginLink' => X_Env::routeLink('megavideo','index') 
		);
	}
	
	public function getLibraryLink($args = array()) {
		return new X_Plx_Item(X_Env::_('megavideo_library'), 
								X_Env::routeLink('plugin', 'exec', array('id' => $this->getId()))
							);
	}
	 
	public function getMegavideoLinks($args = array()) {

		$pluginId = $args['id'];
		if ( $pluginId != $this->getId() )
			return;
		
		$request = $args['request'];
		$paramsBase = 'plg:'.$this->getId();
		
		$action = $request->getParam("$paramsBase:action", 'show');
		$id = $request->getParam("$paramsBase:id", '');
		$category = $request->getParam("$paramsBase:category", '');
		$streamType = $request->getParam("$paramsBase:streamType", 'direct');
		$cAction = $request->getParam("$paramsBase:cAction", '');
		$seekTime = $request->getParam("$paramsBase:minute", '');
		
		
		X_Env::debug(__METHOD__);
		X_Env::debug(print_r($request->getParams(), true));
		
		
		
		/*
		$plx = new X_Plx('VLCShares - '.X_Env::_('megavideo_library'), X_Env::_("title_description"));
		$plx->addItem(new X_Plx_Item('link1', X_Env::routeLink(X_Env::routeLink('plugin', 'exec', array('plg' => $this->options->id, 'linkId' => '1')))));
		$plx->addItem(new X_Plx_Item('link2', X_Env::routeLink(X_Env::routeLink('plugin', 'exec', array('plg' => $this->options->id, 'linkId' => '2')))));
		*/
		
		$plx = new X_Plx('VLCShares - '.X_Env::_('megavideo_library'), X_Env::_("title_description"));
		
		$genConfigs = new Zend_Config_Ini(X_VlcShares::config());
		$vlc = new X_Vlc($genConfigs->vlc);
		if ( $vlc->isRunning() ) {
			$action = 'controls';
		}
		
		switch( $action ) {
			case 'stream':
				if ( $id != '' ) {
					$this->showStream($id, $streamType, $vlc, $plx);
					break;
				}
			case 'show':
				$this->_triggerTraversalPre($plx);
				if ( $id != '' ) {
					$this->showVideo($id, $plx);
				} elseif ( $category != '') {
					$this->showCategory($category, $plx);
				} else {
					$this->showCategories($plx);
				}
				$this->_triggerTraversalPost($plx);
				break;
			case 'controls':
				$this->showControls($id, $cAction, $seekTime, $vlc, $plx);
				break;
				
		}
		
		return $plx;
		
	}
	
	/**
	 * 
	 * @param int $id
	 * @param X_Plx $plx
	 */
	public function showVideo($id, $plx) {
		$mapper = new Application_Model_MegavideoMapper();
		$megavideo = new Application_Model_Megavideo();
		$mapper->find($id, $megavideo);
		
		if ( $megavideo->getId() == $id ) {
			// devo indicare se visualizzare in transcoding
			// o normale
			$paramsBase = 'plg:'.$this->getId();
			/*
			$plx->addItem(new X_Plx_Item(X_Env::_('megavideo_stream_transcoded'),
					X_Env::routeLink('plugin', 'exec', array(
						'id' => $this->getId(),
						"$paramsBase:id" => $megavideo->getId(),
						"$paramsBase:action" => 'stream',
						"$paramsBase:streamType" => 'transcode'
					))
				));
			*/
			$plx->addItem(new X_Plx_Item(X_Env::_('megavideo_stream_direct'),
					X_Env::routeLink('plugin', 'exec', array(
						'id' => $this->getId(),
						"$paramsBase:id" => $megavideo->getId(),
						"$paramsBase:action" => 'stream',
						"$paramsBase:streamType" => 'direct'
					))
				));
		} else {
			$plx->addItem(new X_Plx_Item(X_Env::_('megavideo_id_not_found'),
					X_Env::routeLink('plugin', 'exec', array(
						'id' => $this->getId(),
					))
				));
		}
	}
	
	/**
	 * 
	 * @param X_Plx $plx
	 */
	public function showCategories($plx) {
		
		$mapper = new Application_Model_MegavideoMapper();
		$categories = $mapper->fetchCategories();
		
		if ( count($categories) ) {
			$paramsBase = 'plg:'.$this->getId();
			foreach( $categories as $category) {
				$plx->addItem(new X_Plx_Item($category['category'].' ('.$category['links'].')',
						X_Env::routeLink('plugin', 'exec', array(
							'id' => $this->getId(),
							"$paramsBase:action" => 'show',
							"$paramsBase:category" => $category['category'],
						))
					));
			}
		} else {
			$plx->addItem(new X_Plx_Item(X_Env::_('megavideo_no_categories'),
					X_Env::routeLink('index', 'collections')
				));
		}
				
	}

	/**
	 * 
	 * @param $id
	 * @param $streamType
	 * @param X_Vlc $vlc
	 * @param X_Plx $plx
	 */
	public function showStream($id, $streamType, $vlc, $plx) {
		
		$mapper = new Application_Model_MegavideoMapper();
		$megavideo = new Application_Model_Megavideo();
		$mapper->find($id, $megavideo);
		
		if ( $megavideo->getId() == $id ) {
			
			$adapter = new Megavideo($megavideo->getIdVideo());
			$flvUrl = $adapter->get('URL');
			
			if ( $streamType == 'transcode' ) {
				// avvio vlc
				$profile = $this->options->get('transcode', new Zend_Config(array()))->get('args', 'transcode{venc=ffmpeg,vcodec=mp2v,vb=4000,scale=.5,width=640,fps=30,acodec=a52,ab=384,channels=6,samplerate=48000,soverlay}:std{access=http,mux=ts,dst=:8081}');
				$stream = $this->options->get('transcode', new Zend_Config(array()))->get('stream', 'http://'.$_SERVER['SERVER_ADDR'].':8081');
				
				$vlc->registerArg('source', "\"$flvUrl\"")
					->registerArg('profile', $profile)
					->spawn();
				
				$plx->addItem(new X_Plx_Item(
					X_Env::_('start_play'),$stream,X_Plx_Item::TYPE_VIDEO
				));
			} else {
				// link diretto
				$plx->addItem(new X_Plx_Item(
					X_Env::_('start_play'),$flvUrl,X_Plx_Item::TYPE_VIDEO
				));
			}
		}
		
		
	}

	/**
	 * 
	 * @param X_Plx $plx
	 */
	public function showCategory($category, $plx) {
		
		$mapper = new Application_Model_MegavideoMapper();
		$links = $mapper->fetchByCategory($category);
		
		if ( count($links) ) {
			$paramsBase = 'plg:'.$this->getId();
			foreach( $links as $link) {
				$plx->addItem(new X_Plx_Item($link->getLabel(),
						X_Env::routeLink('plugin', 'exec', array(
							'id' => $this->getId(),
							"$paramsBase:action" => 'show',
							"$paramsBase:id" => $link->getId(),
						))
					));
			}
		} else {
			$plx->addItem(new X_Plx_Item(X_Env::_('megavideo_no_links'),
					X_Env::routeLink('plugin', 'exec', array(
						'id' => $this->getId()
					))
				));
		}
	}
	
	/**
	 * 
	 * @param int $id
	 * @param string $cAction
	 * @param string $seekTime
	 * @param X_Vlc $vlc
	 * @param X_Plx $plx
	 */
	public function showControls($id, $cAction, $seekTime, $vlc, $plx) {
		$mapper = new Application_Model_MegavideoMapper();
		$megavideo = new Application_Model_Megavideo();
		$mapper->find($id, $megavideo);
		$paramsBase = 'plg:'.$this->getId();

		
		switch ($cAction) {
			case 'stop':
				$vlc->forceKill();
				$plx->addItem(new X_Plx_Item(
					X_Env::_('megavideo_back_to_categories'),
					X_Env::routeLink('plugin', 'exec', array(
						'id' => $this->getId(),
						"$paramsBase:action" => 'show',
					))
				));
				$plx->addItem(new X_Plx_Item(
					X_Env::_('megavideo_back_to_category').': '. $megavideo->getCategory(),
					X_Env::routeLink('plugin', 'exec', array(
						'id' => $this->getId(),
						"$paramsBase:action" => 'show',
						"$paramsBase:category" => $megavideo->getCategory(),
					))
				));
				return;
			case 'seek':
				if ( is_numeric($seekTime) ) {
					X_Env::debug("Seeking to minute: $seekTime");
					$seekTime = $seekTime * 60;
					$vlc->seek($seekTime, false);
				} else {
					X_Env::debug("is_numeric check failed: $seekTime");
				}
				break;
			case 'pause':
				$vlc->pause();
			default:
		}
		// visualizza info sul file
		
		$stream = new Megavideo($megavideo->getIdVideo());
		
		$plx->addItem(new X_Plx_Item(
			X_Env::_('on_air').': '.$stream->get('TITLE'),
			X_Env::routeLink('plugin', 'exec', array(
				'id' => $this->getId(),
				"$paramsBase:action" => 'controls',
				"$paramsBase:id" => $megavideo->getId(),
			))
		));

		$plx->addItem(new X_Plx_Item(
			X_Env::_('filename').$vlc->getCurrentName(),
			X_Env::routeLink('plugin', 'exec', array(
				'id' => $this->getId(),
				"$paramsBase:action" => 'controls',
				"$paramsBase:id" => $megavideo->getId(),
			))
		));

		$currentTime = X_Env::formatTime($vlc->getCurrentTime());
		$totalTime = $stream->get('DURATION');
		
		// mostra 000000/0000000
		$plx->addItem(new X_Plx_Item(
			"$currentTime/$totalTime",
			X_Env::routeLink('plugin', 'exec', array(
				'id' => $this->getId(),
				"$paramsBase:action" => 'controls',
				"$paramsBase:id" => $megavideo->getId(),
			))
		));
		
		// pause button
		$plx->addItem(new X_Plx_Item(
			X_Env::_('pause'),
			X_Env::routeLink('plugin', 'exec', array(
				'id' => $this->getId(),
				"$paramsBase:action" => 'controls',
				"$paramsBase:cAction" => 'pause',
				"$paramsBase:id" => $megavideo->getId(),
			))
		));
		
		// pause button
		$plx->addItem(new X_Plx_Item(
			X_Env::_('stop'),
			X_Env::routeLink('plugin', 'exec', array(
				'id' => $this->getId(),
				"$paramsBase:action" => 'controls',
				"$paramsBase:cAction" => 'stop',
				"$paramsBase:id" => $megavideo->getId(),
			))
		));
		
		// seek to minute button
		// per il momento disabilito
		/*
		$plx->addItem(new X_Plx_Item(
			X_Env::_('seek_to_minutes'),
			X_Env::routeLink('plugin', 'exec', array(
				'id' => $this->getId(),
				"$paramsBase:action" => 'controls',
				"$paramsBase:id" => $megavideo->getId(),
				"$paramsBase:cAction" => 'seek',
				"$paramsBase:minute" => ''
			)),
			X_Plx_Item::TYPE_SEARCH
		));
		*/
		
		
	}
	
	private function _triggerTraversalPre($plx) {
		$prePlxItems = X_Env::triggerEvent(X_VlcShares::TRG_DIR_TRAVERSAL_PRE);
		foreach ( $prePlxItems as $plgOutput ) {
			if ( is_array($plgOutput) ) {
				foreach ($plgOutput as $item ) {
					$plx->addItem($item);
				}
			} elseif ($plgOutput instanceof X_Plx_Item ) { 
				$plx->addItem($plgOutput);
			}
		}
		
	}

	private function _triggerTraversalPost($plx) {
		$postPlxItems = X_Env::triggerEvent(X_VlcShares::TRG_DIR_TRAVERSAL_POST);
		foreach ( $postPlxItems as $plgOutput ) {
			if ( is_array($plgOutput) ) {
				foreach ($plgOutput as $item ) {
					$plx->addItem($item);
				}
			} elseif ($plgOutput instanceof X_Plx_Item ) { 
				$plx->addItem($plgOutput);
			}
		}
	}
}
