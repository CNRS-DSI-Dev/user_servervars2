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
use OCA\User_Servervars2\Hook\ServerVarsHooks;


class ServerVarsHooksTest extends \Test\TestCase {

	var $hooks;
	var $tokenService;
	var $tokens;
	var $uag;
	var $user;
	var $appConfig;

	public function setUp() {
		parent::setUp();

		$this->tokenService = $this->getMockBuilder('OCA\User_Servervars2\Service\TokenService')
								->disableOriginalConstructor()
								->getMock();

		$this->tokens = $this->getMockBuilder('OCA\User_Servervars2\Service\Tokens')
								->disableOriginalConstructor()
								->getMock();

		$this->uag 	= $this->getMockBuilder('OCA\User_Servervars2\Service\UserAndGroupService')
								->disableOriginalConstructor()
								->getMock();

		$this->appConfig 	= new LocalAppConfig();

		$this->hooks 		= new ServerVarsHooks(	$this->tokenService,
													$this->uag,
													$this->appConfig);

		$this->user = $this->getMockBuilder('\OC\User\User')
								->disableOriginalConstructor()
								->getMock();

	}


	/**
	 * Test in mock mode.
	 *
	 */
	public function testOnPostLoginTokensOk() {
		//__GIVEN__
		$this->user->expects( $this->once() )->method('getUID')->willReturn('uid@myidp.org');

		//-------------------------------------------------------------------------------------------
		$this->tokenService->expects( $this->once() )->method('checkTokens')->willReturn( 'uid@myidp.org' );
		//-------------------------------------------------------------------------------------------

		$this->tokenService->expects( $this->once() )->method('getTokens')->willReturn( $this->tokens );
		$this->uag->expects( $this->once() )->method('provisionUser')->with('uid@myidp.org', $this->tokens)->willReturn( false );

		//__THEN__
		$this->hooks->onPostLogin( $this->user, 'password');

	}

	/**
	 * Test in mock mode.
	 *
	 */
	public function testOnPostLoginTokensNOTOk() {
		//__GIVEN__
		$this->user->expects( $this->once() )->method('getUID')->willReturn('uid@myidp.org');

		//-------------------------------------------------------------------------------------------
		$this->tokenService->expects( $this->once() )->method('checkTokens')->willReturn( false );
		//-------------------------------------------------------------------------------------------

		$this->tokenService->expects( $this->never() )->method('getTokens');
		$this->uag->expects( $this->never() )->method('provisionUser');

		//__THEN__
		$this->hooks->onPostLogin( $this->user, 'password');

	}

}



class LocalAppConfig {
 	var $data;
 	var $callCount = 0;

 	function getValue($appName, $key, $default=null) {
 		$this->callCount++;
 		if ( isset($this->data[$appName]) && isset($this->data[$appName][$key])) {
 			return $this->data[$appName][$key];
 		}
 		return $default;
 	}
 }
