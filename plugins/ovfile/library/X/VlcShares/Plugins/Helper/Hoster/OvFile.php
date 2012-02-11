<?php 
/*
 * This file is part of the VlcShares OVFile-plugin 0.1.
 *
 * Author: Jan Holthuis <holthuis.jan@googlemail.com>
 *
 *  The VlcShares OVFile-plugin is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The VlcShares OVFile-plugin is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with the VlcShares OVFile-plugin.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class X_VlcShares_Plugins_Helper_Hoster_OvFile implements X_VlcShares_Plugins_Helper_HostInterface {

	const ID = 'ovfile';
	const PATTERN = '/http\:\/\/((www\.)?)ovfile\.com\/(?P<ID>[A-Za-z0-9]+)/';
	
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
			X_Debug::e("No id found in {{$url}}", self::E_ID_NOTFOUND);
			throw new Exception("No id found in {{$url}}");
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
		$http = new Zend_Http_Client("http://www.ovfile.com/" . $url,
			array(
				'headers' => array(
					'User-Agent' => "vlc-shares/".X_VlcShares::VERSION." ovfile/".X_VlcShares_Plugins_OVFile::VERSION
				)
			)
		);
		
		$xml = $http->request()->getBody();

		if ( preg_match('/<title>404 - Not Found<\/title>/', $xml)  ) {
			throw new Exception("Invalid ID {{$url}}", self::E_ID_INVALID);
		}
		
		$matches = array();

		if ( !preg_match('/<input type\=\"hidden\" name\=\"fname\" value\=\"(?P<title>[^\"]+)\">/', $xml, $matches)  ) {
			$title = X_Env::_('p_ovfile_title_not_setted');
		} else {
			$title = $matches['title'];
		}

		$description = '';
		
		$length = 0;

		// First request: log in and start a session
		$http->setUri("http://www.ovfile.com/" . $url);
		$http->setParameterPost('op', 'download1');
		$http->setParameterPost('usr_login', '');
		$http->setParameterPost('id', $url);
		$http->setParameterPost('fname', $title);
		$http->setParameterPost('referer', '');
		$http->setParameterPost('channel', '');
		$http->setParameterPost('method_free', 'Free Download');
		$http->request('POST');

		$xml = $http->request()->getBody();

		
		$matches = array();

		if ( !preg_match("/<param name='flashvars' value='file=(?P<video>[^&]+)&image=(?P<thumbnail>[^&]+)&provider=http'>/", $xml, $matches)  ) {
			X_Debug::e("Invalid regex pattern searching for video link");
			throw new Exception("Invalid regex pattern searching for video link", self::E_ID_INVALID);
		} else {
			//$thumbnail = urldecode($matches['thumbnail']);
			$thumbnail = null; // thumbs disabled 'cause fetched url give 404 (ovfile problem)
			$video =  urldecode($matches['video']);
		}
		
		//X_Debug::i("Thumbnail: {{$thumbnail}}");
		//X_Debug::i("Video url: {{$video}}");
		
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
		return "http://www.ovfile.com/$playableId";
	}
	
	
}
