<?php 

/**
 * Allow to create pagination from items
 */
class X_VlcShares_Plugins_Helper_Paginator extends X_VlcShares_Plugins_Helper_Abstract {
	
	/**
	 * 
	 * @var Zend_Config
	 */
	private $options = null;
	
	function __construct(Zend_Config $options) {
		
		$this->options = $options;
		
	}
	
	/**
	 * Get only page items from $items
	 * @param array|X_Page_ItemList $items
	 * @param int $page page number (1-index)
	 * @param int $perPage per page items
	 * @throws Exception invalid $items type  
	 */
	public function getPage($items, $page = 1, $perPage = null ) {
		if ( $perPage === null ) {
			$perPage = $this->options->get('perpage', 25);
		}
		if ( is_array($items) ) {
			return array_slice($items, ($page - 1) * $perPage, $perPage, true);
		} elseif ($items instanceof X_Page_ItemList ) {
			$itemsClass = get_class($items);
			$items = $items->getItems();
			return new $itemsClass(array_slice($items, ($page - 1) * $perPage, $perPage, true));
		} else {
			throw new Exception("Items must be an array or an X_Page_ItemList");
		}
	}
	
	/**
	 * Get the total count of pages for $items
	 * @param array|X_Page_ItemList $items
	 * @param int $perPage
	 * @return int
	 * @throws Exception invalid $items type
	 */
	public function getPages($items, $perPage = null ) {
		if ( $perPage === null ) {
			$perPage = $this->options->get('perpage', 25);
		}
		
		if ($items instanceof X_Page_ItemList ) {
			$items = $items->getItems();
		}		
		if ( is_array($items) ) {
			$itemsCount = count($items);
			$pages = (int) ($itemsCount / $perPage);
			if ( $itemsCount % $perPage > 0 ) {
				$pages++;
			}
			return $pages;
		}
		
		throw new Exception("Items must be an array or an X_Page_ItemList");
	}
	
	/**
	 * Return true if a next page is possible
	 * @return boolean
	 * @throws Exception invalid $items type
	 */
	public function hasNext($items, $page = 1, $perPage = null) {
		if ( $perPage === null ) {
			$perPage = $this->options->get('perpage', 25);
		}
		if ( is_array($items) ) {
			return ( count($items) > ( $page * $perPage ) ) ;
		} elseif ($items instanceof X_Page_ItemList ) {
			$items = $items->getItems();
			return ( count($items) > ( $page * $perPage ) ) ;
		} else {
			throw new Exception("Items must be an array or an X_Page_ItemList");
		}
	}
	
	/**
	 * return true if a previous page is possible
	 * @return boolean
	 * @throws Exception invalid $items type
	 */
	public function hasPrevious($items, $page = 1, $perPage = null ) {
		return ($page > 1);
	}
	
}

