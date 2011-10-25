<?php

class Application_Model_DevicesMapper extends Application_Model_AbstractMapper {
	
	
	protected $_dbTable;
	protected static $_instance = null;
	protected function getMappedClass() { return 'Application_Model_Device'; }
	protected function getDbTableClass() { return 'Application_Model_DbTable_AutoOptionsDevices'; }
	/**
	 * Singleton
	 * @return Application_Model_DevicesMapper
	 */
	static public function i() {
		if ( self::$_instance == null )
			self::$_instance = new self();
		return self::$_instance;
	}
	
	/**
	 * Store model in the db
	 * if $model->id is null, add a new row
	 * @param Application_Model_Device $model
	 */
	public function save(Application_Model_Device  $model) {
						
        $data = array(
            'label'   => $model->getArg(),
            'idProfile' => $model->getIdProfile(),
        	'idOutput' => $model->getIdOutput(),
        	'pattern' => $model->getPattern(),
        	'exact' => ($model->isExact() ? 1 : 0),
        	'guiClass' => $model->getGuiClass(),
        	'priority' => $model->getPriority()
        );
        
        if (null === ($id = $model->getId())) {
            unset($data['id']);
            $id = $this->getDbTable()->insert($data);
            $model->setId($id);
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
	}

	
	
	/**
	 * 
	 * @param unknown_type $row
	 * @param Application_Model_Device $model
	 */
	protected function _populate($row, $model) {
		$model->setId($row->id)
			->setLabel($row->label)
			->setIdProfile($row->idProfile)
			->setIdOutput($row->idOutput)
			->setExact((bool) $row->exact)
			->setGuiClass($row->guiClass)
			->setPattern($row->pattern)
			->setPriority($row->priority)
			;
	}
	
	public function delete(Application_Model_Device $model) {
		if ( $model->getId() ) {
			$this->getDbTable()->delete(array('id = ?' => $model->getId()));
		}
	}
}

