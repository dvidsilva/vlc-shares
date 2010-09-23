<?php

class Application_Model_PluginsMapper extends Application_Model_AbstractMapper {
	
	
	protected $_dbTable;
	protected static $_instance = null;
	protected function getMappedClass() { return 'Application_Model_Plugin'; }
	protected function getDbTableClass() { return 'Application_Model_DbTable_Plugins'; }
	/**
	 * Singleton
	 * @return Application_Model_PluginsMapper
	 */
	static public function i() {
		if ( self::$_instance == null )
			self::$_instance = new self();
		return self::$_instance;
	}
	
	/**
	 * Store model in the db
	 * if $model->id is null, add a new row
	 * @param Application_Model_Plugin $model
	 */
	public function save(Application_Model_Plugin  $model) {
		
		
		/*
		CREATE TABLE plugins (
			id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
			`key` VARCHAR(255) NOT NULL UNIQUE,
			class VARCHAR(255) NOT NULL,
			file VARCHAR(255) DEFAULT NULL,
			label VARCHAR(255) DEFAULT NULL,
			description VARCHAR(255) DEFAULT NULL,
			`type` INTEGER NOT NULL DEFAULT 0
		);
		*/
						
		$data = array( 
			'key'			=> $model->getKey(),
			'class'			=> $model->getClass(),
			'file'			=> $model->getFile(),
			'label'			=> $model->getLabel(),
			'description'	=> $model->getDescription(),
			'type'			=> $model->getType(),
			'enabled'		=> ($model->isEnabled() ? 1 : 0)
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
	 * @param Application_Model_Plugin $model
	 */
	protected function _populate($row, $model) {
		$model->setId($row->id)
			->setKey($row->key)
			->setClass($row->class)
			->setFile($row->file)
			->setLabel($row->label)
			->setDescription($row->description)
			->setType($row->type)
			->setEnabled($row->enabled);
	}
	
	public function delete(Application_Model_Plugin $model) {
		$this->getDbTable()->delete(array('id = ?' => $model->getId()));
	}
	
}

