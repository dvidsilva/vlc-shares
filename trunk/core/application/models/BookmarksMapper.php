<?php

class Application_Model_BookmarksMapper extends Application_Model_AbstractMapper
{
	
	protected $_dbTable;
	protected static $_instance = null;
	protected function getMappedClass() { return 'Application_Model_Bookmark'; }
	protected function getDbTableClass() { return 'Application_Model_DbTable_Bookmarks'; }
	/**
	 * Singleton
	 * @return Application_Model_BookmarksMapper
	 */
	static public function i() {
		if ( self::$_instance == null )
			self::$_instance = new self();
		return self::$_instance;
	}
	 
	/**
	 * 
	 * @param unknown_type $row
	 * @param Application_Model_Bookmark $model
	 */
	protected function _populate($row, $model) {
		$model->setId($row->id);
		$model->setUrl($row->url);
		$model->setTitle($row->title);
		$model->setDescription($row->description);
		$model->setThumbnail($row->thumbnail);
		$model->setType($row->type);
		$model->setCookies($row->cookies);
		$model->setUa($row->ua);
	}
	
    /**
     * @param Application_Model_Bookmark $model video info
     */
    public function save(Application_Model_Bookmark $model) {
        $data = $model->toArray();
        unset($data['id']);
        
        if ( $model->isNew() ) {
            $id = $this->getDbTable()->insert($data);
            $model->setId($id);
        } else {
            $this->getDbTable()->update($data, array('id = ?' => $model->getId()));
        }
    }
    
	public function delete(Application_Model_Bookmark $model) {
		$this->getDbTable()->delete(array('id = ?' => $model->getId()));
	}
    
	public function getCount() {
		$select = $this->getDbTable()->select()->from($this->getDbTable()->info(Zend_Db_Table_Abstract::NAME), 'COUNT(id) as num');
		return $this->getDbTable()->fetchRow($select)->num;
	}
	
    
}
