<?php

/**
 * Plugins that give browse provider services should
 * implement this interface.
 * Other plugin can use this interface for get
 * real location of a resource
 * @author ximarx
 *
 */
interface X_VlcShares_Plugins_ResolverParentInterface {
	
	/**
	 * Get the parent location of a current
	 * $location param
	 * @param string $location
	 * @return string parent location string
	 */
	function getParentLocation($location);
	
}

