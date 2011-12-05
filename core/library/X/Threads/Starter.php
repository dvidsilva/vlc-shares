<?php 

abstract class X_Threads_Starter {
	
	abstract function spawn($threadId);
	abstract function isValid($keyId, $hash, $salt);
	
}
