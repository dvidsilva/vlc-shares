<?php 
require_once 'X/VlcShares.php';
require_once 'X/VlcShares/Plugins/Abstract.php';
require_once 'Zend/Config.php';
require_once 'Zend/Controller/Request/Abstract.php';
require_once 'X/VlcShares/Plugins/BackuppableInterface.php';

class X_VlcShares_Plugins_Outputs extends X_VlcShares_Plugins_Abstract implements X_VlcShares_Plugins_BackuppableInterface {

	public function __construct() {
		
		$this->setPriority('postRegisterVlcArgs', 100) 
			->setPriority('getStreamItems')
			->setPriority('getModeItems')
			->setPriority('preGetSelectionItems')
			->setPriority('getSelectionItems')
			//->setPriority('preSpawnVlc', 100) // TODO remove this. Only in dev env
			->setPriority('preGetControlItems', 1)
			->setPriority('getIndexManageLinks')
			;
		
	}	

	/**
	 * Give back the link for change modes
	 * and the default config for this location
	 * 
	 * @param string $provider
	 * @param string $location
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function getModeItems($provider, $location, Zend_Controller_Action $controller) {
		
		X_Debug::i('Plugin triggered');
		
		$urlHelper = $controller->getHelper('url');

		$outputLabel = X_Env::_('p_outputs_selection_auto');

		$outputId = $controller->getRequest()->getParam($this->getId(), false);
		
		// search for the output in db
		if ( $outputId !== false ) {
			$output = new Application_Model_Output();
			Application_Model_OutputsMapper::i()->find($outputId, $output);
			if ( $output->getId() !== null ) {
				$outputLabel = $output->getLabel();
			}
		}
		
		$link = new X_Page_Item_PItem($this->getId(), X_Env::_('p_outputs_output').": $outputLabel");
		$link->setIcon('/images/outputs/logo.png')
			->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setLink(array(
					'action'	=>	'selection',
					'pid'		=>	$this->getId()
				), 'default', false);

		return new X_Page_ItemList_PItem(array($link));
		
	}
	
	/**
	 * Show the title in selection page if pid is this id
	 * @param unknown_type $provider
	 * @param unknown_type $location
	 * @param unknown_type $pid
	 * @param Zend_Controller_Action $controller
	 */
	public function preGetSelectionItems($provider, $location, $pid, Zend_Controller_Action $controller) {
		// we want to expose items only if pid is this plugin
		if ( $this->getId() != $pid) return;
			
		X_Debug::i('Plugin triggered');		
		
		$urlHelper = $controller->getHelper('url');
		
		$link = new X_Page_Item_PItem($this->getId().'-header', X_Env::_('p_outputs_selection_title'));
		$link->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setLink(X_Env::completeUrl($urlHelper->url()));
		return new X_Page_ItemList_PItem();
	}
	
	/**
	 * Show plugin options
	 * @param unknown_type $provider
	 * @param unknown_type $location
	 * @param unknown_type $pid
	 * @param Zend_Controller_Action $controller
	 */
	public function getSelectionItems($provider, $location, $pid, Zend_Controller_Action $controller) {
		// we want to expose items only if pid is this plugin
		if ( $this->getId() != $pid) return;
		
		X_Debug::i('Plugin triggered');
		
		$urlHelper = $controller->getHelper('url');
		
		// i try to mark current selected sub based on $this->getId() param
		// in $currentSub i get the name of the current profile
		$currentOutput = $controller->getRequest()->getParam($this->getId(), false);

		$return = new X_Page_ItemList_PItem();
		$item = new X_Page_Item_PItem($this->getId().'-auto', X_Env::_('p_outputs_selection_auto'));
		$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setLink(array(
					'action'	=>	'mode',
					$this->getId() => null, // unset this plugin selection
					'pid'		=>	null
				), 'default', false)
			->setHighlight($currentOutput === false);
		$return->append($item);
		
		
		// check for infile subs
		$outputs = Application_Model_OutputsMapper::i()->fetchByConds($this->helpers()->devices()->getDeviceType());
		
		if ( count($outputs) ) {
			X_Debug::i("Valid outputs for device {$this->helpers()->devices()->getDeviceType()}: ".count($outputs));
		} else {
			X_Debug::e("No valid profiles for device {$this->helpers()->devices()->getDeviceType()}: i need at least an output");
		}
		
		// the best is the first
		foreach ($outputs as $output) {
			/* @var $profile Application_Model_Output */
			X_Debug::i("Valid output: [{$output->getId()}] {$output->getLabel()} ({$output->getCondDevices()})");
			$item = new X_Page_Item_PItem($this->getId().'-'.$output->getId(), $output->getLabel());
			$item->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setCustom(__CLASS__.':output', $output->getId())
				->setLink(array(
						'action'	=>	'mode',
						'pid'		=>	null,
						$this->getId() => $output->getId() // set this plugin selection as profileId
					), 'default', false)
				->setHighlight($currentOutput == $output->getId());
			$return->append($item);
		}
	
	
		
		// general profiles are in the bottom of array
		return $return;
	}
	
	/**
	 * Return the link -go-to-stream-
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to stream
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return X_Page_ItemList_PItem 
	 */
	public function getStreamItems($provider, $location, Zend_Controller_Action $controller) {
		
		X_Debug::i('Plugin triggered');
		
		$outputId = $controller->getRequest()->getParam($this->getId(), false);
		$urlHelper = $controller->getHelper('url');
		
		$output = new Application_Model_Output();
		// i store the default link, so if i don't find the proper output
		// i will have a valid link for -go-to-stream- button
		//$output->setLink($this->config('default.link', "http://{$_SERVER['SERVER_ADDR']}:8081"));
		
		if ( $outputId !== false ) {
			Application_Model_OutputsMapper::i()->find($outputId, $output);
		} else {
			// if no params is provided, i will try to
			// get the best output for this condition
			$output = $this->getBest($this->helpers()->devices()->getDeviceType());
		}
		
		
		$outputLink = $output->getLink();
		$outputLink = str_replace(
			array(
				'{%SERVER_IP%}',
				'{%SERVER_NAME%}'
			),array(
				$_SERVER['SERVER_ADDR'],
				$_SERVER['HTTP_HOST']
			), $outputLink
		);
		
		$item = new X_Page_Item_PItem($this->getId(), X_Env::_('p_outputs_gotostream'));
		$item->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
			->setIcon('/images/icons/play.png')
			->setLink($outputLink);
		return new X_Page_ItemList_PItem(array($item));
		
	}
	
	/**
	 * Add output arg in vlc args
	 * 
	 * @param X_Vlc $vlc vlc wrapper object
	 * @param string $provider id of the plugin that should handle request
	 * @param string $location to stream
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 */
	public function postRegisterVlcArgs(X_Vlc $vlc, $provider, $location, Zend_Controller_Action $controller) {
		
		X_Debug::i('Plugin triggered');
		
		$outputId = $controller->getRequest()->getParam($this->getId(), false);
		
		if ( $outputId !== false ) {
			$output = new Application_Model_Output();
			Application_Model_OutputsMapper::i()->find($outputId, $output);
		} else {
			// if no params is provided, i will try to
			// get the best output for this condition
			$output = $this->getBest($this->helpers()->devices()->getDeviceType());
		}
		
		if ( $output->getArg() !== null ) {

			$vlc->registerArg('output', $output->getArg());			
			
			if ( $this->config('store.session', false) ) {
				// store the link in session for future use
			}
			
		} else {
			X_Debug::e("No output arg for vlc");
		}
	}
	
	/**
	 * Print in debug all the parameter of vlc
	 * This will be moved to advaced debug plugin
	 * @param X_Vlc $vlc
	 * @param unknown_type $provider
	 * @param unknown_type $location
	 * @param Zend_Controller_Action $controller
	 */
	public function preSpawnVlc(X_Vlc $vlc, $provider, $location, Zend_Controller_Action $controller) {
		X_Debug::i(var_export($vlc->getArgs(), true));
	}
	
	/**
	 * Add the button BackToStream in controls page
	 * 
	 * @param Zend_Controller_Action $controller the controller who handle the request
	 * @return array
	 */
	public function preGetControlItems(Zend_Controller_Action $controller) {
		
		X_Debug::i('Plugin triggered');
		
		$outputId = $controller->getRequest()->getParam($this->getId(), false);
		$urlHelper = $controller->getHelper('url');
		
		$output = new Application_Model_Output();
		// i store the default link, so if i don't find the proper output
		// i will have a valid link for -go-to-stream- button
		//$output->setLink($this->config('default.link', "http://{$_SERVER['SERVER_ADDR']}:8081"));
		
		if ( $outputId !== false ) {
			Application_Model_OutputsMapper::i()->find($outputId, $output);
		} else {
			// if store session is enabled, i try to get last output
			// method from store
			// else i fallback to best selection
			if ( $this->config('store.session', false) ) {
				// TODO handle store.session = true params
				$output = $this->getBest($this->helpers()->devices()->getDeviceType()); // FIXME remove this
			} else {
				$output = $this->getBest($this->helpers()->devices()->getDeviceType());
			}
		}
		
		
		$outputLink = $output->getLink();
		$outputLink = str_replace(
			array(
				'{%SERVER_IP%}',
				'{%SERVER_NAME%}'
			),array(
				$_SERVER['SERVER_ADDR'],
				$_SERVER['HTTP_HOST']
			), $outputLink
		);
		
		return array(
			array(
				'label'	=>	X_Env::_('p_outputs_backstream'),
				'link'	=>	$outputLink,
				'type'	=>	X_Plx_Item::TYPE_VIDEO,
				'icon'	=>	'/images/icons/play.png'
			)
		);
		
		
	}
	
	/**
	 * Add the link for -manage-output-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_outputs_mlink'));
		$link->setTitle(X_Env::_('p_outputs_managetitle'))
			->setIcon('/images/outputs/logo.png')
			->setLink(array(
					'controller'	=>	'outputs',
					'action'		=>	'index',
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
	}
	
	/**
	 * Find the best output type for the current device
	 * @param int $device
	 */
	private function getBest($device) {
		
		$output = new Application_Model_Output();
		Application_Model_OutputsMapper::i()->findBest($device, $output);
		return $output;
		
	}

	/**
	 * Backup profiles
	 * This is not a trigger of plugin API. It's called by Backupper plugin
	 */
	function getBackupItems() {
		
		$return = array();
		$models = Application_Model_OutputsMapper::i()->fetchAll();
		
		foreach ($models as $model) {
			/* @var $model Application_Model_Output */
			$return['outputs']['output-'.$model->getId()] = array(
				'id'			=> $model->getId(), 
	            'arg'   => $model->getArg(),
	        	'cond_devices' => $model->getCondDevices(),
	        	'label' => $model->getLabel(),
	        	'weight' => $model->getWeight(),
	        	'link' => $model->getLink()
			);
		}
		
		return $return;
	}
	
	/**
	 * Restore backupped profiles
	 * This is not a trigger of plugin API. It's called by Backupper plugin
	 */
	function restoreItems($items) {
		
		$models = Application_Model_OutputsMapper::i()->fetchAll();
		// cleaning up all shares
		foreach ($models as $model) {
			Application_Model_OutputsMapper::i()->delete($model);
		}
	
		foreach (@$items['outputs'] as $modelInfo) {
			$model = new Application_Model_Output();
			$model->setArg(@$modelInfo['arg'])
				->setLink(@$modelInfo['link'])
				->setCondDevices(@$modelInfo['cond_devices'] !== '' ? @$modelInfo['cond_devices'] : null)
				->setLabel(@$modelInfo['label'])
				->setWeight(@$modelInfo['weight'])
				;
			// i don't set id, or db adapter will try to update old data that i cleaned
			Application_Model_OutputsMapper::i()->save($model);
		}
		
		return X_Env::_('p_outputs_backupper_restoreditems'). ": " .count($items['outputs']);
		
		
	}
	
	
}