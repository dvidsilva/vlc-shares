<?php 

class X_VlcShares_Elements_Menu extends X_VlcShares_Elements_MenuEntry {

	private $_entries = array();
	
	/**
	 * Constuctor of new Menu object
	 * @param X_VlcShares_Elements_MenuEntry $parent parent reference
	 */
	public function __construct($parent = null) {
		parent::__construct($parent); // parent setting in parent constuctor
		$this->setOption('menu.level', 0);
	}
	
	/**
	 * @see X_VlcShares_Elements_Element::getDefaultDecorator()
	 */
	public function getDefaultDecorator() {
		return X_VlcShares_Skins_Manager::i()->getDecorator(X_VlcShares_Skins_Manager::MENU);
	}
	
	/**
	 * Override default render object to add entries inside content,
	 * after that, call the overrided method
	 * @return string
	 */
	public function render($content) {
		foreach ($this->_entries as $entry) {
			$content .= (string) $entry;
		}
		return parent::render($content);
	}
	
	/**
	 * Set the level of this menu
	 * @param int $level positive integer
	 * @return X_VlcShares_Elements_Menu
	 */
	public function setLevel($level) {
		$level = (int) (((int) $level) >= 0 ? $level : 0);
		$this->setOption('menu.level', $level);
		return $this;
	}
	
	/**
	 * Add a new label menuentry inside the menu
	 * @param string $label
	 * @return X_VlcShares_Elements_Menu
	 */
	public function addLabel($label) {
		$this->addEntry(self::LABEL, $label);
		return $this;
	}
	
	/**
	 * This is a proxy method for addLink: 
	 * call addLink after route is built 
	 * @param string $label
	 * @param string $controller
	 * @param string $action
	 * @param array $params
	 * @param string $module
	 * @param string $route
	 * @param string $reset
	 * @return X_VlcShares_Elements_Menu
	 * @see self::addLink()
	 */
	public function addLinkRoute($label, $controller, $action, $params = array(), $module = 'default', $route = 'default', $reset = true) {
		/* @var $urlHelper Zend_View_Helper_Url */
		$urlHelper = $this->view->getHelper('url');
		$href = $urlHelper->url(array_merge($params, array(
				'controller' => $controller,
				'action' => $action,
				'module' => $module
			)), $route, $reset
		);
		return $this->addLink($label, $href);
	}
	
	/**
	 * Add a new link menuentry inside the menu
	 * @param string $label
	 * @param string $href
	 * @return X_VlcShares_Elements_Menu
	 */
	public function addLink($label, $href) {
		return $this->addEntry(self::LINK, $label, $href);
	}
	
	/**
	 * This is a proxy method for addButton: 
	 * call addButton after route is built 
	 * @param string $label
	 * @param string $controller
	 * @param string $action
	 * @param array $params
	 * @param string $module
	 * @param string $route
	 * @param string $reset
	 * @return X_VlcShares_Elements_Menu
	 * @see self::addLink()
	 */
	public function addButtonRoute($label, $controller, $action, $params = array(), $module = 'default', $route = 'default', $reset = true) {
		/* @var $urlHelper Zend_View_Helper_Url */
		$urlHelper = $this->view->getHelper('url');
		$href = $urlHelper->url(array_merge($params, array(
				'controller' => $controller,
				'action' => $action,
				'module' => $module
			)), $route, $reset
		);
		return $this->addButton($label, $href);
	}
	
	
	/**
	 * Add a new button menuentry inside the menu
	 * @param string $label
	 * @param string $href
	 * @return X_VlcShares_Elements_Menu
	 */
	public function addButton($label, $href) {
		return $this->addEntry(self::BUTTON, $label, $href);
	}
	
	/**
	 * Add a new submenu menuentry inside the menu
	 * and return the reference to the new submenu.
	 * Please remember to use endEntry() method to
	 * come back to the parent menu
	 * @param string $label
	 * @param string $href
	 * @return X_VlcShares_Elements_Menu
	 */
	public function addSubMenu($name = '', $href = '') {
		return $this->addEntry(self::SUBMENU, $name, $href);
	}
	
	/**
	 * Add a generic MenuEntry object inside a menu
	 * @param  X_VlcShares_Elements_MenuEntry $entry
	 * @return X_VlcShares_Elements_Menu
	 */
	public function addGenericMenuEntry(X_VlcShares_Elements_MenuEntry $entry) {
		$entry->setParent($this);
		$this->_entries[] = $entry;
		return $this;
	}
	
	/**
	 * Factory method for MenuEntry
	 * Set parent, view and decorator to the MenuEntry
	 * @param string $type type of menuentry
	 * @param string $param1
	 * @param string $param2
	 * @return X_VlcShares_Elements_MenuEntry|X_VlcShares_Elements_Menu
	 */
	protected function addEntry($type, $param1 = null, $param2 = null ) {
		switch ($type) {
			case self::SUBMENU:
				$entry = new self($this);
				$entry
					->setView($this->view)
					->setLevel($this->getOption('menu.level') + 1)
					->setLabel($param1)
					->setHref($param2);
				
				// I have to assign a subdecorator if the current decorator
				// of this implement ParentDecoratorInterface
				// or a default submenu decorator
				$parentDecorator = $this->getDecorator();
				if ( $parentDecorator instanceof  X_VlcShares_Skins_ParentDecoratorInterface ) {
					$decorator = $parentDecorator->getSubDecorator(X_VlcShares_Skins_Manager::MENUENTRY_SUBMENU) ;
				} else {
					$decorator = X_VlcShares_Skins_Manager::i()->getDecorator(X_VlcShares_Skins_Manager::MENUENTRY_SUBMENU);
				}
				$entry->setDecorator($decorator);
				
				$this->_entries[] = $entry;
				return $entry; // add menu has to return a reference to the new submenu
			
			case self::LABEL:
				$entry = new X_VlcShares_Elements_MenuEntry($this);
				$entry->setView($this->view)
					->setLabel($param1);

				$parentDecorator = $this->getDecorator();
				if ( $parentDecorator instanceof  X_VlcShares_Skins_ParentDecoratorInterface ) {
					$decorator = $parentDecorator->getSubDecorator(X_VlcShares_Skins_Manager::MENUENTRY_LABEL) ;
				} else {
					$decorator = X_VlcShares_Skins_Manager::i()->getDecorator(X_VlcShares_Skins_Manager::MENUENTRY_LABEL);
				}
				$entry->setDecorator($decorator);
				$this->_entries[] = $entry;
				break;

			case self::BUTTON:
				$entry = new X_VlcShares_Elements_MenuEntry($this);
				$entry->setView($this->view)
					->setLabel($param1)
					->setHref($param2);

				$parentDecorator = $this->getDecorator();
				if ( $parentDecorator instanceof  X_VlcShares_Skins_ParentDecoratorInterface ) {
					$decorator = $parentDecorator->getSubDecorator(X_VlcShares_Skins_Manager::MENUENTRY_BUTTON) ;
				} else {
					$decorator = X_VlcShares_Skins_Manager::i()->getDecorator(X_VlcShares_Skins_Manager::MENUENTRY_BUTTON);
				}
				$entry->setDecorator($decorator);
				$this->_entries[] = $entry;
				break;
				
			case self::LINK:
			default: // all other types work as LINK
				$entry = new X_VlcShares_Elements_MenuEntry($this);
				$entry->setView($this->view)
					->setLabel($param1)
					->setHref($param2);

				$parentDecorator = $this->getDecorator();
				if ( $parentDecorator instanceof  X_VlcShares_Skins_ParentDecoratorInterface ) {
					$decorator = $parentDecorator->getSubDecorator(X_VlcShares_Skins_Manager::MENUENTRY_LINK) ;
				} else {
					$decorator = X_VlcShares_Skins_Manager::i()->getDecorator(X_VlcShares_Skins_Manager::MENUENTRY_LINK);
				}
				$entry->setDecorator($decorator);
				$this->_entries[] = $entry;
				break;
		}
		return $this;
	}
	
}
