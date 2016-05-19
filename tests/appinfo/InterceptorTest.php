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

class UserBackendTest extends \Test\TestCase {

	var $interceptor;
	var $appConfig;
	var $tokens;
	var $userAndGroupService;
	var $redirector;

	public function setUp() {

		$this->appConfig = new LocalAppConfig();

		parent::setUp();

		$this->tokens = $this->getMockBuilder('OCA\User_Servervars2\Service\Tokens')
								->disableOriginalConstructor()
								->getMock();

		$this->userAndGroupService =  $this->getMockBuilder('OCA\User_Servervars2\Service\UserAndGroupService')
								->disableOriginalConstructor()
								->getMock();

		$this->redirector =  $this->getMockBuilder('OCA\User_Servervars2\AppInfo\Redirector')
								->disableOriginalConstructor()
								->getMock();

		$this->interceptor = new Interceptor($this->appConfig, $this->tokens, $this->userAndGroupService, $this->redirector);
		$this->interceptor->throwExceptionToExit = true;

	}


	public function testGlobalInPhpUnit() {
		// GIVEN
		$this->assertArrayNotHasKey('app', $_GET);
		$_GET['app'] = 'foo';
		$this->assertArrayHasKey('app', $_GET);
	}

	public function testRun_None() {
		// GIVEN
		$this->assertArrayNotHasKey('app', $_GET);
		$this->tokens->expects( $this->never() )->method('getUserId');
		$this->redirector->expects( $this->never() )->method('redirectTo');
		// THEN
		$this->interceptor->run();
	}

	public function testRun_notokens_exception() {
		// GIVEN
		$_GET['app']  = 'usv2';
		$this->assertArrayHasKey('app', $_GET);
		$this->appConfig->data = array('user_servervars2' => array('sso_url' =>'http://my.sso.url', 'stop_if_empty' => 'true') );
		$this->tokens->expects( $this->once() )->method('getUserId')->willReturn(null);
		// $this->redirector->expects( $this->never() )->method('redirect');
		// THEN
		try {
			$this->interceptor->run();
			$this->fail('exception must be raised');
		} catch(\Exception $e) {
			$this->assertEquals('token error', $e->getMessage());
		}
	}

	public function testRun_tokens_exist() {
		// GIVEN
		$_GET['app']  = 'usv2';
		$this->assertArrayHasKey('app', $_GET);
		$this->appConfig->data = array('user_servervars2' => array('sso_url' =>'http://my.sso.url', 'stop_if_empty' => 'true') );
		$this->tokens->expects( $this->once() )->method('getUserId')->willReturn('user@nowhere.com');
		$this->userAndGroupService ->expects( $this->once() )->method('isLoggedIn')->willReturn(false);

		// Users's creation is requested and successful
		$this->userAndGroupService ->expects( $this->once() )->method('login')->with($this->equalTo('user@nowhere.com'))->willReturn(true);
		// Redirection to defaultpage is called
		$this->redirector->expects( $this->once() )->method('redirectToDefaultPage');
		$this->interceptor->run();
	}

	public function testRun_tokens_exist_but_login_fail() {
		// GIVEN
		$_GET['app']  = 'usv2';
		$this->assertArrayHasKey('app', $_GET);
		$this->appConfig->data = array('user_servervars2' => array('sso_url' =>'http://my.sso.url', 'stop_if_empty' => 'true') );
		$this->tokens->expects( $this->once() )->method('getUserId')->willReturn('user@nowhere.com');
		$this->userAndGroupService ->expects( $this->once() )->method('isLoggedIn')->willReturn(false);

		// Users's creation is requested BUT fails
		$this->userAndGroupService ->expects( $this->once() )->method('login')->with($this->equalTo('user@nowhere.com'))->willReturn(false);

		// Redirection to defaultpage is NEVER called
		$this->redirector->expects( $this->never() )->method('redirectToDefaultPage');
		$this->interceptor->run();
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
