<?php 


class X_Threads_Monitor_Db_Mapper {
	
	/**
	 * @var Zend_Db_Table_Abstract
	 */
	protected $dbTable = null;
	
	function __construct(Zend_Db_Table_Abstract $dbTable) {
		$this->dbTable = $dbTable;
	}
	
	public function fetchAll() {

		$resultSet = $this->dbTable->fetchAll ();
		$entries = array ();
		foreach ( $resultSet as $row ) {
			$entries[] = array(
				'id' => $row->id,
				'state' => $row->state,
				'info' => $row->info,
				'lastupdate' => $row->lastupdate,
				'sticked' => $row->sticked
			);
		}
		return $entries;
		
	}
	
	public function find($id) {
		
		$result = $this->dbTable->find ( $id );
		if (0 == count ( $result )) {
			return false;
		}
		$row = $result->current ();
		
		return array(
			'id' => $row->id,
			'state' => $row->state,
			'info' => $row->info,
			'lastupdate' => $row->lastupdate,
			'sticked' => $row->sticked
		);
		
	}
	
	public function delete($id) {
		$this->dbTable->delete(array('id = ?' => $id));
	}
	
	public function update($id, $state, $info) {
		
		$data = array( 
			'state'			=> $state,
			'info'			=> $info,
			'lastupdate'	=> time()
		);

		$found = $this->find($id);
		
		if ( $found ) {
			
			$data = array_merge($found, $data);
			unset($data['id']);
			$this->dbTable->update($data, array('id = ?' => $id));
			
		} else {
			
			$data['id'] = $id;
			$data['sticked'] = 0;
			
			$this->dbTable->insert ( $data );
		}
		
	}
	
}
