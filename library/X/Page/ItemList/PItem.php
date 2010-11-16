<?php

require_once 'X/Page/ItemList.php';
require_once 'X/Page/Item/PItem.php';

/** 
 * @author ximarx
 * 
 * 
 */
class X_Page_ItemList_PItem extends X_Page_ItemList {
	
	/**
	 * Create a new List of items
	 * @param array of X_Page_Item_PItem that will be added to the list
	 */
	public function __construct($items = array()) {
		if ( is_array($items) ) {
			foreach ($items as $item) {
				if ($item instanceof X_Page_Item_PItem) {
					$this->append($item);
				}
			}
		} else {
			throw new Exception('Only arrays of X_Page_Item_PItem are allowed as param');
		}
	}

	/**
	 * Append a new item to the list
	 * @param X_Page_Item_PItem $item
	 * @return X_Page_ItemList_PItem
	 */
	public function append(X_Page_Item_PItem $item) {
		$this->items[] = $item;
		return $this;
	}
	
	/**
	 * Replace an old item with key $key with $item
	 * @param string|X_Page_Item $key
	 * @param X_Page_Item_PItem $item
	 * @return X_Page_ItemList_PItem 
	 */
	public function replace($key, X_Page_Item_PItem $item) {
		if ( $key instanceof X_Page_Item_PItem) {
			$key = $key->getKey();
		} else {
			$key = (string) $key;
		}
		foreach ( $this->getItems() as $k => $i ) {
			if ( $i->getKey() == $key ) {
				$this->items[$k] = $item;
				return $this;
			}
		}
		// if there is no key = $key, i simply append the $item
		$this->append($item);
		return $this;
	}
	
	/**
	 * Return the items
	 * @return array of X_Page_Item_PItem
	 */
	public function getItems() {
		return $this->items;
	}
	
	/**
	 * Get an item by $key
	 * @param string $key
	 * @return X_Page_ItemList_PItem
	 * @throws Exception if $key isn't in the list
	 */
	public function getItem($key) {
		foreach ($this->getItems() as $item) {
			if ( $item->getKey() == $key) {
				return $item;
			}
		}
		throw new Exception("No item with key '$key' in the list");
	}
	
	/**
	 * Merge a list with this one
	 * @param X_Page_ItemList_PItem $list
	 * @return X_Page_ItemList_PItem
	 */
	public function merge(X_Page_ItemList_PItem $list = null) {
		if ( $list == null ) return $this;
		foreach ($list->getItems() as $item) {
			$this->append($item);
		}
		return $this;
	}
	
	/**
	 * @see X_Page_ItemList::remove
	 * @return X_Page_ItemList_PItem
	 */
	public function remove($item) {
		return parent::remove($item);
	}
	
}

