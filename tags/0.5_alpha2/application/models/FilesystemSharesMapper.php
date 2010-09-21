<?php

require_once 'FilesystemShare.php';
require_once 'X/Env.php';
require_once 'X/Debug.php';

class Application_Model_FilesystemSharesMapper
{
	
	private static $instance = null;
	
	/**
	 * Return singleton
	 * @return Application_Model_FilesystemSharesMapper
	 */
	public static function i() { if ( self::$instance === null ) self::$instance = new Application_Model_FilesystemSharesMapper(); return self::$instance; }
	
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
            $this->setDbTable('Application_Model_DbTable_FilesystemShares');
        }
        return $this->_dbTable;
    }
 
    public function save(Application_Model_FilesystemShare $share)
    {

        $data = array(
            'path'   => $share->getPath(),
            'image' => $share->getImage(),
        	'label' => $share->getLabel(),
        );
        
        if (null === ($id = $share->getId())) {
            unset($data['id']);
            $id = $this->getDbTable()->insert($data);
            $share->setId($id);
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }
 
    public function find($id, Application_Model_FilesystemShare $share)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $this->_populate($share, $row);
    }
 
    private function _populate(Application_Model_FilesystemShare $share, $row) {
        $share->setId($row->id)
                  ->setPath($row->path)
                  ->setLabel($row->label)
                  ->setImage($row->image);
	}
    
    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_FilesystemShare();
			$this->_populate($entry, $row);
            $entries[] = $entry;
        }
        return $entries;
    }
    
    public function delete(Application_Model_FilesystemShare  $share) {
    	if ( $share->getId() !== null ) {
	    	$where = $this->getDbTable()->getAdapter()->quoteInto("id = ?", $share->getId());
	    	$this->getDbTable()->delete($where);
    	}
    }
    
}
