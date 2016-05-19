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
namespace OCA\User_Servervars2\Service;

class ProxyUserAndGroupServiceTest extends \Test\TestCase {

	var $service;
	var $userManager;
	var $groupManager;
	var $groupNamingService;
	var $backend;
	var $config;
	var $tokens;
	var $user;


	protected function setUp(){

		parent::setUp();

		$this->userManager = 	$this->getMockBuilder('\OC\User\Manager')
									->disableOriginalConstructor()
									->getMock();


		$this->groupManager = 	$this->getMockBuilder('\OC\Group\Manager')
									->disableOriginalConstructor()
									->getMock();


		$this->groupNamingService = new \OCA\User_Servervars2\Service\Impl\PrependGroupNamingService(
			new  \OCA\User_Servervars2\Lib\CustomConfig(
				array(
					'separator' => '@',
					'mapping' => array(
						'ou'=> array(
							'kind' => 'grp',
							'defaultValue' => 'defVal',
						),
						'o' => array(
							'kind' => 'org',
							'defaultValue' => 'defVal2',
						),
					),
				)
			)
		);


		$this->backend = $this->getMockBuilder('OCA\User_Servervars2\Backend\UserBackend')
								->disableOriginalConstructor()
								->getMock();

		$this->config = new LocalUserConfig();

		$this->tokens = $this->getMockBuilder('OCA\User_Servervars2\Service\Tokens')
									->disableOriginalConstructor()
									->getMock();

		$this->user = $this->getMockBuilder('\OC\User\User')
								->disableOriginalConstructor()
								->getMock();


		$this->service = new ProxyUserAndGroupService($this->userManager, $this->groupManager, $this->groupNamingService, $this->backend, $this->config);

	}



	public function testNewRandomPassword() {
		$a = array();
		for ($i=0; $i < 10 ; $i++) {
			$a[] = $this->service->newRandomPassword();
		}

		// - length of password is 20
		foreach ($a as $value) {
			$this->assertEquals( 20, strlen($value), strlen($value)." len for <$value>");
		}
		// all passwords are unique
		$this->assertEquals( 10, count(array_unique($a)));
	}


	public function testCreateUser() {

		// $this->userManager->expects( $this->once() )->method('delete')->with('jdoo'); // does not exists in OC9
		$this->userManager->expects( $this->once() )->method('userExists')->with('jdoo')->willReturn(false);
		$this->userManager->expects( $this->once() )->method('createUser')->with( 'jdoo', $this->logicalNot( $this->isNull()))->willReturn( $this->user );

		//THEN
		$this-> assertEquals($this->user, $this->service->createUser('jdoo'));

	}


	public function testProvisionUser() {
		// $this->userManager->expects( $this->once() )->method('delete')->with('jdoo'); // does not exists in OC9
		$this->userManager->expects( $this->once() )->method('userExists')->with('jdoo')->willReturn(false);
		$this->userManager->expects( $this->once() )->method('createUser')->with( 'jdoo', $this->logicalNot( $this->isNull()))->willReturn( $this->user );
		$this->userManager->expects( $this->once() )->method('get')->with( 'jdoo')->willReturn( $this->user );

		$this->backend->expects( $this->any() )->method('isAutoCreateUser')->willReturn(true);
		$this->backend->expects( $this->any() )->method('isUpdateUserData')->willReturn(true);
		$this->backend->expects( $this->any() )->method('isUpdateGroups')->willReturn(false);

		$this->tokens->expects( $this->once() )->method('getDisplayName')->willReturn( 'John DOO' );
		$this->tokens->expects( $this->once() )->method('getEmail')->willReturn( 'jdoo@nowhere.org' );
		$this->user->expects( $this->any() )->method('getUID')->willReturn( 'jdoo');

		// expectation about displayName
		$this->user->expects( $this->once() )->method('setDisplayName')->with( 'John DOO');


		//THEN
		$this->service->provisionUser('jdoo', $this->tokens);

		// check mail
		$this->assertEquals('jdoo@nowhere.org', $this->config->receveidData['jdoo']['settings']['email']);
	}

	function __backup() {
				$this->backend->expects( $this->any() )->method('getDefaultGroups')->willReturn( array());

		$this->tokens->expects( $this->once() )->method('getDisplayName')->willReturn( 'John DOO' );
		$this->tokens->expects( $this->once() )->method('getEmail')->willReturn( 'jdoo@nowhere.org' );
		$this->tokens->expects( $this->once() )->method('getGroupsArray')->willReturn( array('ou' => array('tokyo'), 'o' => array('nowhere') ));
		$this->user->expects( $this->any() )->method('getUID')->willReturn( 'jdoo');

		// expectation about displayName
		$this->user->expects( $this->once() )->method('setDisplayName')->with( 'John DOO');

		// expectation about group
		$this->groupManager->expects( $this->once() )->method('getUserGroupIds')->with('jdoo')->willReturn( array('org@nowhere', 'org@former_group') );


		$grpTokyo = $this->getMockBuilder('OC\Group\Group')
								->disableOriginalConstructor()
								->getMock();

		$grpFormer = $this->getMockBuilder('OC\Group\Group')
								->disableOriginalConstructor()
								->getMock();

		$grpNowhere = $this->getMockBuilder('OC\Group\Group')
								->disableOriginalConstructor()
								->getMock();

		$map = array('grp@tokyo' => $grpTokyo, 'org@former_group' => $grpFormer, 'org@nowhere' => $grpNowhere);
		$this->groupManager->expects( $this->once() )->method('get')->will($this->returnValueMap($map));

		$grpTokyo->expects( $this->once() )->method('addUser')->with( $this->user );
		$grpFormer->expects( $this->once() )->method('removeUser')->with( $this->user );


		//THEN
		$this->service->provisionUser('jdoo', $this->tokens);

		$this->assertEquals('jdoo@nowhere.org', $this->config->receveidData['jdoo']['settings']['email']);

	}


	function testAddToGroup_With_Creation() {
		$grpTokyo = $this->getMockBuilder('OC\Group\Group')
								->disableOriginalConstructor()
								->getMock();

		// group doesn't exist
		$this->groupManager->expects( $this->at(0) )->method('get')->with('grp@tokyo')->willReturn( null );
		// group is created
		$this->groupManager->expects( $this->at(1) )->method('createGroup')->with('grp@tokyo')->willReturn( $grpTokyo );

		// member is added
		$grpTokyo->expects( $this->once() )->method('addUser')->with( $this->user );

		$this->service->addToGroup($this->user, array('grp@tokyo'));
	}



	/**
	*
	*/
	function testAddToGroup_without_Creation() {
		$grpTokyo = $this->getMockBuilder('OC\Group\Group')
								->disableOriginalConstructor()
								->getMock();

		// group doesn't exist
		$this->groupManager->expects( $this->once() )->method('get')->with('grp@tokyo')->willReturn( $grpTokyo );
		// group is created
		$this->groupManager->expects( $this->never() )->method('createGroup')->with('grp@tokyo')->willReturn( $grpTokyo );

		// member is added
		$grpTokyo->expects( $this->once() )->method('addUser')->with( $this->user );

		$this->service->addToGroup($this->user, array('grp@tokyo'));
	}



	/**
	*
	*/
	public function testGetGroupNames() {
		$grpArray = array(
			'ou' => array('tokyo','kyoto'),
			'o' => array('japan'),
			'foo' => array('bar')
			);
		$names = $this->service->getGroupNames($grpArray, $this->groupNamingService);
		$this->assertEquals( array('grp@tokyo', 'grp@kyoto', 'org@japan'), $names);
	}

	/**
	*
	*/
	public function testGetOldGroupNames() {
		$grpArray = array('grp@tokyo', 'grp@kyoto', 'org@japan', 'foo_bar', 'something', 'admin');
		$names = $this->service->getOldGroupNames($grpArray, $this->groupNamingService);
		$this->assertEquals( array('grp@tokyo', 'grp@kyoto', 'org@japan'), $names);
	}


	public function testgetGroupNamesToAdd() {
		$names = array('toAdd1', 'same1', 'same2', 'toAdd2');
		$oldNames = array('toRemove1', 'same1', 'same2');
		$result = $this->service->getGroupNamesToAdd($names, $oldNames);

		$this->assertTrue(in_array('toAdd1', $result) );
		$this->assertTrue(in_array('toAdd2', $result) );
		$this->assertEquals(2, count($result) );
	}

	public function testgetGroupNamesToRemove() {
		$names = array('toAdd1', 'same1', 'same2', 'toAdd2');
		$oldNames = array('toRemove1', 'same1', 'same2', 'toRemove2');
		$result = $this->service->getGroupNamesToRemove($names, $oldNames);

		$this->assertTrue(in_array('toRemove1', $result), print_r($result, 1) );
		$this->assertTrue(in_array('toRemove2', $result) );
		$this->assertEquals(2, count($result) );
	}


}

class LocalUserConfig {
 	var $data = array();
 	var $receveidData = array();
 	var $callCount = 0;

 	function getUserValue($uid, $appName, $key, $default=null) {
 		$this->callCount++;
 		if ( isset($this->data[$uid]) && isset($this->data[$uid][$appName]) && isset($this->data[$uid][$appName][$key])) {
 			return $this->data[$appName][$key];
 		}
 		return $default;
 	}

 	function setUserValue($uid, $appName, $key, $value) {
 		$this->callCount++;
 		if ( ! isset($this->data[$uid]) ) {
 			$this->data[$uid] = array();
 			$this->receveidData[$uid] = array();
 		}

 		if ( ! isset($this->data[$uid][$appName]) ) {
 			$this->data[$uid][$appName] = array();
 			$this->receveidData[$uid][$appName] = array();
 		}

		$this->data[$uid][$appName][$key] = $value;
		$this->receveidData[$uid][$appName][$key] = $value;
 	}
 }
