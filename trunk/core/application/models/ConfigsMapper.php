<?php

class Application_Model_ConfigsMapper extends Application_Model_AbstractMapper {
	
	
	protected $_dbTable;
	protected static $_instance = null;
	protected function getMappedClass() { return 'Application_Model_Config'; }
	protected function getDbTableClass() { return 'Application_Model_DbTable_Configs'; }
	/**
	 * Singleton
	 * @return Application_Model_ConfigsMapper
	 */
	static public function i() {
		if ( self::$_instance == null )
			self::$_instance = new self();
		return self::$_instance;
	}
	
	/**
	 * Store model in the db
	 * if $model->id is null, add a new row
	 * @param Application_Model_Config $model
	 */
	public function save(Application_Model_Config  $model) {
		
	
		/*
		id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
		key VARCHAR(255) NOT NULL UNIQUE,
		value TEXT DEFAULT NULL,
		default TEXT DEFAULT NULL,
		section VARCHAR(255) NOT NULL DEFAULT "general",
		label VARCHAR(255) DEFAULT NULL,
		description VARCHAR(255) DEFAULT NULL,
		type INTEGER NOT NULL DEFAULT 0
		 */
			
		$data = array( 
			'key'			=> $model->getKey(),
			'value'			=> $model->getValue(),
			'default'		=> $model->getDefault(),
			'section'		=> $model->getSection(),
			'label'			=> $model->getLabel(),
			'description'	=> $model->getDescription(),
			'type'			=> $model->getType(),
			'class'			=> $model->getClass()
		);
		
		if (null === ($id = $model->getId ())) {
			unset ( $data ['id'] );
			$id = $this->getDbTable ()->insert ( $data );
			$model->setId($id);
		} else {
			$this->getDbTable ()->update ( $data, array ('id = ?' => $id ) );
		}
	}

	/**
	 * 
	 * @param unknown_type $row
	 * @param Application_Model_Config $model
	 */
	protected function _populate($row, $model) {
		$model->setId($row->id)
			->setKey($row->key)
			->setDefault($row->default)
			->setValue($row->value)
			->setSection($row->section)
			->setLabel($row->label)
			->setDescription($row->description)
			->setType($row->type)
			->setClass($row->class);
	}
	
	public function delete(Application_Model_Config $model) {
		$this->getDbTable()->delete(array('id = ?' => $model->getId()));
	}
	
    public function fetchSections() {
    	$resultSet = $this->getDbTable()->getAdapter()->fetchAll('SELECT distinct section, count(id) AS configs FROM configs GROUP BY section');
    	return $resultSet;    	
    }
    
    public function fetchBySection($section) {
		$resultSet = $this->getDbTable ()->fetchAll (array('section LIKE ?' => $section));
		$entries = array ();
		foreach ( $resultSet as $row ) {
			$mappedClass = $this->getMappedClass();
			$entry = new $mappedClass();
			$this->_populate($row, $entry);
			$entries [] = $entry;
		}
		return $entries;
    }
    
    public function fetchByKey($key, Application_Model_Config $model) {
		$result = $this->getDbTable ()->fetchAll (array('key LIKE ?' => $key));
		
		if (0 == count ( $result )) {
			return;
		}
		$row = $result->current ();
		$this->_populate($row, $model);
		
    }
	
    
    public function fetchBySectionNamespace($section, $namespace = null) {
    	if ( $namespace == null ) {
    		return $this->fetchBySection($section);
    	} else {
    		$select = $this->getDbTable()->select()->where('section LIKE ?', $section)->where('key LIKE ?', "$namespace.%");
			$resultSet = $this->getDbTable ()->fetchAll ($select);
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
    
}

