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
namespace OCA\User_Servervars2\Backend;

use OCA\User_Servervars2\Service\Context;
use OCA\User_Servervars2\Backend\UserBackend;

class UserBackendTest extends \Test\TestCase {

	var $backend;
	var $tokenService;
	var $appConfig;

	public function setUp() {

		parent::setUp();

		$this->tokenService = $this->getMockBuilder('OCA\User_Servervars2\Service\TokenService')
								->disableOriginalConstructor()
								->getMock();

		$this->proxiedBackend = $this->getMockBuilder('\OC_User_Interface')
								->disableOriginalConstructor()
								->getMock();

		$this->appConfig = new LocalAppConfig();

		$this->backend = new UserBackend( $this->tokenService, $this->appConfig, $this->proxiedBackend );

	}

	//=========================================================================
	//
	//   -- CheckPassword tests
	//
	//=========================================================================

	public function testCheckPassword() {
		//__GIVEN__
		$this->tokenService->expects($this->any())->method('checkTokens')->willReturn('jean.gabin@myidp.org');

		//__WHEN__
		$this->backend->startLoginCycle('jean.gabin@myidp.org');
		$returnedValue = $this->backend->checkPassword('jean.gabin@myidp.org','mlkdfmuxm');

		//_THEN__
		$this->assertEquals('jean.gabin@myidp.org', $returnedValue);
	}


	public function testCheckPasswordWithoutLoginCycle() {
		//__GIVEN__
		$this->tokenService->expects($this->any())->method('checkTokens')->willReturn('jean.gabin@myidp.org');

		//__WHEN__
		$returnedValue = $this->backend->checkPassword('jean.gabin@myidp.org','mlkdfmuxm');

		//_THEN__
		$this->assertEquals(false, $returnedValue);
	}

	public function testCheckPasswordNoUid() {
		//__GIVEN__
		$this->tokenService->expects($this->any())->method('checkTokens')->willReturn('jean.gabin@myidp.org');

		//__WHEN__
		$this->backend->startLoginCycle('jean.gabin@myidp.org');
		$returnedValue = $this->backend->checkPassword('','mlkdfmuxm');

		//_THEN__
		$this->assertFalse($returnedValue);
	}

	public function testCheckPasswordNotSameUid() {
		//__GIVEN__
		$this->tokenService->expects($this->any())->method('checkTokens')->willReturn('another@myidp.org');

		//__WHEN__
		$this->backend->startLoginCycle('jean.gabin@myidp.org');
		$returnedValue = $this->backend->checkPassword('jean.gabin@myidp.org','mlkdfmuxm');

		//_THEN__
		$this->assertFalse($returnedValue);
	}


	public function testCheckPasswordCheckFailed() {
		//__GIVEN__
		$this->tokenService->expects($this->any())->method('checkTokens')->willReturn(false);

		//__WHEN__
		$this->backend->startLoginCycle('jean.gabin@myidp.org');
		$returnedValue = $this->backend->checkPassword('jean.gabin@myidp.org','mlkdfmuxm');

		//_THEN__
		$this->assertFalse($returnedValue);
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
