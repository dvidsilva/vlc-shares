<?php

/**
 * Plugins that give browse provider services should
 * implement this interface.
 * Other plugin can use this interface for get
 * real location of a resource
 * @author ximarx
 *
 */
interface X_VlcShares_Plugins_ResolverInterface {
	
	/**
	 * Get the real location of a resource from
	 * $location param
	 * @param string $location
	 * @return string resource real location
	 */
	function resolveLocation($location);
	/**
	 * Get the parent location string of the current location
	 * value
	 * 
	 * @param $location
	 * @return string resource parent. NULL if there is no parent, false if location is invalid
	 */
	function getParentLocation($location);
	
}

