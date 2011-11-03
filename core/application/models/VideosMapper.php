<?php

class Application_Model_VideosMapper extends Application_Model_AbstractMapper
{
	
	protected $_dbTable;
	protected static $_instance = null;
	protected function getMappedClass() { return 'Application_Model_Video'; }
	protected function getDbTableClass() { return 'Application_Model_DbTable_Videos'; }
	/**
	 * Singleton
	 * @return Application_Model_VideosMapper
	 */
	static public function i() {
		if ( self::$_instance == null )
			self::$_instance = new self();
		return self::$_instance;
	}
	 
	/**
	 * 
	 * @param unknown_type $row
	 * @param Application_Model_Video $model
	 */
	protected function _populate($row, $model) {
		$model->setId($row->id)
			->setIdVideo($row->idVideo)
			->setTitle($row->title)
			->setDescription($row->description)
			->setThumbnail($row->thumbnail)
			->setHoster($row->hoster)
			->setCategory($row->category)
			;
	}
	
    /**
     * @param Application_Model_Video $model video info
     */
    public function save(Application_Model_Video $model) {
        $data = array(
            'idVideo'   => $model->getIdVideo(),
            'description' => $model->getDescription(),
            'category' => $model->getCategory(),
        	'title' => $model->getTitle(),
        	'hoster' => $model->getHoster(),
        	'thumbnail' => $model->getThumbnail()
        );
        
        if (null === ($id = $model->getId())) {
            unset($data['id']);
            $id = $this->getDbTable()->insert($data);
            $model->setId($id);
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }

    public function fetchCategories() {
    	$resultSet = $this->getDbTable()->getAdapter()->fetchAll('SELECT distinct category, count(id) AS links FROM videos GROUP BY category');
    	return $resultSet;    	
    }
    
    
    public function fetchByCategory($categoryName) {
    	$resultSet = $this->getDbTable()->fetchAll('category LIKE "'.$categoryName.'"');
		$entries = array ();
		foreach ( $resultSet as $row ) {
			$mappedClass = $this->getMappedClass();
			$entry = new $mappedClass();
			$this->_populate($row, $entry);
			$entries [] = $entry;
		}
		return $entries;
	}
    
    public function renameCategory($oldName, $newName) {
    	$where = $this->getDbTable()->getAdapter()->quoteInto("category = ?", $oldName);
    	$update = array('category' => $newName);
    	$this->getDbTable()->update($update, $where);
    }
    
	public function delete(Application_Model_Video $model) {
		$this->getDbTable()->delete(array('id = ?' => $model->getId()));
	}
    
    public function deleteCategory($categoryName) {
    	$where = $this->getDbTable()->getAdapter()->quoteInto("category LIKE ?", $categoryName);
    	$this->getDbTable()->delete($where);
    }
    
	public function getCount() {
		$select = $this->getDbTable()->select()->from($this->getDbTable()->info(Zend_Db_Table_Abstract::NAME), 'COUNT(id) as num');
		return $this->getDbTable()->fetchRow($select)->num;
	}
    
	public function getCountCategories() {
		$select = $this->getDbTable()->select()->from($this->getDbTable()->info(Zend_Db_Table_Abstract::NAME), 'COUNT(DISTINCT category) as num');
		return $this->getDbTable()->fetchRow($select)->num;
	}
	
    
}
