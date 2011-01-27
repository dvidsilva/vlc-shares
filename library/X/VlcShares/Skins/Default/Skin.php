<?php 

class X_VlcShares_Skins_Default_Skin implements X_VlcShares_Skins_SkinInterface {
	
	private $decorators = array();
	
	public function __construct() {
		$this->decorators[X_VlcShares_Skins_Manager::DUMMY] = X_VlcShares_Skins_Default_Dummy::i();
		
		$this->decorators[X_VlcShares_Skins_Manager::PORTION] = X_VlcShares_Skins_Default_Dummy::i();
		
		$this->decorators[X_VlcShares_Skins_Manager::CONTAINER] = new X_VlcShares_Skins_Default_Container();
		
		$this->decorators[X_VlcShares_Skins_Manager::SECTIONCONTAINER] = new X_VlcShares_Skins_Default_SectionContainer();
		
		$this->decorators[X_VlcShares_Skins_Manager::SECTION] = new X_VlcShares_Skins_Default_Section();
		
		$this->decorators[X_VlcShares_Skins_Manager::BLOCK] = new X_VlcShares_Skins_Default_Block();
		$this->decorators[X_VlcShares_Skins_Manager::BLOCK_DISABLED] = new X_VlcShares_Skins_Default_Block(array('variant' => 'disabled'));
		$this->decorators[X_VlcShares_Skins_Manager::BLOCK_HIGHLIGHT] = new X_VlcShares_Skins_Default_Block(array('variant' => 'red'));
		
		$this->decorators[X_VlcShares_Skins_Manager::INNERBLOCK] = new X_VlcShares_Skins_Default_InnerBlock();
		$this->decorators[X_VlcShares_Skins_Manager::INNERBLOCK_DISABLED] = new X_VlcShares_Skins_Default_InnerBlock(array('variant' => 'disabled'));
		$this->decorators[X_VlcShares_Skins_Manager::INNERBLOCK_HIGHLIGHT] = new X_VlcShares_Skins_Default_InnerBlock(array('variant' => 'red'));

		$this->decorators[X_VlcShares_Skins_Manager::MENU] = new X_VlcShares_Skins_Default_Menu();
		$this->decorators[X_VlcShares_Skins_Manager::MENU_BREADCRUMB] = new X_VlcShares_Skins_Default_Breadcrumb();
		$this->decorators[X_VlcShares_Skins_Manager::MENU_CONTEXTUAL] = new X_VlcShares_Skins_Default_MenuContextual();
		//$this->decorators[X_VlcShares_Skins_Manager::MENU_STATUS] = new X_VlcShares_Skins_Default_StatusBar();
		//$this->decorators[X_VlcShares_Skins_Manager::MENU_TOOLBAR] = new X_VlcShares_Skins_Default_ToolBar();
		
		$this->decorators[X_VlcShares_Skins_Manager::MENUENTRY_LABEL] = new X_VlcShares_Skins_Default_MenuEntry(array('type' => 'label'));
		$this->decorators[X_VlcShares_Skins_Manager::MENUENTRY_LABEL_DISABLED] = new X_VlcShares_Skins_Default_MenuEntry(array('type' => 'label', 'variant' => 'disabled'));
		$this->decorators[X_VlcShares_Skins_Manager::MENUENTRY_LABEL_HIGHLIGHT] = new X_VlcShares_Skins_Default_MenuEntry(array('type' => 'label', 'variant' => 'highlight'));

		$this->decorators[X_VlcShares_Skins_Manager::MENUENTRY_BUTTON] = new X_VlcShares_Skins_Default_MenuEntry(array('type' => 'button'));
		$this->decorators[X_VlcShares_Skins_Manager::MENUENTRY_BUTTON_DISABLED] = new X_VlcShares_Skins_Default_MenuEntry(array('type' => 'button', 'variant' => 'disabled'));
		$this->decorators[X_VlcShares_Skins_Manager::MENUENTRY_BUTTON_HIGHLIGHT] = new X_VlcShares_Skins_Default_MenuEntry(array('type' => 'button', 'variant' => 'highlight'));
		
		$this->decorators[X_VlcShares_Skins_Manager::MENUENTRY_LINK] = new X_VlcShares_Skins_Default_MenuEntry(array('type' => 'link'));
		$this->decorators[X_VlcShares_Skins_Manager::MENUENTRY_LINK_DISABLED] = new X_VlcShares_Skins_Default_MenuEntry(array('type' => 'link', 'variant' => 'disabled'));
		$this->decorators[X_VlcShares_Skins_Manager::MENUENTRY_LINK_HIGHLIGHT] = new X_VlcShares_Skins_Default_MenuEntry(array('type' => 'link', 'variant' => 'highlight'));

		$this->decorators[X_VlcShares_Skins_Manager::MENUENTRY_SUBMENU] = new X_VlcShares_Skins_Default_Menu();
		$this->decorators[X_VlcShares_Skins_Manager::MENUENTRY_SUBMENU_DISABLED] = new X_VlcShares_Skins_Default_Menu(array('variant' => 'disabled'));
		$this->decorators[X_VlcShares_Skins_Manager::MENUENTRY_SUBMENU_HIGHLIGHT] = new X_VlcShares_Skins_Default_Menu(array('variant' => 'highlight'));
		
	}
	
	function getDecorator($elementName, $fallbackName = null) {
		$decorator = @$this->decorators[$elementName];
		if ( is_null($decorator) && !is_null($fallbackName) ) {
			$decorator = @$this->decorators[$fallbackName];
		}
		if ( is_null($decorator) ) {
			throw new Exception("Invalid element decorator type: $elementName (fallback: $fallbackName)");
		}
		return $decorator;
	}
	
	
}
