<?php

require_once 'X/Page/Item/Message.php';

/**
 * A Test
 *  
 * @author ximarx
 */
class X_Page_Item_Test extends X_Page_Item_Message {

	private $reason;
	
	/**
	 * Create a new X_Page_Item_Test
	 * @param string $key item key in the list
	 * @param string $label item label
	 * @param array|string $link an array of Route params
	 */
	function __construct($key, $label, $type = self::TYPE_INFO) {
		parent::__construct($key, $label, $type);
	}

	
	
	/**
	 * Get the reason for a failure/success
	 * @return string
	 */
	public function getReason() {
		return $this->reason;
	}
	
	/**
	 * Set the reason
	 * @param string $reason
	 * @return X_Page_Item_Test
	 */
	public function setReason($reason) {
		$this->reason = $reason;
		return $this;
	}
	
}

