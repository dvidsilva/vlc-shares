<?php 
/*
 * This file is part of the VlcShares PutLocker-plugin 0.1.
 *
 * Author: Jan Holthuis <holthuis.jan@googlemail.com>
 *
 *  The VlcShares PutLocker-plugin is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The VlcShares PutLocker-plugin is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with the VlcShares PutLocker-plugin.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class X_VlcShares_Plugins_Helper_Hoster_PutLocker implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'putlocker';
	const PATTERN = '/http\:\/\/((www\.)?)putlocker\.com\/(file|embed)\/(?P<ID>[A-Za-z0-9]+)/';
	
	private $info_cache = array();
	
	/**
	 * give the hoster id
	 * @return string
	 */
	function getId() {
		return self::ID;
	}
	/**
	 * get the hoster pattern for regex match
	 * @return string
	 */
	function getPattern() {
		return self::PATTERN;
	}
	/**
	 * get the resource ID for the hoster
	 * from an $url
	 * @param string $url the hoster page
	 * @return string the resource id
	 */
	function getResourceId($url) {
		$matches = array();
		if ( preg_match(self::PATTERN, $url, $matches ) ) {
			if ( $matches['ID'] != '' ) {
				return $matches['ID'];
			}
			X_Debug::e("No id found in {{$url}}");
			throw new Exception("No id found in {{$url}}", self::E_ID_NOTFOUND);
		} else {
			X_Debug::e("Regex failed");
			throw new Exception("Regex failed", self::E_URL_INVALID);
		}
	}
	/**
	 * get a playable resource url
	 * from an $url (or a resource id if $isId = true)
	 * @param string $url the hoster page or resource ID
	 * @param boolean $isId
	 * @return string a playable url
	 */
	function getPlayable($url, $isId = true) {
		$infos = $this->getPlayableInfos($url, $isId);
		return $infos['url'];
	}
	
	/**
	 * get an array with standard information about the playable
	 * @param string $url the hoster page or resource ID
	 * @param boolean $isId
	 * @return array format:
	 * 		array(
	 * 			'title' => TITLE
	 * 			'description' => DESCRIPTION
	 * 			'length' => LENGTH
	 * 			...
	 * 		)
	 */
	function getPlayableInfos($url, $isId = true) {
		
		if ( !$isId ) {
			$url = $this->getResourceId($url);
		}
		
		// use cached values
		if ( array_key_exists($url, $this->info_cache) ) {
			return $this->info_cache[$url];
		}
		
		// use the api
		$http = new Zend_Http_Client("http://www.putlocker.com/embed/" . $url,
			array(
				'headers' => array(
					'User-Agent' => "vlc-shares/".X_VlcShares::VERSION." putlocker/".X_VlcShares_Plugins_PutLocker::VERSION
				)
			)
		);
		
		$xml = $http->request()->getBody();

		if ( preg_match('/<div class\=\"message t_0\">This file doesn\'t exist, or has been removed\.<\/div>/', $xml)  ) {
			X_Debug::e("Invalid ID {{$url}} or file removed");
			throw new Exception("Invalid ID {{$url}}", self::E_ID_INVALID);
		}
		
		$matches = array();

		if ( !preg_match('/<strong>(?P<title>[^<]+)<\/strong>/', $xml, $matches)  ) {
			$title = X_Env::_('p_putlocker_title_not_setted');
			X_Debug::w("Title not found");
		} else {
			$title = $matches['title'];
		}
			
		$description = '';

		if ( !preg_match('/<input type\=\"hidden\" value\=\"(?P<hash>[^\"]+)\" name\=\"(?P<arg>[^\"]+)\">/', $xml, $matches)  ) {
			X_Debug::w("Couldn't find hash for file {{$url}}");
			throw new Exception("Couldn't find hash for file {{$url}}", self::E_ID_INVALID);
		}
		$hash = $matches['hash'];
		$arg = $matches['arg'];

		// To turn cookie stickiness on, set a Cookie Jar
		$http->setCookieJar();

		// First request: log in and start a session
		$http->setUri("http://www.putlocker.com/embed/" . $url);
		$http->setParameterPost($arg, $hash);
		$http->setParameterPost('confirm', 'Close Ad and Watch as Free User');
		$http->request('POST');

		$xml = $http->request()->getBody();

		
		$matches = array();
		if ( !preg_match('/<img src="(?P<thumbnail>.+?)" name="bg" style=".+?" id="bg"\/>/', $xml, $matches)  ) {
			X_Debug::w("No thumbnail found");
			$thumbnail = '';
		} else {
			$thumbnail = $matches['thumbnail'];
		}
		X_Debug::i("Thumbnail found {{$thumbnail}}");
		
		$matches = array();
		if ( !preg_match("/playlist: '(?P<playlist>[^']+)'/", $xml, $matches)  ) {
			$playlist = '';
			X_Debug::w("Couldn't find playlist for file ".$url."!");
			throw new Exception("Couldn't find playlist for file {{$url}}", self::E_ID_INVALID);
		}
		$playlist = $matches['playlist'];

		$http->setUri("http://www.putlocker.com" . $playlist);
		$http->request('GET');

		$xml = $http->request()->getBody();

		$matches = array();
		if ( !preg_match('/<media:content url\=\"(?P<video>[^\"]+)\" type\=\"video\/x-flv\"  duration\=\"(?P<length>[^\"]+)\" \/>/', $xml, $matches)  ) {
			X_Debug::w("Couldn't find video link for file ".$url."!");
			throw new Exception("Couldn't find video link for file {{$url}}", self::E_ID_INVALID);
		}
		$length = $matches['length'];
		$video  = $matches['video'];
		
		$infos = array(
			'title' => $title,
			'description' => $description,
			'length' => $length,
			'thumbnail' => $thumbnail,
			'url' => $video
		);
				
		// add in cache
		$this->info_cache[$url] = $infos;
		
		return $infos;
		
	}
	
	function getHosterUrl($playableId) {
		return "http://www.putlocker.com/file/$playableId";
	}
	
	
}
