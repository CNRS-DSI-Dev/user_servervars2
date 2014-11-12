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


class Interceptor {


	var $tokens;
	var $appConfig;
	var $uag;
	var $throwExceptionToExit = false;


	function __construct($appConfig, $tokens, $userAndGroupService, $URLGenerator, $redirector=null) {
		$this->appConfig = $appConfig;
		$this->tokens = $tokens;
		$this->uag = $userAndGroupService;
		$this->nextURL = 'apps/user_servervars2/api/deferred/provisionning';
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
					$isLoggedIn = $this->uag->login($uid); 
				}
				if ( ! $isLoggedIn ) {
					// if ( !$this->uag->isLoggedIn())  {
					\OC_Log::write('servervars',
						'Error trying to log-in the user' . $uid,
						\OC_Log::DEBUG);
					return;
				}

				\OC::$REQUESTEDAPP = '';
				//$this->redirector->redirectToDefaultPage();
				$this->redirector->redirectTo( 'apps/user_servervars2/api/deferred/provisionning' );
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