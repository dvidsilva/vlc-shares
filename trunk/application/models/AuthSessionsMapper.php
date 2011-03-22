<?php

class Application_Model_AuthSessionsMapper extends Application_Model_AbstractMapper {
	
	
	protected $_dbTable;
	protected static $_instance = null;
	protected function getMappedClass() { return 'Application_Model_AuthSession'; }
	protected function getDbTableClass() { return 'Application_Model_DbTable_AuthSessions'; }
	/**
	 * Singleton
	 * @return Application_Model_AuthSessionsMapper
	 */
	static public function i() {
		if ( self::$_instance == null )
			self::$_instance = new self();
		return self::$_instance;
	}
	
	/**
	 * Store model in the db
	 * if $model->id is null, add a new row
	 * @param Application_Model_AuthSession $model
	 */
	public function save(Application_Model_AuthSession  $model) {
						
		$data = array( 
			'ip'			=> $model->getIp(),
			'useragent'		=> $model->getUserAgent(),
			'created'		=> $model->getCreated(),
			'username'		=> $model->getUsername()
		);

		if ($model->isNew()) {
			//unset ( $data ['id'] );
			$this->getDbTable ()->insert ( $data );
			//$model->setId($id);
		} else {
			$this->getDbTable ()->update ( $data, array ('ip = ?' => $model->getIp(), 'useragent = ?' => $model->getUserAgent() ) );
		}
	}

	
	public function fetchByIpUserAgent($ip, $userAgent, Application_Model_AuthSession $model = null) {
		$result = $this->getDbTable ()->fetchAll ( array('ip = ?' => $ip, 'useragent = ?' => $userAgent));
		if (0 == count ( $result )) {
			return false;
		}
		if ( $model !== null ) {
			$row = $result->current ();
			$this->_populate($row, $model);
		}
		return true;
	}
	
	
	/**
	 * 
	 * @param unknown_type $row
	 * @param Application_Model_AuthSession $model
	 */
	protected function _populate($row, $model) {
		$model->setIp($row->ip)
			->setUserAgent($row->useragent)
			->setCreated($row->created)
			->setUsername($row->username)
			->setNew(false);
	}
	
	public function delete(Application_Model_AuthSession $model) {
		$this->getDbTable()->delete(array('ip = ?' => $model->getIp(), 'useragent = ?' => $model->getUserAgent()));
	}

	public function clearSessions($ip, $useragent) {
		$this->getDbTable()->delete(array('ip = ?' => $ip, 'useragent = ?' => $useragent));
	}
	
	
	public function clearInvalid($time = null) {
		if ( $time === null ) {
			$time = time() - (24 * 60 * 60); // 24h * 60m * 60s = 1 day in seconds
		}
		$this->getDbTable()->delete(array('created < ?' => $time));
	}
	
	public function getCount() {
		$select = $this->getDbTable()->select()->from($this->getDbTable()->info(Zend_Db_Table_Abstract::NAME), 'COUNT(ip) as num');
		return $this->getDbTable()->fetchRow($select)->num;
	}
}

