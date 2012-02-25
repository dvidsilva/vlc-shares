<?php
/*
 * This file is part of the VlcShares PutLocker-plugin 0.1.
 *
 * Author: Jan Holthuis <holthuis.jan@googlemail.com>
 *
 *  The VlcShares PutLocker-plugin is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The VlcShares PutLocker-plugin is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with the VlcShares PutLocker-plugin.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class X_VlcShares_Plugins_PutLocker extends X_VlcShares_Plugins_Abstract {
	
    const VERSION = '0.1.1';
    const VERSION_CLEAN = '0.1.1';
	
	function __construct() {
		
		$this
			->setPriority('gen_beforeInit');
		
	}
	
	/**
	 * Registers a veoh hoster inside the hoster broker
	 */
	public function gen_beforeInit(Zend_Controller_Action $controller) {
		
		$this->helpers()->language()->addTranslation(__CLASS__);
		$this->helpers()->hoster()->registerHoster(new X_VlcShares_Plugins_Helper_Hoster_PutLocker());
		
	}
	
}
