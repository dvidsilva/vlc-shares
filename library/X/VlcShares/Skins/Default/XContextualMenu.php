<?php 

require_once 'X/VlcShares/Skins/Default/Abstract.php';

class X_VlcShares_Skins_Default_XContextualMenu extends X_VlcShares_Skins_Default_Abstract {
	
	/**
	 * decorate a list of links as a contextual menu
	 * 
	 * @param array $links contextualmenu links
	 * @param stdClass $options decorator options
	 */
	public function decorate($links, $options) {

		$content = '';
		
		foreach ($links as $link) {
			if ( !is_object($link) ) $link = (object) $link;
			$content .= $this->recursiveWrap($link->label, array(
				array('li', 'class="fixed column subnav-button"'),
				array('div', 'class="container"'),
				array('a', "href=\"{$link->url}\""),
				array('span')
			)) . PHP_EOL;
		}
		
		return $this->wrap($content, 'ul', 'class="columns same-height contextual-menu"');
	}
	
}
