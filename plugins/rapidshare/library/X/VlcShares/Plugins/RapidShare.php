<?php
/**
 * This file is part of the vlc-shares project by Francesco Capozzo (ximarx) <ximarx@gmail.com>
 *
 * @author: Francesco Capozzo (ximarx) <ximarx@gmail.com>
 *
 * vlc-shares is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * vlc-shares is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with vlc-shares.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

class X_VlcShares_Plugins_RapidShare extends X_VlcShares_Plugins_Abstract {
	
    const VERSION = '0.1';
    const VERSION_CLEAN = '0.1';
	
    const MESSAGE_PREMIUM_EXPIRED = 'p_rapidshare_error_premiumexpired';
    const MESSAGE_LOGIN_INVALID = 'p_rapidshare_error_invalidlogin';
    
    private $countdown = 0;
    private $error_message = false;
    
	function __construct() {
		
		$this
			->setPriority('getIndexManageLinks')
			->setPriority('getIndexMessages')
			->setPriority('prepareConfigElement')
			->setPriority('gen_beforeInit');
		
	}
	
	/**
	 * Registers a veoh hoster inside the hoster broker
	 */
	public function gen_beforeInit(Zend_Controller_Action $controller) {
		
		$this->helpers()->language()->addTranslation(__CLASS__);
		$this->helpers()->hoster()->registerHoster(new X_VlcShares_Plugins_Helper_Hoster_RapidShare(array(
					'premium' => $this->config('premium.enabled', false),
					'username' => $this->config('premium.username', ''),
					'password' => $this->config('premium.password', ''),
					'timeout' => $this->config('request.timeout', 25)
				)));
		
	}
	
	/**
	 * Set priority for getModeItems to show a message with a countdown warning
	 * @param string|int $countdown
	 */
	public function setCountDownMessage($countdown) {
		$this->setPriority('getModeItems', 0); // top priority
		$this->countdown = $countdown;
	}
	
	/**
	 * Set an error message
	 * @param unknown_type $message
	 */
	public function setInvalidPremiumMessage($message) {
		$this->setPriority('getModeItems', 0); // top priority
		$this->error_message = $message;
	}
	
	/* (non-PHPdoc)
	 * @see X_VlcShares_Plugins_Abstract::getModeItems()
	 */
	public function getModeItems($provider, $location, Zend_Controller_Action $controller) {
		
		$list = new X_Page_ItemList_PItem();
		
		$urlHelper = $controller->getHelper('url');
		
		if ( $this->countdown > 0 ) {
			
			$countdown = new X_Page_Item_PItem('core-countdown', X_Env::_('core_countdown', $this->countdown));
			$countdown->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setIcon("/images/{$this->getId()}/logo.png")
				->setLink(
					X_Env::completeUrl(
							$urlHelper->url()
					));
			$list->append($countdown);
		}

		if ( $this->error_message ) {
				
			$error = new X_Page_Item_PItem('core-error', X_Env::_($this->error_message));
			$error->setType(X_Page_Item_PItem::TYPE_ELEMENT)
				->setIcon("/images/{$this->getId()}/logo.png")
				->setLink(
					X_Env::completeUrl(
							$urlHelper->url()
					));
			$list->append($error);
		}
		
		return $list;
		
	}
	
	/**
	 * Add the link for -manage-streamingonline-
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_ManageLink
	 */
	public function getIndexManageLinks(Zend_Controller_Action $controller) {
		if ( class_exists("X_VlcShares_Plugins_Utils", true) ) {
			return X_VlcShares_Plugins_Utils::getIndexManageEntryList($this->getId());
		}
	}
	
	/**
	 * Show an error message if one of the plugin dependencies is missing
	 * @param Zend_Controller_Action $this
	 * @return X_Page_ItemList_Message
	 */
	public function getIndexMessages(Zend_Controller_Action $controller) {
		$messages = new X_Page_ItemList_Message();
	
		if ( !class_exists("X_VlcShares_Plugins_Utils", true) ) {
			$message = new X_Page_Item_Message($this->getId(),"PageParser API is required from RapidShare. Please, install PageParserLib plugin");
			$message->setType(X_Page_Item_Message::TYPE_FATAL);
			$messages->append($message);
		}
		return $messages;
	}	
	
	/**
	 * Convert form password to password element
	 * @param string $section
	 * @param string $namespace
	 * @param unknown_type $key
	 * @param Zend_Form_Element $element
	 * @param Zend_Form $form
	 * @param Zend_Controller_Action $controller
	 */
	public function prepareConfigElement($section, $namespace, $key, Zend_Form_Element $element, Zend_Form  $form, Zend_Controller_Action $controller) {
			// nothing to do if this isn't the right section
		if ( $namespace != $this->getId() ) return;
		
		switch ($key) {
			// i have to convert it to a password element
			case 'plugins_rapidshare_premium_password':
				$password = $form->createElement('password', 'plugins_rapidshare_premium_password', array(
					'label' => $element->getLabel(),
					'description' => $element->getDescription(),
					'renderPassword' => true,
				));
				$form->plugins_rapidshare_premium_password = $password;
				break;
		}
		
		// remove cookie if somethings has value
		if ( !$form->isErrors() && !is_null($element->getValue()) && $key = 'plugins_rapidshare_premium_password' ) {
			// clean cookies
			try {
				X_Debug::i("Cleaning up cookies in cache");
				/* @var $cacheHelper X_VlcShares_Plugins_Helper_Cache */
				$cacheHelper = X_VlcShares_Plugins::helpers()->helper('cache');
				try {
					$cacheHelper->retrieveItem("rapidshare::cookie");
					// set expire date to now!
					$cacheHelper->storeItem("rapidshare::cookie", '', 0);
				} catch (Exception $e) {
					// nothing to do
				}
				try {
					$cacheHelper->retrieveItem("rapidshare::lastreloginflag");
					// set expire date to now!
					$cacheHelper->storeItem("realdebrid::lastreloginflag", '', 0);
				} catch (Exception $e) {
					// nothing to do
				}
			} catch (Exception $e) {
				X_Debug::w("Cache plugin disabled? O_o");
			}
		}
	}	

}
