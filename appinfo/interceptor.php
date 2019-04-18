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
namespace OCA\User_Servervars2\AppInfo;

use \OCA\User_Servervars2\Service\Tokens;
use \OCA\User_Servervars2\Service\UserAndGroupService;

class Interceptor {


	var $tokens;
	var $appConfig;
	var $uag;
	var $throwExceptionToExit = false;


	function __construct($appConfig, Tokens $tokens, UserAndGroupService $userAndGroupService, $redirector=null) {
		$this->appConfig = $appConfig;
		$this->tokens = $tokens;
		$this->uag = $userAndGroupService;
		$this->redirector = $redirector;
		if ( $this->redirector === null ) {
			$this->redirector = new DefaultRedirector();
		}
	}
	/**
	* To avoid infinite loop it used TWO differents app parameter
	*/
	function checkGet($name, $value) {
		return (isset($_GET[$name]) && $_GET[$name] == $value);
	}



	/**
	*
	*/
	function run() {
		if( $this->checkGet('app','usv2') ) {

			$uid = $this->tokens->getUserId();
			$providerId = $this->tokens->getProviderId();
			$userConfig = $this->uag->config;

			if (strpos($providerId,'janus') || strpos($providerId, 'shibboleth')){
					if($userConfig->getUserValue($uid, 'owncloud', 'isGuest', false) == 1){
							$userConfig->setUserValue($uid,'owncloud','isGuest','0');
					}
			}

			if ( $uid === false || $uid === null) {
				if (  $this->appConfig->getValue('user_servervars2','stop_if_empty',false) ) {
					throw new \Exception('token error');
				}
				// Danger: possibilité de fabriquer une boucle avec janus
				$ssoURL = $this->appConfig->getValue('user_servervars2', 'sso_url', 'http://localhost/sso');
				$this->redirector->redirectTo($ssoURL);

			} else {

				$isLoggedIn = $this->uag->isLoggedIn();

				if ( ! $isLoggedIn ) {

					$userId = $this->uag->checkUserPassword($uid);
					if ($userId !== false) {
						$this->uag->provisionUser($uid, $this->tokens);
					}

					$isLoggedIn = $this->uag->login($uid);
				}
				if ( ! $isLoggedIn ) {
					// if ( !$this->uag->isLoggedIn())  {
					\OC\Log\Owncloud::write('servervars',
						'Error trying to log-in the user' . $uid,
						\OCP\Util::DEBUG);
					return;
				}

				\OC::$REQUESTEDAPP = '';
				$this->redirector->redirectToDefaultPage();
			}
		}
	}

	function doesExit(){
		if ($this->throwExceptionToExit ) {
			throw new \Exception('exit');
		} else {
			exit();
		}
	}
}
