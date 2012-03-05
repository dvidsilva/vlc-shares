<?php

class Application_Model_AclPermissionsMapper extends Application_Model_AbstractMapper {
	
	
	protected $_dbTable;
	protected static $_instance = null;
	protected function getMappedClass() { return 'Application_Model_AclPermission'; }
	protected function getDbTableClass() { return 'Application_Model_DbTable_AclPermissions'; }
	/**
	 * Singleton
	 * @return Application_Model_AclPermissionsMapper
	 */
	static public function i() {
		if ( self::$_instance == null )
			self::$_instance = new self();
		return self::$_instance;
	}
	
	/**
	 * Store model in the db
	 * if $model->id is null, add a new row
	 * @param Application_Model_Permission $model
	 */
	public function save(Application_Model_AclPermission  $model) {
						
		$data = $model->toArray();

		if ($model->isNew()) {
			$this->getDbTable ()->insert ( $data );
		} else {
			$this->getDbTable ()->update ( $data, array ('username = ?' => $model->getUsername(), 'class = ?' => $model->getClass() ) );
		}
	}
	
	public function fetchAllByUsername($username) {
    	$resultSet = $this->getDbTable()->fetchAll(array('username = ?' => $username));
		$entries = array ();
		foreach ( $resultSet as $row ) {
			$mappedClass = $this->getMappedClass();
			$entry = new $mappedClass();
			$this->_populate($row, $entry);
			$entries [] = $entry;
		}
		return $entries;
	}
	
	public function findPermission($username, $class, Application_Model_AclPermission $model) {
		$result = $this->getDbTable ()->fetchAll ( array('username = ?' => $username, 'class = ?' => $class));
		if (0 == count ( $result )) {
			return;
		}
		$row = $result->current ();
		$this->_populate($row, $model);
	}
	
	
	/**
	 * 
	 * @param unknown_type $row
	 * @param Application_Model_AclPermission $model
	 */
	protected function _populate($row, $model) {
		$model->setUsername($row->username);
		$model->setClass($row->class);
		$model->setNew(false);
	}
	
	public function delete(Application_Model_AclPermission $model) {
		if ( !$model->isNew() ) {
			$this->getDbTable()->delete(array('username = ?' => $model->getUsername(), 'class = ?' => $model->getClass()));
		}
	}
	
	public function getCount() {
		$select = $this->getDbTable()->select()->from($this->getDbTable()->info(Zend_Db_Table_Abstract::NAME), 'COUNT(*) as num');
		return $this->getDbTable()->fetchRow($select)->num;
	}
}

