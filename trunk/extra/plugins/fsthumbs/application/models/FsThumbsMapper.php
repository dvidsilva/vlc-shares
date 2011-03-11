<?php

class Application_Model_FsThumbsMapper extends Application_Model_AbstractMapper {
	
	
	protected $_dbTable;
	protected static $_instance = null;
	protected function getMappedClass() { return 'Application_Model_FsThumb'; }
	protected function getDbTableClass() { return 'Application_Model_DbTable_FsThumbs'; }
	/**
	 * Singleton
	 * @return Application_Model_FsThumbsMapper
	 */
	static public function i() {
		if ( self::$_instance == null )
			self::$_instance = new self();
		return self::$_instance;
	}
	
	/**
	 * Store model in the db
	 * if $model->id is null, add a new row
	 * @param Application_Model_FsThumb $model
	 */
	public function save(Application_Model_FsThumb  $model) {
						
		$data = array( 
			'path'			=> $model->getPath(),
			'url'			=> $model->getUrl(),
			'size'			=> $model->getSize(),
			'created'		=> $model->getCreated(),
		);

		if ($model->isNew()) {
			//unset ( $data ['id'] );
			$this->getDbTable ()->insert ( $data );
			//$model->setId($id);
		} else {
			$this->getDbTable ()->update ( $data, array ('path = ?' => $model->getPath() ) );
		}
	}

	
	public function fetchByPath($path, Application_Model_FsThumb $model) {
		$result = $this->getDbTable ()->fetchAll ( array('path = ?' => $path));
		if (0 == count ( $result )) {
			return;
		}
		$row = $result->current ();
		$this->_populate($row, $model);
	}
	
	public function fetchPage($page, $perpage) {
		
		$resultSet = $this->getDbTable ()->fetchAll (null, 'created DESC', $perpage, $perpage * $page );
		$entries = array ();
		foreach ( $resultSet as $row ) {
			$mappedClass = $this->getMappedClass();
			$entry = new $mappedClass();
			$this->_populate($row, $entry);
			$entries [] = $entry;
		}
		return $entries;
				
	}

	public function fetchOlderOffset($offset) {
		
		//$resultSet = $this->getDbTable ()->fetchAll (null, 'created DESC', null, $offset );
		$select = $this->getDbTable()->select();
		$select->order('created DESC')->limit(null, (int) $offset);
		//X_Debug::i((string) $select);
		$resultSet = $this->getDbTable()->fetchAll($select);
		$entries = array ();
		foreach ( $resultSet as $row ) {
			$mappedClass = $this->getMappedClass();
			$entry = new $mappedClass();
			$this->_populate($row, $entry);
			$entries [] = $entry;
		}
		return $entries;
				
	}
	
	
	/**
	 * 
	 * @param unknown_type $row
	 * @param Application_Model_FsThumb $model
	 */
	protected function _populate($row, $model) {
		$model->setPath($row->path)
			->setUrl($row->url)
			->setCreated($row->created)
			->setSize($row->size)
			->setNew(false);
	}
	
	public function delete(Application_Model_FsThumb $model) {
		$this->getDbTable()->delete(array('path = ?' => $model->getPath()));
	}
	
	public function removeOlder($time) {
		$this->getDbTable()->delete(array('created < ?' => $time));
	}
	
	public function getCount() {
		$select = $this->getDbTable()->select()->from($this->getDbTable()->info(Zend_Db_Table_Abstract::NAME), 'COUNT(path) as num');
		return $this->getDbTable()->fetchRow($select)->num;
	}
	
	public function getTotalSize() {
		$select = $this->getDbTable()->select()->from($this->getDbTable()->info(Zend_Db_Table_Abstract::NAME), 'SUM(size) as size');
		return $this->getDbTable()->fetchRow($select)->size;
	}
}

