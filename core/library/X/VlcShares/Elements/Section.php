<?php 


class X_VlcShares_Elements_Section extends X_VlcShares_Elements_Container {
	
	const ELASTIC = 'elastic';
	const FIXED = 'fixed';
	const RANGE = 'range';
	const FULL = 'full';
	
	const TOP = 'top';
	const MIDDLE = 'middle';
	const BOTTOM = 'bottom';
	
	public function getDefaultDecorator() {
		return X_VlcShares_Skins_Manager::i()->getDecorator(X_VlcShares_Skins_Manager::SECTION);
	}
	
	public function setFixedWidth($width) {
		$this->setWidth(self::FIXED, $width);
		return $this;
	}
	
	public function setRangeWidth($min, $max) {
		$this->setWidth(self::RANGE, $min, $max);
		return $this;
	}
	
	public function setElasticWidth() {
		$this->setWidth(self::ELASTIC);
		return $this;
	}
	
	public function setFullWidth() {
		return $this->setWidth(self::FULL);
	}
	
	protected function setWidth($type = '', $param1 = '', $param2 = '' ) {
		switch ($type) {
			case self::FULL:
				$this->setOption('section.width.type', self::FULL );
				break;
			case self::ELASTIC:
				$this->setOption('section.width.type', self::ELASTIC );
				break;
			case self::FIXED:
				$this->setOption('section.width.type', self::FIXED );
				$this->setOption('section.width.value', $param1);
				break;
			case self::RANGE:
				$this->setOption('section.width.type', self::RANGE );
				$this->setOption('section.width.min', $param1);
				$this->setOption('section.width.max', $param2);
				break;
			default:
				$this->setOption('section.width.type', '' );
				break;
		}
		return $this;
	}
	
	
	public function setSpan($span) {
		$this->setOption('section.span', (int) $span);
		return $this;
	}
	
	public function setFixedHeight($height) {
		return $this->setHeight(self::FIXED, $height);
	}
	
	public function setRangeHeight($min, $max) {
		return $this->setHeight(self::RANGE, $min, $max);
	}
	
	public function setElasticHeight() {
		return $this->setHeight(self::ELASTIC);
	}
	
	public function setFullHeight() {
		return $this->setHeight(self::FULL);
	}

	protected function setHeight($type = '', $param1 = '', $param2 = '' ) {
		switch ($type) {
			case self::FULL:
				$this->setOption('section.height.type', self::FULL );
				break;
			case self::ELASTIC:
				$this->setOption('section.height.type', self::ELASTIC );
				break;
			case self::FIXED:
				$this->setOption('section.height.type', self::FIXED );
				$this->setOption('section.height.value', $param1);
				break;
			case self::RANGE:
				$this->setOption('section.height.type', self::RANGE );
				$this->setOption('section.height.min', $param1);
				$this->setOption('section.height.max', $param2);
				break;
			default:
				$this->setOption('section.height.type', '' );
				break;
		}
		return $this;
	}
	
	public function setVerticalTop() {
		return $this->setOption('section.vertical', '');
	}
	
	public function setVerticalMiddle() {
		return $this->setOption('section.vertical', self::MIDDLE);
	}
	
	public function setVerticalBottom() {
		return $this->setOption('section.vertical', self::BOTTOM);
	}
	
	public function setHorizontalMiddle() {
		return $this->setOption('section.horizonal', self::MIDDLE);
	}
}

