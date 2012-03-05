<?php

class Application_Model_AclResourcesMapper extends Application_Model_AbstractMapper {
	
	
	protected $_dbTable;
	protected static $_instance = null;
	protected function getMappedClass() { return 'Application_Model_AclResource'; }
	protected function getDbTableClass() { return 'Application_Model_DbTable_AclResources'; }
	/**
	 * Singleton
	 * @return Application_Model_AclResourcesMapper
	 */
	static public function i() {
		if ( self::$_instance == null )
			self::$_instance = new self();
		return self::$_instance;
	}
	
	/**
	 * Store model in the db
	 * if $model->id is null, add a new row
	 * @param Application_Model_AclResource $model
	 */
	public function save(Application_Model_AclResource  $model) {
						
		$data = $model->toArray();

		if ($model->isNew()) {
			$this->getDbTable ()->insert ( $data );
		} else {
			$this->getDbTable ()->update ( $data, array ('key = ?' => $model->getKey() ) );
		}
	}
	
	/*
	public function findByKey($key, Application_Model_AclResource $model) {
		$result = $this->getDbTable ()->fetchAll ( array('key = ?' => $key));
		if (0 == count ( $result )) {
			return;
		}
		$row = $result->current ();
		$this->_populate($row, $model);
	}
	*/
	
	/**
	 * 
	 * @param unknown_type $row
	 * @param Application_Model_AclResource $model
	 */
	protected function _populate($row, $model) {
		$model->setKey($row->key);
		$model->setClass($row->class);
		$model->setGenerator($row->generator);
		$model->setNew(false);
	}
	
	public function delete(Application_Model_AclResource $model) {
		if ( !$model->isNew() ) {
			$this->getDbTable()->delete(array('key = ?' => $model->getKey()));
		}
	}
	
	public function getCount() {
		$select = $this->getDbTable()->select()->from($this->getDbTable()->info(Zend_Db_Table_Abstract::NAME), 'COUNT(key) as num');
		return $this->getDbTable()->fetchRow($select)->num;
	}
	
	public function fetchByClassNotGenerated($class, $notGenerator) {
		$resultSet = $this->getDbTable ()->fetchAll (array('class = ?' => $class, 'generator <> ?' => $notGenerator));
		$entries = array ();
		foreach ( $resultSet as $row ) {
			$mappedClass = $this->getMappedClass();
			$entry = new $mappedClass();
			$this->_populate($row, $entry);
			$entries [] = $entry;
		}
		return $entries;
	}
}

