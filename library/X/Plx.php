<?php

require_once 'X/Plx/Item.php';

/**
 * Wrapper class per la scrittura di PLX
 * @author ximarx
 *
 */
class X_Plx {

	private $title = '';
	private $description = '';
	private $version = 1;
	private $background = '';
	private $logo = '';
	private $items = array();
	
	/**
	 * Create a new playlist
	 * @param string $title
	 * @param string $description
	 * @param string $version
	 * @param string $background
	 * @param string $logo
	 */
	public function __construct($title = '', $description = '',$version = 1, $background = '', $logo = '') {
		$this->setTitle($title)
			->setDescription($description)
			->setVersion($version)
			->setBackground($background)
			->setLogo($logo);
	}
	
	
	public function __call($name, $argv) {
		if ( substr($name,0,3) == 'set' && count($argv) === 1 ) {
			$lowered = strtolower(substr($name, 3));
			if ( property_exists($this, $lowered) && $lowered != 'items' ) {
				$this->$lowered = $argv[0];
			}
			return $this;
		} elseif ( substr($name,0,3) == 'get' && count($argv) === 0 ) {
			$lowered = strtolower(substr($name, 3));
			if ( property_exists($this, $lowered) ) {
				return $this->$lowered;
			}
		}
	}
	
	public function addItem(X_Plx_Item $item) {
		$this->items[] = $item;
		return $this;
	}
	
	public function getHeader() {
		$output = '';
		$output .= "version=".$this->getVersion()."\n";
		$output .= "title=".$this->getTitle()."\n";
		if ( $this->getBackground() != '' )
			$output .= "background=".$this->getBackground()."\n";
		if ( $this->getLogo() != '' )
		$output .= "logo=".$this->getLogo()."\n";
		$output .= "description=".$this->getDescription()."\n";
		$output .= "\n";
		return $output;
	}
	
	public function __toString() {
		$output = '';
		$output .= $this->getHeader();
		foreach ($this->items as $item) {
			$output .= $item;
		}
		return $output;
	}
}
