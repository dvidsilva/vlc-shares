<?php

interface X_Streamer_StopperEngine {
	
	/**
	 * Execute a custom operation before threads::halt is called
	 * 
	 * @param array $threadInfo last info about the thread
	 * @return X_Streamer_StopperEngine
	 */
	public function doStop($threadInfo);
	
}

