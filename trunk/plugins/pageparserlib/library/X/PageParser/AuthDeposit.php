<?php

abstract class X_PageParser_AuthDeposit {
	
	/**
	 * Store an auth credential in the deposit
	 * @param string $key
	 * @param string $value
	 * @param int $validity a suggested validity for the auth in seconds (concrete implementation could ignore this param)
	 * @throws Exception if out of places or generic error
	 */
	public abstract function store($key, $value, $validity = null);
	/**
	 * Retrive a store auth credential from the deposit
	 * @param string $key
	 */
	public abstract function retrieve($key);
	
}