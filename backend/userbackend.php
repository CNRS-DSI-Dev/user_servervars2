<?php
/**
 * ownCloud - usershibbbackend.php
 *
 * @author Marc DeXeT
 * @copyright 2014 DSI CNRS https://www.dsi.cnrs.fr
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */
namespace OCA\User_Servervars2\Backend;

use OCA\User_Servervars2\Service\TokenService;
use OC\AppConfig;
/**
 * UserBackend
 *
 */
class UserBackend extends \OC_User_Backend { //implements \OC_User_Interface {


	var $tokenService;
	var $proxiedBackend;
	var $autoCreateUser;
	var $updateUserData;
	var $isUpdateGroups;
	var $defaultGroups;
	var $protectedGroups;
	var $config;
	var $currentUid = null;


	public function __construct(TokenService $tokenService, $config, $backend=null) { //, \OC_User_Interface $proxiedBackend = null
		$this->tokenService = $tokenService;

		$this->autoCreateUser = false;
		$autoCreateUser = $config->getValue('user_servervars2', 'auto_create_user','false');
		if ($autoCreateUser !== 'false') {
				$this->autoCreateUser = true;
		}

		$this->updateUserData = true;
		$updateUser = $config->getValue('user_servervars2', 'update_user','true');
		if ($updateUser !== 'true') {
				$this->updateUserData = false;
		}

		$this->isUpdateGroups = true;
		$updateGroups = $config->getValue('user_servervars2', 'update_groups','true');
		if ($updateGroups !== 'true') {
				$this->isUpdateGroups = false;
		}

		$this->defaultGroups = false; //$config->getValue('user_servervars2', 'auto_create_user');
		$this->protectedGroups = false;
		$this->proxiedBackend = $backend;
		if ( is_null($this->proxiedBackend) ) {
			$this->proxiedBackend = new \OC\User\Database();
		}
	}

	/**
	* Flag for Login cycle because of IDP cookies are persistant
	*/
	public function startLoginCycle($uid) {
		$this->currentUid = $uid;
	}

	/**
	* Flag for Login cycle because of IDP cookies are persistant
	*/
	public function endLoginCycle($uid) {
		$this->currentUid = null;
	}

	/**
	* @see \OC\User\manager::checkPassword
	*/
	public function checkPassword($uid, $password) {
		if ( $uid !== $this->currentUid ) return FALSE;

		if ( $uid === $this->tokenService->checkTokens() ){
			return $uid;
		}
		return false;
	}

	function isAutoCreateUser() {
		return $this->autoCreateUser;
	}

	function isUpdateUserData() {
		return $this->updateUserData;
	}


	function isUpdateGroups() {
		return $this->isUpdateGroups;
	}

	function getDefaultGroups() {
		return $this->defaultGroups;
	}

	function getProtectedGroups() {
		return $this->protectedGroups;
	}



	//--------------------------------------------------------------------------
	// PROXYING
	//--------------------------------------------------------------------------
/*	public function implementsActions($actions) {
		if ( \OC_USER_BACKEND_CHECK_PASSWORD & $actions )  return true;
		return $this->proxiedBackend->implementsActions($actions);
	}

	public function __call($name, $arguments) {
		call_user_func_array(array($this->proxiedBackend, $name), $arguments);
	}*/


}
