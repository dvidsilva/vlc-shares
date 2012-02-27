<?php

/**
 * 
 */
interface X_VlcShares_Plugins_Helper_HostInterface {
	
	//{{{ URL EXCEPTION CODE FROM 0->9
	const E_URL_MIN = 0;
	const E_URL_INVALID = 1;
	const E_URL_MAX = 9;
	//}}}
	
	//{{{ ID EXCEPTION CODE FROM 10->99
	const E_ID_MIN = 10;
	const E_ID_INVALID = 11;
	const E_ID_NOTFOUND = 12;
	const E_ID_MAX = 99;
	//}}}
	
	//{{{ RESOURCE EXCEPTION CODE FROM 100->199
	const E_RESOURCE_MIN = 100;
	const E_RESOURCE_TEMP_UNAVAILABLE = 101;
	const E_RESOURCE_ILLEGAL = 102;
	const E_RESOURCE_MAX = 199;
	//}}}
	
	//{{{ AUTO EXCEPTION CODE FROM 200->299
	const E_AUTH_MIN = 200;
	const E_AUTH_INVALID = 201;
	const E_AUTH_RETRY = 298;
	const E_AUTH_MAX = 299;
	//}}}
	
	//{{{ QUOTA EXCEPTION CODE FROM 300->399
	const E_QUOTA_MIN = 300;
	const E_QUOTA_NOMORE = 301;
	const E_QUOTA_MAX = 399;
	//}}}
	
	const E_HOSTER_OBSOLETE = 999998;
	
	const E_EXCEPTIONS_MAXSTD = 999999; 
	// 1000000+ for custom exception code
	
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

