<?php

/**
 * 
 */
interface X_VlcShares_Plugins_Helper_HostInterface {
	
	const E_URL_INVALID = 1;
	const E_ID_INVALID = 2;
	const E_ID_NOTFOUND = 3;
	const E_QUOTA_NOMORE = 99;
	
	/**
	 * give the hoster id
	 * @return string
	 */
	function getId();
	/**
	 * get the hoster pattern for regex match
	 * @return string
	 */
	function getPattern();
	/**
	 * get the resource ID for the hoster
	 * from an $url
	 * @param string $url the hoster page
	 * @return string the resource id
	 * @throws Exception if id not found
	 */
	function getResourceId($url);
	/**
	 * get a playable resource url
	 * from an $url (or a resource id if $isId = true)
	 * @param string $url the hoster page or resource ID
	 * @param boolean $isId
	 * @return string a playable url
	 * @throws Exception if resource not found
	 */
	function getPlayable($url, $isId = true);
	
	/**
	 * get an array with standard information about the playable
	 * @param string $url the hoster page or resource ID
	 * @param boolean $isId
	 * @return array format:
	 * 		array(
	 * 			'title' => TITLE
	 * 			'description' => DESCRIPTION
	 * 			'length' => LENGTH
	 * 			'thumbnail' => THUMBNAIL URL
	 * 			...
	 * 		)
	 * @throws Exception if resource not found
	 */
	function getPlayableInfos($url, $isId = true);
	
	/**
	 * get a hoster site page url from the $playableId
	 * @param string $playableId
	 * @return string
	 */
	function getHosterUrl($playableId);
	
}

