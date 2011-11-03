<?php 

/**
 * Manages skins. It's a singleton object!!!
 * 
 * If you want to extend this class, you have to
 * override self::i() method
 */
class X_VlcShares_Skins_Manager {

	const DUMMY = 'dummy';
	
	const CONTAINER = 'container';
	
	const PORTION = 'portion';
	
	const SECTIONCONTAINER = 'sectioncontainer';
	
	const SECTION = 'section';
	
	const BLOCK = 'block';
	const BLOCK_DISABLED = 'block_disabled';
	const BLOCK_HIGHLIGHT = 'block_highlight';
	
	const INNERBLOCK = 'innerblock';
	const INNERBLOCK_DISABLED = 'innerblock_disabled';
	const INNERBLOCK_HIGHLIGHT = 'innerblock_highlight';

	const BUTTON = 'button';
	const BUTTON_DISABLED = 'button_disabled';
	const BUTTON_HIGHLIGHT = 'button_highlight';
	
	const MENU = 'menu';
	const MENU_BREADCRUMB = 'menu_breadcrumb';
	const MENU_CONTEXTUAL = 'menu_contextual';
	const MENU_STATUS = 'menu_contextual';
	const MENU_TOOLBAR = 'menu_toolbar';
	
	const MENUENTRY_LINK = 'menuentry_link';
	const MENUENTRY_LINK_DISABLED = 'menuentry_link_disabled';
	const MENUENTRY_LINK_HIGHLIGHT = 'menuentry_link_highlight';
	
	const MENUENTRY_LABEL = 'menuentry_label';
	const MENUENTRY_LABEL_DISABLED = 'menuentry_label_disabled';
	const MENUENTRY_LABEL_HIGHLIGHT = 'menuentry_label_highlight';

	const MENUENTRY_BUTTON = 'menuentry_button';
	const MENUENTRY_BUTTON_DISABLED = 'menuentry_button_disabled';
	const MENUENTRY_BUTTON_HIGHLIGHT = 'menuentry_button_highlight';
	
	const MENUENTRY_SUBMENU = 'menuentry_submenu';
	const MENUENTRY_SUBMENU_DISABLED = 'menuentry_submenu_disabled';
	const MENUENTRY_SUBMENU_HIGHLIGHT = 'menuentry_submenu_highlight';
	
	const TABLE = "table";
	
	const TABLEROW = "tablerow";
	const TABLEROW_HEADER = "tablerow_header";
	const TABLEROW_ALTERNATE = "tablerow_alternate";
	
	const TABLECELL = "tablecell";
		
	private $_flag_throws = false;

	/**
	 * @var X_VlcShares_Skins_SkinInterface
	 */
	private $skin = null;
	
	/**
	 * Register a new skin. If another skin has been already registered, throws an exception
	 * @param X_VlcShares_Skins_SkinInterface $skin
	 * @return X_VlcShares_Skins_Manager fluent interface
	 */
	public function registerSkin(X_VlcShares_Skins_SkinInterface $skin) {
		if ( !is_null($this->skin) ) {
			throw new Exception("Skin already registered: ".get_class($this->skin));
		}
		$this->skin = $skin;
		return $this;
	}
	
	/**
	 * Get the skin reference
	 * (if no skin is registered, register the default one)
	 * @return X_VlcShares_Skins_SkinInterface
	 */
	public function getSkin() {
		if ( is_null($this->skin) ) {
			$this->registerSkin(new X_VlcShares_Skins_Default_Skin()); 
		}
		return $this->skin;
	}
	
	/**
	 * Get the skin decorator for the element type. If $type is unknown can throws
	 * exception or return a Default_Dummy decorator reference
	 * @param string $type
	 * @return X_VlcShares_Skins_DecoratorInterface
	 * @throws Exception if $type is unknown and throwsException is true
	 */
	public function getDecorator($type) {
		try {
			return $this->getSkin()->getDecorator($type, self::DUMMY);
		} catch (Exception $e) {
			X_Debug::e($e->getMessage());
			if ( $this->_flag_throws ) {
				throw $e;
			} else {
				// return a reference to default dummy decorator
				return X_VlcShares_Skins_Default_Dummy::i();
			}
		}
	}
	
	/**
	 * Set the throwsException flag
	 * @param boolean $value
	 * @return X_VlcShares_Skins_Manager fluent interface
	 */
	public function throwsException($value) {
		$this->_flag_throws = (bool) $value;
		return $this;
	}
	
	// SINGLETON STRATEGY
	/**
	 * @var X_VlcShares_Skins_Manager
	 */
	protected static $instance = null;
	protected function __construct() {
		// here i have to register default skin decorators
	}
	/**
	 * 
	 * 
	 * @return X_VlcShares_Skins_Manager
	 */
	public static function i() {
		if ( self::$instance == null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	// END SINGLETON STRATEGY
	
	
}
