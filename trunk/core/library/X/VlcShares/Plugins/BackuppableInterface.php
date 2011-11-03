<?php

/**
 * Plugins which need backup/restore services should implement
 * this interface
 * @author ximarx
 *
 */
interface X_VlcShares_Plugins_BackuppableInterface {
	
	/**
	 * Return an array of key=>value items
	 * that will be backuped
	 * 
	 * @return array of associative arrays
	 */
	function getBackupItems();
	/**
	 * Restore backuped items
	 * 
	 * @param array $items the list of items (the same format of getBackupItems())
	 * @return boolean
	 */
	function restoreItems($items);
	
}

