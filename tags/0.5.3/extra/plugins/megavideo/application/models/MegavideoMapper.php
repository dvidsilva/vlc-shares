<?php

class Application_Model_MegavideoMapper
{
	
	private static $instance = null;
	
	/**
	 * Return singleton
	 * @return Application_Model_MegavideoMapper
	 */
	public static function i() { if ( self::$instance === null ) self::$instance = new Application_Model_MegavideoMapper(); return self::$instance; }
	
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
            $this->setDbTable('Application_Model_DbTable_Megavideo');
        }
        return $this->_dbTable;
    }

    /*
     * Allow to save items without megavideo check
     * @param Application_Model_Megavideo $megavideo video info
     */
    public function directSave(Application_Model_Megavideo $megavideo) {
        $data = array(
            'idVideo'   => $megavideo->getIdVideo(),
            'description' => $megavideo->getDescription(),
            'category' => $megavideo->getCategory(),
        	'label' => $megavideo->getLabel(),
        );
        
        if (null === ($id = $megavideo->getId())) {
            unset($data['id']);
            $this->getDbTable()->insert($data);
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }
    
    public function save(Application_Model_Megavideo $megavideo)
    {
        $wrapper = new X_Megavideo($megavideo->getIdVideo());
        
		preg_match('#\?v=(.+?)$#', $megavideo->getIdVideo(), $id); 
		$megavideo->setIdVideo(@$id[1]?$id[1]:$megavideo->getIdVideo()); 
        
        
        if ( $megavideo->getLabel() == '' || is_null($megavideo->getLabel()) ) {
        	$megavideo->setLabel(urldecode($wrapper->get('TITLE')));
        }
        if ( $megavideo->getDescription() == '' || is_null($megavideo->getDescription()) ) {
        	$megavideo->setDescription(urldecode($wrapper->get('DESCRIPTION')));
        }

        $data = array(
            'idVideo'   => $megavideo->getIdVideo(),
            'description' => $megavideo->getDescription(),
            'category' => $megavideo->getCategory(),
        	'label' => $megavideo->getLabel(),
        );
        
        if (null === ($id = $megavideo->getId())) {
            unset($data['id']);
            $this->getDbTable()->insert($data);
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }
 
    public function find($id, Application_Model_Megavideo $megavideo)
    {
        $result = $this->getDbTable()->find($id);
        if (0 == count($result)) {
            return;
        }
        $row = $result->current();
        $megavideo->setId($row->id)
                  ->setDescription($row->description)
                  ->setLabel($row->label)
                  ->setCategory($row->category)
                  ->setIdVideo($row->idVideo);
    }
 
    public function fetchAll()
    {
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Megavideo();
			$entry->setId($row->id)
                  ->setDescription($row->description)
                  ->setLabel($row->label)
                  ->setCategory($row->category)
                  ->setIdVideo($row->idVideo);
            $entries[] = $entry;
        }
        return $entries;
    }

    public function fetchCategories() {
    	$resultSet = $this->getDbTable()->getAdapter()->fetchAll('SELECT distinct category, count(id) AS links FROM plg_megavideo GROUP BY category');
    	return $resultSet;    	
    }
    
    public function fetchByCategory($categoryName) {
    	$resultSet = $this->getDbTable()->fetchAll('category LIKE "'.$categoryName.'"');
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new Application_Model_Megavideo();
			$entry->setId($row->id)
                  ->setDescription($row->description)
                  ->setLabel($row->label)
                  ->setCategory($row->category)
                  ->setIdVideo($row->idVideo);
            $entries[] = $entry;
        }
        return $entries;
    }
    
    public function renameCategory($oldName, $newName) {
    	$where = $this->getDbTable()->getAdapter()->quoteInto("category = ?", $oldName);
    	$update = array('category' => $newName);
    	$this->getDbTable()->update($update, $where);
    }
    
    public function delete($id) {
    	$where = $this->getDbTable()->getAdapter()->quoteInto("id = ?", $id);
    	$this->getDbTable()->delete($where);
    }

    public function deleteCategory($categoryName) {
    	$where = $this->getDbTable()->getAdapter()->quoteInto("category LIKE ?", $categoryName);
    	X_Debug::i($where);
    	$this->getDbTable()->delete($where);
    }
    
}
