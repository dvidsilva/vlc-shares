<?php

require_once 'Output.php';
require_once 'X/Env.php';
require_once 'X/Debug.php';

class Application_Model_OutputsMapper
{
	
	private static $instance = null;
	
	/**
	 * Return singleton
	 * @return Application_Model_OutputsMapper
	 */
	public static function i() { if ( self::$instance === null ) self::$instance = new Application_Model_OutputsMapper(); return self::$instance; }
	
	private function __construct() {}
	
    protected $_dbTable;
 
    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }
 
    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable('Application_Model_DbTable_Outputs');
        }
        return $this->_dbTable;
    }
 
    public function save(Application_Model_Output $model)
    {

        $data = array(
            'arg'   => $model->getArg(),
        	'cond_devices' => $model->getCondDevices(),
        	'label' => $model->getLabel(),
        	'weight' => $model->getWeight(),
        	'link' => $model->getLink()
        );
        
        if (null === ($id = $model->getId())) {
            unset($data['id']);
            $id = $this->getDbTable()->insert($data);
            $model->setId($id);
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }
 
    public function find($id, Application_Model_Output $model)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $this->_populate($model, $row);
    }
 
    public function findBest($device = null, Application_Model_Output $model) {
    	$select = $this->getDbTable()->select();
    	if ( $device !== null && is_integer($device) ) {
    		$select->where("cond_devices = ?", $device);
    	}
    	$select->orWhere("cond_devices IS NULL")
    		->order(array('cond_devices DESC', 'weight DESC', 'label ASC'))
    		->limit(1);
    	
        $result = $this->getDbTable()->fetchAll($select);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $this->_populate($model, $row);
    }
    
    
    /**
     * 
     * @param Application_Model_Profile $model
     * @param unknown_type $row
     */
    private function _populate(Application_Model_Output $model, $row) {
        $model->setId($row->id)
			->setLabel($row->label)
			->setCondDevices($row->cond_devices)
			->setWeight($row->weight)
			->setLink($row->link)
			->setArg($row->arg);
	}
    
    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Output();
			$this->_populate($entry, $row);
            $entries[] = $entry;
        }
        return $entries;
    }
    
    public function delete(Application_Model_Output  $model) {
    	if ( $model->getId() !== null ) {
	    	$where = $this->getDbTable()->getAdapter()->quoteInto("id = ?", $model->getId());
	    	$this->getDbTable()->delete($where);
    	}
    }
    
    public function fetchByConds($device = null) {
    	$select = $this->getDbTable()->select();
    	if ( $device !== null && is_integer($device) ) {
    		$select->where("cond_devices = ?", $device);
    	}
    	$select->orWhere("cond_devices IS NULL")
    		->order(array('cond_devices DESC', 'weight DESC', 'label ASC'));
    	
        $resultSet = $this->getDbTable()->fetchAll($select);
    	$entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Output();
			$this->_populate($entry, $row);
            $entries[] = $entry;
        }
        return $entries;
    	
    }
}
