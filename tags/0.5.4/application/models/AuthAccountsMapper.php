<?php

class Application_Model_AuthAccountsMapper extends Application_Model_AbstractMapper {
	
	
	protected $_dbTable;
	protected static $_instance = null;
	protected function getMappedClass() { return 'Application_Model_AuthAccount'; }
	protected function getDbTableClass() { return 'Application_Model_DbTable_AuthAccounts'; }
	/**
	 * Singleton
	 * @return Application_Model_AuthAccountsMapper
	 */
	static public function i() {
		if ( self::$_instance == null )
			self::$_instance = new self();
		return self::$_instance;
	}
	
	/**
	 * Store model in the db
	 * if $model->id is null, add a new row
	 * @param Application_Model_AuthAccount $model
	 */
	public function save(Application_Model_AuthAccount  $model) {
						
		$data = array(
			'username' => $model->getUsername(),
			'password' => $model->getPassword(),
			'passphrase' => $model->getPassphrase(),
			'enabled' => (int) $model->isEnabled(),
			'altAllowed' => (int) $model->isAltAllowed()
		);

		if (null === ($id = $model->getId())) {
			unset ( $data ['id'] );
			$id = $this->getDbTable ()->insert ( $data );
			$model->setId($id);
		} else {
			$this->getDbTable ()->update ( $data, array ('id = ?' => $id ) );
		}
	}

	/**
	 * Try to fetch account information by username/password
	 * @param string $username
	 * @param string $password
	 * @param Application_Model_AuthAccount $model account informations stored here if found
	 * @return boolean
	 */
	public function fetchByUsernamePassword($username, $password, Application_Model_AuthAccount $model = null) {
		$result = $this->getDbTable ()->fetchAll ( array(
			'username = ?' => $username,
			'password = ?' => md5("$username:$password"),
			'enabled = ?' => true,
		));
		if (0 == count ( $result )) {
			return false;
		}
		if ( $model != null ) {
			$row = $result->current ();
			$this->_populate($row, $model);
		}
		return true;
	}

	/**
	 * Try to fetch account information by username
	 * @param string $username
	 * @param Application_Model_AuthAccount $model account informations stored here if found
	 * @return boolean
	 */
	public function fetchByUsername($username, Application_Model_AuthAccount $model = null) {
		$result = $this->getDbTable ()->fetchAll ( array(
			'username = ?' => $username
		));
		if (0 == count ( $result )) {
			return false;
		}
		if ( $model != null ) {
			$row = $result->current ();
			$this->_populate($row, $model);
		}
		return true;
	}
	
	
	/**
	 * Try to fetch account information by username/password
	 * @param string $username
	 * @param string $password
	 * @param Application_Model_AuthAccount $model account informations stored here if found
	 * @return boolean
	 */
	public function fetchByUsernamePassphrase($username, $passphrase, Application_Model_AuthAccount $model = null) {
		$result = $this->getDbTable ()->fetchAll ( array(
			'username = ?' => $username,
			'passphrase = ?' => $passphrase,
			'enabled = ?' => true,
			'altAllowed = ?' => true,
		));
		
		if (0 == count ( $result )) {
			return false;
		}
		if ( $model != null ) {
			$row = $result->current ();
			$this->_populate($row, $model);
		}
		return true;
	}
	
	
	
	/**
	 * 
	 * @param unknown_type $row
	 * @param Application_Model_AuthAccount $model
	 */
	protected function _populate($row, $model) {
		$model->setId($row->id)
			->setUsername($row->username)
			->setPassword($row->password)
			->setPassphrase($row->passphrase)
			->setEnabled($row->enabled)
			->setAltAllowed($row->altAllowed);
	}
	
	public function delete(Application_Model_AuthAccount $model) {
		$this->getDbTable()->delete(array('id = ?' => $model->getId()));
	}
	
	
	public function getCount($enabledOnly = false) {
		$select = $this->getDbTable()->select()->from($this->getDbTable()->info(Zend_Db_Table_Abstract::NAME), 'COUNT(id) as num');
		if ( $enabledOnly ) {
			$select->where("enabled = ?", (int) true);
		}
		return $this->getDbTable()->fetchRow($select)->num;
	}
}

