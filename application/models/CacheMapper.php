<?php

class Application_Model_CacheMapper extends Application_Model_AbstractMapper {
	
	
	protected $_dbTable;
	protected static $_instance = null;
	protected function getMappedClass() { return 'Application_Model_Cache'; }
	protected function getDbTableClass() { return 'Application_Model_DbTable_Cache'; }
	/**
	 * Singleton
	 * @return Application_Model_CacheMapper
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
	public function save(Application_Model_Cache  $model) {
						
		$data = array( 
			'uri'			=> $model->getUri(),
			'content'		=> $model->getContent(),
			'cType'			=> $model->getCType(),
			'validity'		=> $model->getValidity(),
		);

		if ($model->isNew()) {
			//unset ( $data ['id'] );
			$this->getDbTable ()->insert ( $data );
			//$model->setId($id);
		} else {
			$this->getDbTable ()->update ( $data, array ('uri = ?' => $model->getUri() ) );
		}
	}

	
	public function fetchByUri($class, Application_Model_Cache $model) {
		$result = $this->getDbTable ()->fetchAll ( array('uri = ?' => $class));
		if (0 == count ( $result )) {
			return;
		}
		$row = $result->current ();
		$this->_populate($row, $model);
	}
	
	
	/**
	 * 
	 * @param unknown_type $row
	 * @param Application_Model_Cache $model
	 */
	protected function _populate($row, $model) {
		$model->setUri($row->uri)
			->setContent($row->content)
			->setCType($row->cType)
			->setValidity($row->validity)
			->setNew(false);
	}
	
	public function delete(Application_Model_Cache $model) {
		$this->getDbTable()->delete(array('uri = ?' => $model->getUri()));
	}
	
	public function clearOutdated($time) {
		$this->getDbTable()->delete(array('validity < ?' => $time));
	}

	public function clearAll() {
		$this->getDbTable()->delete(array('validity > ?' => 0));
	}
	
	public function getCount() {
		$select = $this->getDbTable()->select()->from($this->getDbTable()->info(Zend_Db_Table_Abstract::NAME), 'COUNT(uri) as num');
		return $this->getDbTable()->fetchRow($select)->num;
	}
}

