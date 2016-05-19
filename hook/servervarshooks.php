<?php
/**
 * ownCloud -
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

namespace OCA\User_Servervars2\Hook;

use \OCP\User;

/**
 *
 */
class ServerVarsHooks {

	var $tokenService;
	var $userAndGroupService;
	var $redirector;
	var $appConfig;


	function __construct($tokenService, $userAndGroupService, $appConfig, $redirector=null) {
		$this->tokenService = $tokenService;
		$this->userAndGroupService = $userAndGroupService;
		$this->redirector = $redirector;
		if ( $this->redirector === null ) {
			$this->redirector = new \OCA\User_Servervars2\AppInfo\DefaultRedirector();
		}
		$this->appConfig = $appConfig;
	}


	function onPostLogin($user, $password) {

		$justCreatedUser = null;
		$uag = $this->userAndGroupService;
		$uid = $user->getUID();

		if ( $uid === $this->tokenService->checkTokens() ) {
			$justCreatedUser = $uag->provisionUser($uid, $this->tokenService->getTokens() );
		}
	}




	function register($userSession) {
		$obj = $this;
		// $userSession->listen('\OC\User', 'postLogin', function($user, $password) use(&$obj) {
		// 	return $obj->onPostLogin($user, $password);
		// });

		$userSession->listen('\OC\User', 'logout', function() use(&$obj) {
			$sloUrl = $this->appConfig->getValue('user_servervars2','slo_url');
			if ( ! empty($sloUrl) ) {
				$this->redirector->redirectTo($sloUrl);
			}
		});
	}
}
