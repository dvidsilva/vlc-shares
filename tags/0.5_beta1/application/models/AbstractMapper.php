<?php

abstract class Application_Model_AbstractMapper
{
	/**
	 * @return Zend_Db_Table_Abstract
	 */
	public function getDbTable() {
		if (null === $this->_dbTable) {
			$this->setDbTable ( $this->getDbTableClass() );
		}
		return $this->_dbTable;
	}
	
	
	public function setDbTable($dbTable) {
		if (is_string ( $dbTable )) {
			$dbTable = new $dbTable ();
		}
		if (! $dbTable instanceof Zend_Db_Table_Abstract) {
			throw new Exception ( 'Invalid table data gateway provided' );
		}
		$this->_dbTable = $dbTable;
		return $this;
	}
	
	public function find($id, $mappedClass) {
		$validClass = $this->getMappedClass();
		if ( !($mappedClass instanceof $validClass ) )
			throw new Exception("Invalid model object: " . get_class($mappedClass) . " vs $validClass");
		$result = $this->getDbTable ()->find ( $id );
		if (0 == count ( $result )) {
			return;
		}
		$row = $result->current ();
		$this->_populate($row, $mappedClass);
	}
	
	
	public function fetchAll() {
		$resultSet = $this->getDbTable ()->fetchAll ();
		$entries = array ();
		foreach ( $resultSet as $row ) {
			$mappedClass = $this->getMappedClass();
			$entry = new $mappedClass();
			$this->_populate($row, $entry);
			$entries [] = $entry;
		}
		return $entries;
	}
	
	public function findLast($mappedClass) {
		$validClass = $this->getMappedClass();
		if ( !($mappedClass instanceof $validClass ) )
			throw new Exception("Invalid model object: " . get_class($mappedClass) . " vs $validClass");
		$result = $this->getDbTable ()->fetchAll(null, 'id DESC', 1);
		if (0 == count ( $result )) {
			return;
		}
		$row = $result->current ();
		$this->_populate($row, $mappedClass);
	}
	
	abstract protected function _populate($row, $mappedObj);
	
	
	abstract protected function getMappedClass();
	abstract protected function getDbTableClass();
	
	//abstract static protected function _newInstance();
}

