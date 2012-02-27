<?php

/**
 * Check for CORE and plugins updates
 * @author ximarx
 *
 */
class X_VlcShares_Plugins_UpdateNotifier extends X_VlcShares_Plugins_Abstract {
	
	public function __construct() {
		$this
			->setPriority('preGetCollectionsItems', 0)
			->setPriority('getIndexManageLinks')
			->setPriority('prepareConfigElement')
			->setPriority('getIndexMessages', 0); // i add it near the top of the stack
	}
	
	/**
	 * Add the link for -update-settings-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {

		$link = new X_Page_Item_ManageLink($this->getId(), X_Env::_('p_updatenotifier_mlink'));
		$link->setTitle(X_Env::_('p_updatenotifier_managetitle'))
			->setIcon('/images/updatenotifier/logo.png')
			->setLink(array(
					'controller'	=>	'config',
					'action'		=>	'index',
					'key'			=>	$this->getId()
			), 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}
	
	
	/**
	 * Return the -shared-folders- link
	 * for the collection index
	 * @param Zend_Controller_Action $controller
	 * @return X_Page_ItemList_PItem
	 */
	public function preGetCollectionsItems(Zend_Controller_Action $controller) {
		
		X_Debug::i("Plugin triggered");
		
		$updates = $this->checkUpdates();
		
		/* @var $urlHelper Zend_Controller_Action_Helper_Url */
		$urlHelper = $controller->getHelper('url');
		
		$list = new X_Page_ItemList_PItem();
		
		if ( $updates['core'] !== false ) {
			$link = new X_Page_Item_PItem("{$this->getId()}-coreupdate", X_Env::_('p_updatenotifier_collectionindex_core'));
			$link->setIcon('/images/updatenotifier/logo.png')
				->setDescription(X_Env::_('p_updatenotifier_collectionindex_core_desc'))
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink($urlHelper->url());
				
			$list->append($link);
		}
		
		if ( count($updates['plugins']) ) {
			$link = new X_Page_Item_PItem("{$this->getId()}-pluginsupdate", X_Env::_('p_updatenotifier_collectionindex_plugins', count($updates['plugins'])));
			$link->setIcon('/images/updatenotifier/logo.png')
				->setDescription(X_Env::_('p_updatenotifier_collectionindex_plugins_desc'))
				->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setLink($urlHelper->url());
				
			$list->append($link);
		}
			
		return (count($list->getItems()) ? $list : null);
	}
	
	
	/**
	 * Retrieve statistic from plugins
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Message
	 */
	public function getIndexMessages(Zend_Controller_Action $controller) {
		
		$updates = $this->checkUpdates();
		
		$list = new X_Page_ItemList_Message();
		
		if ( $updates['core'] !== false ) {
			
			$m = new X_Page_Item_Message("{$this->getId()}-core", X_Env::_('p_updatenotifier_coreupdate',
				 $updates['core']['version'],
				 $updates['core']['type'],
				 trim($updates['core']['changelog']),
				 $updates['core']['download'],
				 $updates['core']['update']  
			));
			$m->setType(X_Page_Item_Message::TYPE_INFO);
			$list->append($m);
		}
		
		if ( count($updates['plugins']) ) {
			
			foreach ($updates['plugins'] as $key => $infos ) {

				$m = new X_Page_Item_Message("{$this->getId()}-plugin", X_Env::_('p_updatenotifier_pluginupdate',
					$key,
					$infos['version'],
					$infos['type'],
					trim($infos['changelog']),
					$infos['download'],
					$infos['update']  
				));
				$m->setType(X_Page_Item_Message::TYPE_INFO);
				$list->append($m);
			}
		}
		
		return ( count($list->getItems()) ? $list : null );
		
		
	}
	
	/**
	 * Remove cookie.jar if configs change and convert form password to password element
	 * @param string $section
	 * @param string $namespace
	 * @param unknown_type $key
	 * @param Zend_Form_Element $element
	 * @param Zend_Form $form
	 * @param Zend_Controller_Action $controller
	 */
	public function prepareConfigElement($section, $namespace, $key, Zend_Form_Element $element, Zend_Form  $form, Zend_Controller_Action $controller) {
		// nothing to do if this isn't the right section
		if ( $namespace != $this->getId() ) return;
		
		// remove cookie.jar if somethings has value
		if ( !$form->isErrors() && !is_null($element->getValue()) ) {
			$this->clearLastCheck();
		}
	}	
	
	
	protected function checkUpdates() {
		
		$requireUpdate = array('core' => false, 'plugins' => array());
		
		if ( !file_exists(APPLICATION_PATH.'/../data/updatenotifier/lastcheck.txt') || $this->config('autocheck.last', '') < time() - ($this->config('autocheck.delay', 3) * 24 * 60 * 60)  ) {
		
			
			$coreUnstableAllowed =  (bool) $this->config('core.allow.unstable', false);
			$pluginUnstableAllowed = (bool) $this->config('plugins.allow.unstable', false);
			
			X_Debug::i("Checking for updates. Core dev allowed: ".($coreUnstableAllowed ? 'Yes' : 'No').". Plugin dev allowed: ".($pluginUnstableAllowed ? 'Yes' : 'No')."." );
			
			$coreLast = $this->getLastCore($coreUnstableAllowed);
			
			//X_Debug::i("Last core infos: ".var_export($coreLast, true));
	
			if ( $coreLast !== false 
					&& (
						$coreLast['version'] != X_VlcShares::VERSION_CLEAN
						 || (
						 	$coreUnstableAllowed
						 		&& $coreLast['type'] != 'stable'
						 		&& $coreLast['version'].$coreLast['type'] != X_VlcShares::VERSION  
						)
					) 
				) {
					
				X_Debug::i("New core version available");
				$requireUpdate['core'] = $coreLast;
			}
			
			$pluginsLast = $this->getLastPlugins();
			
			//X_Debug::i("Last plugins infos: ".var_export($pluginsLast, true));
			
			foreach ($pluginsLast as $key => $versions) {
				
				// if plugin isn't registered or isn't installed, i don't check for updates 
				if ( !X_VlcShares_Plugins::broker()->isRegistered($key) ) continue;
				
				$pluginClass = X_VlcShares_Plugins::broker()->getPluginClass($key);
				
				// if plugin hasn't a VERSION constant, continue
				if ( !defined("$pluginClass::VERSION") ) continue;
				if ( !defined("$pluginClass::VERSION_CLEAN") ) continue;
				
				$currentVersion = constant("$pluginClass::VERSION");
				$currentVersionClean = constant("$pluginClass::VERSION_CLEAN");
				
				X_Debug::i("Checking $key: $currentVersionClean ($currentVersion)");
				
				foreach ($versions as $infos) {
					
					//X_Debug::i("Version available: ".var_export($infos, true));
					
					if ( $infos['type'] != 'stable' && !$pluginUnstableAllowed ) continue;
					
					// version isn't allowed if cMin <= CURRENT_VLCSHARES_VERSION < xMax
					if ( version_compare(X_VlcShares::VERSION_CLEAN, $infos['cMin'], '<')
						|| version_compare(X_VlcShares::VERSION_CLEAN, $infos['cMax'], '>=') ) {
	
						continue;
					}
	
					$versionCheck = $infos['version'] . ($infos['type'] != 'stable' ? $infos['type'] : '');
					
					//X_Debug::i("Last version available for $key: ".var_export($infos, true));
					
					if ( $infos['version'] != $currentVersionClean || $versionCheck != $currentVersion ) {
						X_Debug::i("New version available for $key: {$infos['version']}");
						$requireUpdate['plugins'][$key] = $infos;
					}
					
				}
				
			}
			
			@file_put_contents(APPLICATION_PATH.'/../data/updatenotifier/lastcheck.txt', serialize($requireUpdate));
			
			$c = new Application_Model_Config();
			Application_Model_ConfigsMapper::i()->fetchByKey($this->getId().'.autocheck.last', $c);
			if ( $c->getId() ) {
				$c->setValue(time());
				try {
					Application_Model_ConfigsMapper::i()->save($c);
				} catch (Exception $e) {
					X_Debug::e("Error while storing last check time in db");
				} 
			}
			
		} else {
			
			X_Debug::i("Reading last check cache");
			
			$requireUpdate = unserialize(file_get_contents(APPLICATION_PATH.'/../data/updatenotifier/lastcheck.txt'));
		}
		
		return $requireUpdate;
		
	}
	
	
	protected function getLastCore($unstable = false) {
		
		// leggo il valore dell'ultima versione
		
		try {
			if ( $unstable ) {
				$manifestUrl = $this->config('core.unstable.index', 'http://vlc-shares.googlecode.com/svn/updates/core/UNSTABLE.xml');
			} else {
				$manifestUrl = $this->config('core.stable.index', 'http://vlc-shares.googlecode.com/svn/updates/core/STABLE.xml');
			}
			
			$client = new Zend_Http_Client($manifestUrl, array(
				'maxredirects'	=> $this->config('request.maxredirects', 10),
				'timeout'		=> $this->config('request.timeout', 10)
			));
			
			$client->setHeaders(array(
				'User-Agent: vlc-shares/'.X_VlcShares::VERSION.' updatenotifier/'.X_VlcShares::VERSION
			));
			
			$response = $client->request();
			
			if ( $response->isError() ) {
				throw new Exception('Invalid manifest');
			}
			
			$xml = new SimpleXMLElement($response->getBody());
		
			
			/*
			<version name="VERSION">
				<update></update>
				<download></download>
				<changelog>
					<![CDATA[
					]]>
				</changelog>
			</version>
			 */
	
			$return = array(
				'version' => (string) $xml['name'],
				'type' => (string) $xml['type'],
				'update' => (string) $xml->update,
				'download' => (string) $xml->download,
				'changelog' => (string) $xml->changelog
			);
			
		} catch (Exception $e) {
			X_Debug::e($e->getMessage());
			$return = false;
		}
		return $return;
		
	}

	public function getLastPlugins() {
		
		// leggo il valore dell'ultima versione
		
		try {
		
			$client = new Zend_Http_Client($this->config('plugins.index', 'http://vlc-shares.googlecode.com/svn/updates/plugins/INDEX.xml'), array(
				'maxredirects'	=> $this->config('request.maxredirects', 10),
				'timeout'		=> $this->config('request.timeout', 10)
			));
			
			$client->setHeaders(array(
				'User-Agent: vlc-shares/'.X_VlcShares::VERSION.' updatenotifier/'.X_VlcShares::VERSION
			));
			
			$response = $client->request();
			
			if ( $response->isError() ) {
				throw new Exception('Invalid manifest');
			}
			
			$xml = new SimpleXMLElement($response->getBody());
						
			/*
			<plugins>
				<plugin key="PLUGINKEY">
					<version name="VERSION" type="TYPE" cMin="CORE_MINIMAL" cMax="CORE_MAXIMAL">
						<update></update>
						<download></download>
						<changelog>
							<![CDATA[
							]]>
						</changelog>
					</version>
					...
				</plugin>
				...
			</plugins>
			 */
	
			$return = array();
			
			foreach ($xml->plugin as $plugin) {
				
				$key = (string) $plugin['key'];
				
				$pluginArray = array();
				
				foreach ($plugin->version as $version) {
					$version = array(
						'version' => (string)  $version['name'],
						'type' => (string)  $version['type'],
						'cMin' => (string) $version['cMin'],
						'cMax' => (string) $version['cMax'],
						'update' => (string) $version->update,
						'download' => (string) $version->download,
						'changelog' => (string) $version->changelog,
						'description' => (string) $version->description,
						'thumbnail' => (string) $version->thumbnail,
					);
					
					$pluginArray[] = $version;
					
				}
				
				$return[$key] = $pluginArray;
				
			}
			
		} catch (Exception $e) {
			X_Debug::e($e->getMessage());
			$return = array();
		}
		
		return $return;
		
	}
	
	public function clearLastCheck() {
		
		X_Debug::i("Resetting last check");
		
		$c = new Application_Model_Config();
		Application_Model_ConfigsMapper::i()->fetchByKey($this->getId().'.autocheck.last', $c);
		if ( $c->getId() ) {
			$c->setValue('');
			try {
				Application_Model_ConfigsMapper::i()->save($c);
			} catch (Exception $e) {
				X_Debug::e("Error while storing last check time in db");
			}
		}
		
		@unlink(APPLICATION_PATH.'/../data/updatenotifier/lastcheck.txt');
		
	}
	
}
