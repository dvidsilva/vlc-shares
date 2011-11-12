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
	
	static private function isset_or($value, $alternative) {
		return (isset($value) ? $value : $alternative);
	}
	
}
