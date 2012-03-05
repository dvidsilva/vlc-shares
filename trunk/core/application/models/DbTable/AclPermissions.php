<?php

class Application_Model_DbTable_AclPermissions extends Zend_Db_Table_Abstract
{

    protected $_name = 'plg_acl_permissions';
    protected $_primary = array('username', 'class');
    

}

