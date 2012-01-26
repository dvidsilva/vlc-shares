<?php 

class X_Upnp {
	
	const UUID = '51c24a73-2d0c-3ba1-1c51-3a71c550d5d5';
	const VERSION_MAJOR = '0';
	const VERSION_MINOR = '0';
	const VERSION_STATE = '1';
	
	static private $deviceName = 'VLCShares';
	
	static function version($withMinor = false, $withState = false) {
		$version = array();
		$version[] = self::VERSION_MAJOR;
		
		if ( $withMinor ) {
			$version[] = self::VERSION_MINOR;
			if ( $withState ) {
				$version[] = self::VERSION_STATE;
			} else {
				$version[] = '0';
			}
		} else {
			$version[] = '0';
			$version[] = '0';
		}
		
		return implode('.', $version);
	}
	
	static function getDeviceName($manufacter = false) {
		return self::$deviceName . ($manufacter ? ' - VLCShares '.X_VlcShares::VERSION : '');
	}
	
	static function setDeviceName($deviceName) {
		self::$deviceName = $deviceName;
	}
	
	//{{{ PORTED FUNCTIONS
	/**
	 * Ported functions from UMSP/UMSPX
	 * @see http://svn.wdlxtv.com/filedetails.php?repname=1.05.04-wdlxtv&path=%2Fplus%2Ftrunk%2Fusr%2Fshare%2Fumsp%2Ffuncs-upnp.php
	 * All rights go to UMSP author
	 */  
	
	/**
	 * Parse an UPNP request
	 * 
	 * @param string $prmRequest
	 * @see http://svn.wdlxtv.com/filedetails.php?repname=1.05.04-wdlxtv&path=%2Fplus%2Ftrunk%2Fusr%2Fshare%2Fumsp%2Ffuncs-upnp.php
	 */
	static function parseUPnPRequest($prmRequest) {;
		$reader = new XMLReader();
		$reader->XML($prmRequest);
		$retArr = array();
		while ($reader->read()) {
			if (($reader->nodeType == XMLReader::ELEMENT) && !$reader->isEmptyElement) {
				switch ($reader->localName) {
					case 'Browse':
						$retArr['action'] = 'browse';
						break;
					case 'Search':
						$retArr['action'] = 'search';
						break;
					case 'ObjectID':
						$reader->read();
						if ($reader->nodeType == XMLReader::TEXT) {
							$retArr['objectid'] = $reader->value;
						} # end if
						break;
					case 'BrowseFlag':
						$reader->read();
						if ($reader->nodeType == XMLReader::TEXT) {
							$retArr['browseflag'] = $reader->value;
						} # end if
						break;
					case 'Filter':
						$reader->read();
						if ($reader->nodeType == XMLReader::TEXT) {
							$retArr['filter'] = $reader->value;
						} # end if
						break;
					case 'StartingIndex':
						$reader->read();
						if ($reader->nodeType == XMLReader::TEXT) {
							$retArr['startingindex'] = $reader->value;
						} # end if
						break;
					case 'RequestedCount':
						$reader->read();
						if ($reader->nodeType == XMLReader::TEXT) {
							$retArr['requestedcount'] = $reader->value;
						} # end if
						break;
					case 'SearchCriteria':
						$reader->read();
						if ($reader->nodeType == XMLReader::TEXT) {
						  $retArr['searchcriteria'] = $reader->value;
						} # end if
						break;
					case 'SortCriteria':
						$reader->read();
						if ($reader->nodeType == XMLReader::TEXT) {
							$retArr['sortcriteria'] = $reader->value;
						} # end if
						break;
				} # end switch
			} # end if
		} #end while
		return $retArr;
	} #end function	
	
	
	/**
	 * Convert an array of items in DIDL xml format
	 * @param array $prmItems
	 */
	static function createDIDL($prmItems, $parentUrl = '-1', &$num, $defaultController = 'browse', $defaultAction = 'share', $defaultProvider = 'onlinelibrary') {
		# TODO: put object.container in container tags where they belong. But as long as the WDTVL doesn't mind... ;)
		# $prmItems is an array of arrays
		$xmlDoc = new DOMDocument('1.0', 'utf-8');
		$xmlDoc->formatOutput = true;
		 
		# Create root element and add namespaces:
		$ndDIDL = $xmlDoc->createElementNS('urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/', 'DIDL-Lite');
		$ndDIDL->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
		$ndDIDL->setAttribute('xmlns:upnp', 'urn:schemas-upnp-org:metadata-1-0/upnp/');
		$ndDIDL->setAttribute('xmlns:dnla', 'urn:schemas-dlna-org:metadata-1-0/');
		$xmlDoc->appendChild($ndDIDL);
		 
		# Return empty DIDL if no items present:
		if ( (!isset($prmItems)) || ($prmItems == '') ) {
			return $xmlDoc;
		} # end if
			 
		# Add each item in $prmItems array to $ndDIDL:
		foreach ($prmItems as $item) {
			/* @var $item X_Page_Item_PItem */
			
			// IGNORE REQUEST items
			if ( $item->getType() == X_Page_Item_PItem::TYPE_REQUEST ) {
				// decrease the counter, we discard one item
				$num--;
				continue;
			}
			
			$ndRes = $xmlDoc->createElement('res');
			
			if ( $item->getType() == X_Page_Item_PItem::TYPE_PLAYABLE ) {
				$ndItem = $xmlDoc->createElement('item');
				self::appendTag('upnp:class', 'object.item.videoitem', $ndItem, $xmlDoc);
				
				$ndItem->appendChild($ndRes);
				
				$ndRes->setAttribute('protocolInfo', '*:*:video:*');
				
				if ( $item->isUrl() ) {
					$ndRes_text = $xmlDoc->createTextNode($item->getLink());
					$ndRes->appendChild($ndRes_text);
				}
			} else {
				// container, request, element
				$ndItem = $xmlDoc->createElement('container');
				self::appendTag('upnp:class', 'object.container', $ndItem, $xmlDoc);
				
				$ndRes->setAttribute('protocolInfo', '*:*:*:*');
			}
			
			//{{{ MANDATORY PARAMS
			/*
			if ( $item->isUrl() ) {
				$ndItem->setAttribute('id', X_Env::encode($item->getKey()));
			} else {
				$ndItem->setAttribute('id', X_Env::encode(http_build_query($item->getLink())) );
			}
			$ndItem->setAttribute('parentID', $parentUrl);
			*/
			if ( $item->isUrl() ) {
				$ndItem->setAttribute('id', "$parentUrl/".X_Env::_($item->getKey()));
			} else {
				
				$controller = $item->getLinkController() ? $item->getLinkController() : $defaultController;
				$action = $item->getLinkAction() ? $item->getLinkAction() : $defaultAction;
				$provider = $item->getLinkParam("p") ? $item->getLinkParam("p") : $defaultProvider;
				$location = $item->getLinkParam('l') ? $item->getLinkParam('l') : '';

				$params = $item->getLink();
				foreach (array('controller', 'action', 'p', 'l') as $key ) {
					if ( isset($params[$key]) ) unset($params[$key]);
				}
				$customs = '';
				if ( count($params) ) {
					$customs .= '#';
					foreach ($params as $key => $value) {
						$customs .= "/{$key}/{$value}";
					}
				}
				
				$ndItem->setAttribute('id', 
					"{$controller}/{$action}/{$provider}/{$location}{$customs}"
				);
			}
			$ndItem->setAttribute('parentID', $parentUrl);
			$ndItem->setAttribute('restricted', true);
			$ndItem->setAttribute('searchable', '0');
			//}}}

			self::appendTag('dc:title', $item->getLabel(), $ndItem, $xmlDoc);
			self::appendTag('dc:description', $item->getDescription(), $ndItem, $xmlDoc);
			
			if ( $item->getThumbnail() ) {
				self::appendTag('upnp:album_art', $item->getThumbnail(), $ndItem, $xmlDoc);
			}

			$ndDIDL->appendChild($ndItem);
			
		} # end foreach
		
		return $xmlDoc;
	} # end function

	/**
	 * Convert an array of items in DIDL xml format
	 * @param array $prmItems
	 */
	static function createMetaDIDL(X_Page_Item_PItem $item, $parentUrl = '0', &$num, $defaultController = 'browse', $defaultAction = 'share', $defaultProvider = 'onlinelibrary') {
		# TODO: put object.container in container tags where they belong. But as long as the WDTVL doesn't mind... ;)
		# $prmItems is an array of arrays
		$xmlDoc = new DOMDocument('1.0', 'utf-8');
		$xmlDoc->formatOutput = true;
		 
		# Create root element and add namespaces:
		$ndDIDL = $xmlDoc->createElementNS('urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/', 'DIDL-Lite');
		$ndDIDL->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
		$ndDIDL->setAttribute('xmlns:upnp', 'urn:schemas-upnp-org:metadata-1-0/upnp/');
		$ndDIDL->setAttribute('xmlns:dnla', 'urn:schemas-dlna-org:metadata-1-0/');
		$xmlDoc->appendChild($ndDIDL);
		
		//$ndRes = $xmlDoc->createElement('res');
		
			// container, request, element
		$ndItem = $xmlDoc->createElement('container');
		self::appendTag('upnp:class', 'object.container', $ndItem, $xmlDoc);
		
		$ndItem->setAttribute('childCount', $num);
		
		//{{{ MANDATORY PARAMS
		
		if ( $item->isUrl() ) {
			$ndItem->setAttribute('id', "$parentUrl/".X_Env::_($item->getKey()));
		} else {
			
			$controller = $item->getLinkController() ? $item->getLinkController() : $defaultController;
			$action = $item->getLinkAction() ? $item->getLinkAction() : $defaultAction;
			$provider = $item->getLinkParam("p") ? $item->getLinkParam("p") : $defaultProvider;
			$location = $item->getLinkParam('l') ? $item->getLinkParam('l') : '';

			$params = $item->getLink();
			foreach (array('controller', 'action', 'p', 'l') as $key ) {
				if ( isset($params[$key]) ) unset($params[$key]);
			}
			$customs = '';
			if ( count($params) ) {
				$customs .= '#';
				foreach ($params as $key => $value) {
					$customs .= "/{$key}/{$value}";
				}
			}
			
			$ndItem->setAttribute('id', 
				"{$controller}/{$action}/{$provider}/{$location}{$customs}"
			);
		}		
		
		$ndItem->setAttribute('parentID', $parentUrl);
		$ndItem->setAttribute('restricted', true);
		$ndItem->setAttribute('searchable', '0');
		//}}}

		self::appendTag('dc:title', $item->getLabel(), $ndItem, $xmlDoc);
		self::appendTag('dc:description', $item->getDescription(), $ndItem, $xmlDoc);
		
		if ( $item->getThumbnail() ) {
			self::appendTag('upnp:album_art', $item->getThumbnail(), $ndItem, $xmlDoc);
		}

		$ndDIDL->appendChild($ndItem);
		
		return $xmlDoc;
	} # end function		
	
	static function createSOAPEnvelope($prmDIDL, $prmNumRet, $prmTotMatches, $prmResponseType = 'u:BrowseResponse', $prmUpdateID = '0') {
		# $prmDIDL is DIDL XML string
		# XML-Layout:
		#
		#		-s:Envelope
		#				-s:Body
		#						-u:BrowseResponse
		#								Result (DIDL)
		#								NumberReturned
		#								TotalMatches
		#								UpdateID
		#
		$doc  = new DOMDocument('1.0', 'utf-8');
		$doc->formatOutput = true;
		$ndEnvelope = $doc->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 's:Envelope');
		$doc->appendChild($ndEnvelope);
		$ndBody = $doc->createElement('s:Body');
		$ndEnvelope->appendChild($ndBody);
		$ndBrowseResp = $doc->createElementNS('urn:schemas-upnp-org:service:ContentDirectory:1', $prmResponseType);
		$ndBody->appendChild($ndBrowseResp);
		$ndResult = $doc->createElement('Result',$prmDIDL);
		$ndBrowseResp->appendChild($ndResult);
		$ndNumRet = $doc->createElement('NumberReturned', $prmNumRet);
		$ndBrowseResp->appendChild($ndNumRet);
		$ndTotMatches = $doc->createElement('TotalMatches', $prmTotMatches);
		$ndBrowseResp->appendChild($ndTotMatches);
		$ndUpdateID = $doc->createElement('UpdateID', $prmUpdateID); # seems to be ignored by the WDTVL
		#$ndUpdateID = $doc->createElement('UpdateID', (string)mt_rand(); # seems to be ignored by the WDTVL
		$ndBrowseResp->appendChild($ndUpdateID);
		 
		Return $doc;
	}
	
	static function appendTag($tagName, $tagValue, $node, $xmlDoc, $encode = true) {
		
		$tag = $xmlDoc->createElement($tagName);
		$node->appendChild($tag);
		# check if string is already utf-8 encoded
		if ( $encode ) {
			$tag_text = $xmlDoc->createTextNode( ( mb_detect_encoding($tagValue ,'auto') =='UTF-8') ? $tagValue : utf8_encode($tagValue));
		} else {
			$tag_text = $xmlDoc->createTextNode($tagValue);
		}
		$tag->appendChild($tag_text);		
		
	}
	//}}}
}
