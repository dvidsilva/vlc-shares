<?php

require_once 'X/Page/ItemList.php';
require_once 'X/Page/Item/Statistic.php';

/** 
 * @author ximarx
 * 
 * 
 */
class X_Page_ItemList_Statistic extends X_Page_ItemList {
	
	/**
	 * Create a new List of items
	 * @param array of X_Page_Item_Statistic that will be added to the list
	 */
	public function __construct($items = array()) {
		if ( is_array($items) ) {
			foreach ($items as $item) {
				if ($item instanceof X_Page_Item_Statistic) {
					$this->append($item);
				}
			}
		} else {
			throw new Exception('Only arrays of X_Page_Item_Statistic are allowed as param');
		}
	}

	/**
	 * Append a new item to the list
	 * @param X_Page_Item_Statistic $item
	 * @return X_Page_ItemList_Statistic
	 */
	public function append(X_Page_Item $item) {
		if ( !($item instanceof X_Page_Item_Statistic ) ) return $this;
		return parent::append($item);
	}
	
	/**
	 * Replace an old item with key $key with $item
	 * @param string|X_Page_Item_Statistic $key
	 * @param X_Page_Item_Statistic $item
	 * @return X_Page_ItemList_Statistic 
	 */
	public function replace($key, X_Page_Item $item) {
		if ( !($item instanceof X_Page_Item_Statistic ) ) return $this;
		return parent::replace($key, $item);
	}
	
	/**
	 * Return the items
	 * @return array of X_Page_Item_Statistic
	 */
	public function getItems() {
		return $this->items;
	}
	
	/**
	 * Get an item by $key
	 * @param string $key
	 * @return X_Page_ItemList_Statistic
	 * @throws Exception if $key isn't in the list
	 */
	public function getItem($key) {
		return parent::getItem($key);
	}
	
	/**
	 * Merge a list with this one
	 * @param X_Page_ItemList_Statistic $list
	 * @return X_Page_ItemList_Statistic
	 */
	public function merge(X_Page_ItemList $list = null) {
		if ( !($list instanceof X_Page_ItemList_Statistic ) || $list == null ) return $this;
		return parent::merge($list);
	}
	
	/**
	 * @see X_Page_ItemList::remove
	 * @return X_Page_ItemList_Statistic
	 */
	public function remove($item) {
		return parent::remove($item);
	}
	
}

