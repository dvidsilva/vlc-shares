<?php

abstract class X_VlcShares_Plugins_Utils {

	/**
	 * Util function for Collection Entry List (X_Page_Item_PItem) creation
	 * @param string $pluginId
	 * @param string $entryLabel
	 * @param string $entryDesc
	 * @param string $entryIcon
	 * @param string $entryThumb
	 */
	static function getCollectionsEntryList($pluginId, $entryLabel = null, $entryDesc = null, $entryIcon = null) {
		
		$entryLabel = self::isset_or($entryLabel, "p_{$pluginId}_collectionindex");
		$entryDesc = self::isset_or($entryDesc, "p_{$pluginId}_collectionindex_desc");
		$entryIcon = self::isset_or($entryIcon, "/images/{$pluginId}/logo.png");
		
		$link = new X_Page_Item_PItem($pluginId, X_Env::_($entryLabel));
		$link->setIcon($entryIcon)
			->setDescription(X_Env::_($entryDesc))
			->setType(X_Page_Item_PItem::TYPE_CONTAINER)
			->setLink(
				array(
					'controller' => 'browse',
					'action' => 'share',
					'p' => $pluginId,
				), 'default', true
			);
		return new X_Page_ItemList_PItem(array($link));
	}
	
	static function getIndexManageEntryList($pluginId, $entryLabel = null, $entryTitle = null, $entryIcon = null, $params = null) {
		
		$entryLabel = self::isset_or($entryLabel, "p_{$pluginId}_mlink");
		$entryTitle = self::isset_or($entryTitle, "p_{$pluginId}_managetitle");
		$entryIcon = self::isset_or($entryIcon, "/images/{$pluginId}/logo.png");
		$params = self::isset_or($params, array(
				'controller'	=>	'config',
				'action'		=>	'index',
				'key'			=>	$pluginId
		));
		
		$link = new X_Page_Item_ManageLink($pluginId, X_Env::_($entryLabel));
		$link->setTitle(X_Env::_($entryTitle))
		->setIcon($entryIcon)
		->setLink($params, 'default', true);
		return new X_Page_ItemList_ManageLink(array($link));
		
	}
	
	static function getMessageEntry($pluginId, $message, $type = X_Page_Item_Message::TYPE_INFO) {
		$message = new X_Page_Item_Message($pluginId, X_Env::_($message));
		$message->setType($type);
		return $message;
	}
	
	static function getWatchDirectlyOrFilter($pluginId, X_VlcShares_Plugins_ResolverInterface $plugin, $location ) {
		
		$url = $plugin->resolveLocation($location);
		
		if ( $url ) {
			// add watch directly link
			$link = new X_Page_Item_PItem('core-directwatch', X_Env::_('watchdirectly'));
			$link->setIcon('/images/icons/play.png')
			->setType(X_Page_Item_PItem::TYPE_PLAYABLE)
			->setLink($url);
		} else {
			// prepare for filter
			X_Debug::i('Setting priority to filterModeItems');
			$plugin->setPriority('filterModeItems', 99);
			
			$link = new X_Page_Item_PItem('core-invalidlink', X_Env::_('invalidlink'));
			$link->setIcon('/images/msg_error.png')
			->setType(X_Page_Item_PItem::TYPE_ELEMENT)
			->setLink(array (
					'controller' => 'browse',
					'action' => 'share',
					'p'	=> $pluginId,
					'l' => X_Env::encode($plugin->getParentLocation($location)),
			), 'default', true);
			
		}
		
		return new X_Page_ItemList_PItem(array($link));
	}
	
	
	static function getNextPage($location, $nextPage, $totalPages = '???') {
		$item = new X_Page_Item_PItem('nextpage', X_Env::_("nextpage", ($nextPage), $totalPages));
		$item//->setIcon('/images/icons/folder_32.png')
		->setType(X_Page_Item_PItem::TYPE_CONTAINER)
		->setCustom(__CLASS__.':location', $location)
		->setDescription(APPLICATION_ENV == 'development' ? $location : null)
		->setLink(array(
				'l'	=>	X_Env::encode($location)
				), 'default', false);
		return $item;
	}

	/**
	 * Prepare and return a -previous-page- item
	 * 
	 * @param string $location
	 * @param int $previousPage
	 * @param int $totalPages
	 */
	static function getPreviousPage($location, $previousPage, $totalPages = '???') {
		$item = new X_Page_Item_PItem('previouspage', X_Env::_("previouspage", ($previousPage), $totalPages));
		$item//->setIcon('/images/icons/folder_32.png')
		->setType(X_Page_Item_PItem::TYPE_CONTAINER)
		->setCustom(__CLASS__.':location', $location)
		->setDescription(APPLICATION_ENV == 'development' ? $location : null)
		->setLink(array(
				'l'	=>	X_Env::encode($location)
		), 'default', false);
		return $item;
	}
	
	static function registerVlcLocation(X_VlcShares_Plugins_ResolverInterface $plugin, X_Vlc $vlc, $location) {
		$location = $plugin->resolveLocation($location);
		if ( $location !== null ) {
			$vlc->registerArg('source', "\"$location\"");
		} else {
			X_Debug::e("No source o_O");
		}
	}

	/**
	 * Call a $menusContainer method following $nodeDefinitions and the current $location provided
	 * 
	 * @param X_Page_ItemList_PItem $items
	 * @param string $location
	 * @param array $nodesDefinitions
	 * @param object $menusContainer
	 * @return X_Page_ItemList_PItem
	 * @throws Exception
	 */
	static function menuProxy(X_Page_ItemList_PItem $items, $location = '', $nodesDefinitions = array(), $menusContainer ) {
		if ( !is_object($menusContainer) ) throw new Exception(sprintf("\$menuContainer must be an object, %s given", gettype($menusContainer)));
		
		X_Debug::i("Searching node handler for: $location");
		foreach ($nodesDefinitions as $nodeSign => $handler) {
			@list($matchType, $matchValue) = @explode(':', $nodeSign, 2);
			$validHandler = false;
			$method = false;
			$params = array($items);
			$matches = array();
			switch ($matchType) {
				case 'exact':
					$validHandler = ($matchValue == $location);
					$method = $handler['function'];
					$params = array_merge($params,$handler['params']);
					break;
		
				case 'regex':
					$validHandler = (preg_match($matchValue, $location, $matches) > 0);
					$method = $handler['function'];
					// optimization: continue only if $validHandler
					if ( $validHandler ) {
						foreach ($handler['params'] as $placeholder) {
							// if placehoster start with $ char, it's a placehoster from regex
							if ( X_Env::startWith($placeholder, "$") ) {
								// remove the $ char from the beginning of $placehoster
								$placeholder = substr($placeholder, 1);
								$params[] = array_key_exists($placeholder, $matches) ? $matches[$placeholder] : null;
							} else {
								$params[] = $placeholder;
							}
						}
					}
					break;
			}
			// stop loop on first validHandler found
			if ( $validHandler ) { X_Debug::i(sprintf("Matching NodeSign: %s", $nodeSign)); break;	}
		}
		
		if ( !$validHandler ) throw new Exception("Provider can't handle this node location: $location");
		if ( !method_exists($menusContainer, $method) ) throw new Exception(sprintf("Invalid node handler: %s::%s() => %s", get_class($menusContainer), $method, htmlentities($nodeSign)));
		$reflectionMethod = new ReflectionMethod($menusContainer, $method);
		if ( !$reflectionMethod->isPublic() ) throw new Exception(sprintf("Menu method must be public: ",  get_class($menusContainer), $method));
		
		X_Debug::i(sprintf("Calling menu function {%s::%s(%s)}", get_class($menusContainer), $method, print_r($params, true) ));
		
		// $items is always the first params item (index 0)
		call_user_func_array(array($menusContainer, $method), $params);
		
		return $items;
		
	}
	
	/**
	 * Fill an X_Page_ItemList_PItem object ($items) with a 
	 * simple list of static values
	 * @param X_Page_ItemList_PItem $items
	 * @param array $menuEntries an array of menu entries, keys are locations and values are label keys 
	 * @param string $keyPrefix a string prepended to all items key 
	 * @param string $locationPrefix a string prepended to all items location
	 * @return X_Page_ItemList_PItem
	 */
	static function fillStaticMenu(X_Page_ItemList_PItem $items, $menuEntries = array(), $keyPrefix = '', $locationPrefix = '') {
		foreach ( $menuEntries as $location => $labelKey ) {
			$entry = new X_Page_Item_PItem("{$keyPrefix}-$location", X_Env::_($labelKey));
			$entry->setDescription(APPLICATION_ENV == 'development' ? "{$locationPrefix}{$location}" : null);
			$entry->setGenerator(__METHOD__)
				->setIcon('/images/icons/folder_32.png')
				->setCustom(__METHOD__.":location", "{$locationPrefix}{$location}")
				->setType(X_Page_Item_PItem::TYPE_CONTAINER)
				->setLink(array(
						'l' => X_Env::encode("{$locationPrefix}{$location}")
					), 'default', false);
			$items->append($entry);
		}
		return $items;
	}
	
	static function isset_or($value, $alternative) {
		return (isset($value) ? $value : $alternative);
	}
	
}
