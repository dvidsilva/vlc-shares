<?php 

/**
 * Wrap content with a <tr /> tag
 * 
 * Allowed options:
 * 		container.classes: extra classes args appended to container
 * 		container.args: extra args appended to class arg
 * 
 */
class X_VlcShares_Skins_Default_TableRowHeader extends X_VlcShares_Skins_Default_Abstract implements X_VlcShares_Skins_ParentDecoratorInterface {
	
	/**
	 * Wrap the content with a <tr /> tag
	 * 
	 * @param string $content content to decorate
	 * @param stdClass $options decorator options
	 */
	public function decorate($content, $options) {

		$content = $this->wrap($content, 'tr', 'class="headers"');
		
		return $content;
	}
	
	protected function getDefaultOptions() {

		return array_merge(parent::getDefaultOptions(), array(
		));
		
	}
	
	public function getSubDecorator($elementType) {
		
		switch ($elementType) {
			
			case X_VlcShares_Skins_Manager::TABLECELL:
				return new X_VlcShares_Skins_Default_TableCellHeader();
				
			default:
				return X_VlcShares_Skins_Manager::i()->getDecorator($elementType);
				
		}
		
	}
	
}
