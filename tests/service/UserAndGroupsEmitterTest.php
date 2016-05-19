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

namespace OCA\User_ServerVars2\Service;

class UserAndGroupsEmitterTest extends \Test\TestCase {

	var $emitter;
	var $listener;

	protected function setUp(){

 		parent::setUp();

		// $this->tokens = $this->getMock('OCA\User_Servervars2\Service\Tokens');
 		// $this->metadataProvider = $this->getMock('OCA\User_Servervars2\Backend\MetadataProvider');
 		// $this->metadata = $this->getMock('OCA\User_Servervars2\Backend\Metadata');
 		// $this->scopeValidator = $this->getMock('OCA\User_Servervars2\Backend\scopeValidator');
	 	$this->emitter = new UserAndGroupsEmitter();
 	}


 	public function testListen() {
 		$this->listener = new LocalListener();
 		$obj = $this->listener;
 		$this->emitter->listen('OCA/UserChange/', 'postChangeMail', function($uid, $newMail, $oldMail) use( &$obj){
 			$obj->onPostChangeMail($uid, $newMail, $oldMail);
 		} );

 		$this->emitter->emitPostChangeMail('foo@bar', 'newMail', 'oldMail');

 		$this->assertEquals(1, count($this->listener->log));
 		$this->assertEquals('foo@bar', $this->listener->log[1]['uid']);
 		$this->assertEquals('newMail', $this->listener->log[1]['newMail']);
 		$this->assertEquals('oldMail', $this->listener->log[1]['oldMail']);
 	}
}

class LocalListener {

	var $log = array();
	var $counter = 0;

	public function onPostChangeMail($uid, $newMail, $oldMail) {
		$this->counter++;
		$this->log[$this->counter] = array();
		$this->log[$this->counter]['uid'] = $uid;
		$this->log[$this->counter]['newMail'] = $newMail;
		$this->log[$this->counter]['oldMail'] = $oldMail;
	}


}
