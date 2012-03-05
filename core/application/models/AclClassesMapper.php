<?php

class Application_Model_AclClassesMapper extends Application_Model_AbstractMapper {
	
	
	protected $_dbTable;
	protected static $_instance = null;
	protected function getMappedClass() { return 'Application_Model_AclClass'; }
	protected function getDbTableClass() { return 'Application_Model_DbTable_AclClasses'; }
	/**
	 * Singleton
	 * @return Application_Model_AclClassesMapper
	 */
	static public function i() {
		if ( self::$_instance == null )
			self::$_instance = new self();
		return self::$_instance;
	}
	
	/**
	 * Store model in the db
	 * if $model->id is null, add a new row
	 * @param Application_Model_Cache $model
	 */
	public function save(Application_Model_AclClass  $model) {
						
		$data = $model->toArray();
		
		if ($model->isNew()) {
			$this->getDbTable ()->insert ( $data );
		} else {
			$this->getDbTable ()->update ( $data, array ('name = ?' => $model->getName() ));
		}
	}
	
	/*
	public function findByName($field, Application_Model_AclClass $model) {
		$result = $this->getDbTable ()->fetchAll ( array('name = ?' => $field));
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
	 * @param Application_Model_AclClass $model
	 */
	protected function _populate($row, $model) {
		$model->setName($row->name);
		$model->setDescription($row->description);
		$model->setNew(false);
	}
	
	public function delete(Application_Model_AclClass $model) {
		if ( !$model->isNew() ) {
			$this->getDbTable()->delete(array('name = ?' => $model->getName()));
		}
	}
	
	public function getCount() {
		$select = $this->getDbTable()->select()->from($this->getDbTable()->info(Zend_Db_Table_Abstract::NAME), 'COUNT(name) as num');
		return $this->getDbTable()->fetchRow($select)->num;
	}
}

