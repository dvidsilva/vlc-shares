<?php 


class X_VlcShares_Plugins_Helper_Acl extends X_VlcShares_Plugins_Helper_Abstract {
	
	// {{{ caches
	private $checkCache = array();
	private $permissionsCache = array();
	private $classesCache = array();
	private $resourcesCache = array();
	// }}}
	
	// {{{ singleton to force cache persistence
	private static $_instance = null;
	
	protected function __construct(Zend_Config $options) {}
	
	public static function instance(Zend_Config $options) {
		if ( self::$_instance === null ) {
			self::$_instance = new self($options);
		}
		return self::$_instance;
	}
	// }}}
	
	/**
	 * Check if idAccount can access a resource
	 * @param id $username Account username
	 * @param string $resourceKey Resource key
	 * @return booblean
	 */
	public function canUse($username, $resourceKey) {

		// check cache first
		if ( isset($this->checkCache[$username]) && isset($this->checkCache[$username][$resourceKey]) ) {
			return $this->checkCache[$username][$resourceKey];
		} 
		
		//X_Debug::i("Checking username {{$username}} permissions for {{$resourceKey}}");
		
		$class = $this->getResourceDescriptor($resourceKey)->getClass();
		
		//X_Debug::i("Required permission class {{$class}}");
		
		$permissions = $this->getPermissions($username);
		
		//X_Debug::i("User permissions: ".print_r($permissions, true));

		$valid = ( $class == Application_Model_AclClass::CLASS_ANONYMOUS || in_array(Application_Model_AclClass::CLASS_ADMIN, $permissions) || in_array($class, $permissions));
		$this->checkCache[$username][$resourceKey] = $valid;
		
		return $valid;
		
	}
	
	public function canUseAnonymously($resourceKey) {
		return ($this->getResourceDescriptor($resourceKey)->getClass() == Application_Model_AclClass::CLASS_ANONYMOUS);
	}
	
	/**
	 * Grant permission to ad account id for a resource class
	 * 
	 * @param int $authId account id
	 * @param int $class class id
	 * @return booleam
	 */
	public function grantPermission($username, $class) {

		$permission = new Application_Model_AclPermission();
		// try to load an identical permission
		Application_Model_AclPermissionsMapper::i()->findPermission($username, $class, $permission);
		if ( $permission->isNew() ) {
		
			$permission->setUsername($username);
			$permission->setClass($class);
			
			try {
				Application_Model_AclPermissionsMapper::i()->save($permission);
				X_Debug::i("Permission granted for {username: {$username}, class: {$class}}");
				return true;
			} catch (Exception $e) {
				X_Debug::e("Error granting permission: {$e->getMessage()}");
				return false;
			}
			
		} else {
			X_Debug::w("Permission already granted for {username: {$username}, class: {$class}}");
			return true;
		}
		
	} 
	
	/**
	 * Revoke a permission to an userId for a classId
	 * @param string $username
	 * @param string $class
	 * @return boolean
	 */
	public function revokePermission($username, $class) {
		
		$permission = new Application_Model_AclPermission();
		Application_Model_AclPermissionsMapper::i()->findPermission($username, $class, $permission);
		
		if ( !$permission->isNew() ) {
		
			try {
				Application_Model_AclPermissionsMapper::i()->delete($permission);
				X_Debug::i("Permission revoked for {username: {$username}, class: {$class}}");
				return true;
			} catch (Exception $e) {
				X_Debug::e("Error revoking permission: {$e->getMessage()}");
				return false;
			}
				
		} else {
			X_Debug::w("Permission already revoked for {username: {$username}, class: {$class}}");
			return true;
		}
		
	}
	
	/**
	 * Add a new permission class
	 * @param string $className Class name
	 * @param string $classDescription class description
	 * @return boolean
	 */
	public function addClass($className, $classDescription) {
		$class = new Application_Model_AclClass();
		Application_Model_AclClassesMapper::i()->find($className, $class);
		if ( $class->isNew() ) {
			$class->setName($className);
			$class->setDescription($classDescription);
			try {
				Application_Model_AclClassesMapper::i()->save($class);
				X_Debug::i("Permission class added {{$className}}");
				return true;
			} catch (Exception $e) {
				X_Debug::e("Error adding class: {$e->getMessage()}");
				return false;
			}
		} else {
			X_Debug::w("Permission class exists {{$className}}");
			return true;
		}
	}
	
	/**
	 * Remove a permission class
	 * @param string $className
	 * @throws Exception if permission class is one of the core ones
	 * @return boolean
	 */
	public function removeClass($className) {
		
		// check if the class is one of the core ones
		if ( in_array($className, array(
				Application_Model_AclClass::CLASS_ADMIN,
				Application_Model_AclClass::CLASS_BROWSE,
				Application_Model_AclClass::CLASS_ANONYMOUS
				) ) ) {
			throw new Exception("The class {{$className}} can't be removed because is a core ones");
		}
		
		$class = new Application_Model_AclClass();
		Application_Model_AclClassesMapper::i()->find($className, $class);
		if ( !$class->isNew() ) {
			try {
				Application_Model_AclClassesMapper::i()->delete($class);
				X_Debug::i("Permission class removed {{$className}}");
				return true;
			} catch (Exception $e) {
				X_Debug::e("Error removing class: {$e->getMessage()}");
				return false;
			}
		} else {
			X_Debug::w("Permission class doesn't exists {{$className}}}");
			return true;
		}
	}
	
	/**
	 * Add a new resource if not available or edit an old one if $allowChange is present
	 * @param string $resourceKey
	 * @param string $class
	 * @param string $generator
	 * @param boolean $allowChange
	 * @return boolean
	 */
	public function addResource( $resourceKey, $class = Application_Model_AclClass::CLASS_BROWSE, $generator = 'auth', $allowChange = false ) {
		$resource = new Application_Model_AclResource();
		Application_Model_AclResourcesMapper::i()->find($resourceKey, $resource);
		if ( $allowChange || $resource->isNew() ) {
			$resource->setKey($resourceKey);
			$resource->setClass($class);
			// allow generator change only if this is
			// a new resource
			if ( $resource->isNew() ) {
				$resource->setGenerator($generator);
			}
			try {
				Application_Model_AclResourcesMapper::i()->save($resource);
				X_Debug::i("Resource setted {{$resourceKey}}");
				return true;
			} catch (Exception $e) {
				X_Debug::e("Error setting resource: {$e->getMessage()}");
				return false;
			}
		} else {
			 X_Debug::e("Resource already exists and edit is not allowed {{$resourceKey}}}");
			return false;
		}
	} 
	
	public function removeResource($key) {
		$resource = new Application_Model_AclResource();
		Application_Model_AclResourcesMapper::i()->find($key, $resource);
		if ( !$resource->isNew() ) {
			try {
				Application_Model_AclResourcesMapper::i()->delete($resource);
				X_Debug::i("Resource removed {{$key}}");
				return true;
			} catch (Exception $e) {
				X_Debug::e("Error removing resource: {$e->getMessage()}");
				return false;
			}
		} else {
			X_Debug::w("Resource doesn't exists {{$key}}}");
			return true;
		}
	}
	
	
	public function getPermissions($username) {
		// check cache first
		if ( isset($this->permissionsCache[$username])) {
			return $this->permissionsCache[$username];
		}
		$permissions = Application_Model_AclPermissionsMapper::i()->fetchAllByUsername($username);
		
		$permissionsC = array();
		
		foreach ($permissions as $permission) {
			$permissionsC[] = $permission->getClass();
		}
		
		$this->permissionsCache[$username] = $permissionsC;
		
		return $permissionsC;
	}

	public function getResourceDescriptor($resourceKey) {
		// check cache first
		if ( isset($this->resourcesCache[$resourceKey])) {
			return $this->resourcesCache[$resourceKey];
		}
		
		$resourceDescriptor = new Application_Model_AclResource();
		Application_Model_AclResourcesMapper::i()->find($resourceKey, $resourceDescriptor);
		$this->resourcesCache[$resourceKey] = $resourceDescriptor;
		
		return $resourceDescriptor;
	}
	
	/**
	 * Get all availables classes registered
	 * @return array[Application_Model_AclClass]
	 */
	public function getClasses() {
		if ( !count($this->classesCache) ) {
			$this->classesCache = Application_Model_AclClassesMapper::i()->fetchAll();
		}
		return  $this->classesCache;
	}
	
}

