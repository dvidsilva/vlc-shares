<?php 

class X_PageParser_Parser_Own3dLive extends X_PageParser_Parser {

	//const PATTERN = '#<a href="/live/(?P<ID>[0-9]+)" class="font-size_14 font-farbe_1" title="(?P<LABEL>[^\"]+?)" >#';
	const PATTERN = '#<a rel="(?P<ID>[0-9]+)" class="small_tn_title small_tn_title_live" href="([^\"]+?)" >(?P<LABEL>[^<]+?)</a>#';

	/**
	 * (non-PHPdoc)
	 * @see X_PageParser_Parser::parse()
	 */
	public function parse($string) {
		
		$matches = array();
		$links = array();
		preg_match_all(self::PATTERN, $string, $matches, PREG_SET_ORDER);
		X_Debug::i(sprintf("Links found: %s", count($matches)));
		
		// process links
		foreach ($matches as $match ) {
			
			$link = array();
			$link['label'] = $match['LABEL'];
			$link['hoster'] = X_VlcShares_Plugins_Helper_Hoster_Own3dLive::ID;
			$link['id'] = $match['ID'];
			$links[] = $link;
			
		}
		
		return $links;
	}
	
	
}

