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
use \OC_User;

class ProxyUserAndGroupService implements UserAndGroupService {

	var $userManager;
	var $groupManager;
	var $config;
	var $backend;
	var $groupNamingService;
	var $emitter;

	function __construct( $userManager, $groupManager, $groupNamingService, $backend, $config, $emitter=null ) {
		$this->userManager = $userManager;
		$this->backend = $backend;
		$this->config = $config;
		$this->groupNamingService = $groupNamingService;
		$this->groupManager = $groupManager;
		if ( is_null($emitter)) {
			$emitter = UserAndGroupsEmitter::getInstance();
		}
		$this->emitter = $emitter;
	}

	/**
	 *
	 * @return created user or false
	 */
	public function createUser($uid) {
		// $this->userManager->delete($uid);
		if (  ! $this->userManager->userExists($uid)) {
			$randomPaswword = $this->newRandomPassword();
			//return OC_User::createUser($uid, $randomPaswword);
			return $this->userManager->createUser($uid, $randomPaswword);
		}
	}


	public function provisionUser($uid, $tokens) {
			if ( $this->isAutoCreateUser() ) {
				$justCreatedUser = $this->createUser($uid);
			}

			if ( $justCreatedUser || $this->isUpdateUserData() ) {

				$user = $this->userManager->get($uid);
				$this->updateDisplayName( 	$user, 	$tokens->getDisplayName() );
				$this->updateMail( 			$uid ,  $tokens->getEmail() );

				if ( $this->isUpdateGroups()) {
					$this->updateGroup( 		$user, 	$tokens->getGroupsArray() );
				}
			}
	}


	public function isAutoCreateUser() {
		return $this->backend->isAutoCreateUser();
	}

	public function isUpdateUserData() {
		return $this->backend->isUpdateUserData();
	}

	public function isUpdateGroups() {
		return $this->backend->isUpdateGroups();
	}


	public function updateDisplayName($user, $name) {
		$oldDisplayName =  $user->getDisplayName() ;
		if ( $name !== $oldDisplayName ) {
			$user->setDisplayName($name);
		}
		$this->emitter->emitPostChangeDisplayName($user->getUID(), $name, $oldDisplayName);
	}

	// /**
	//  * Cf. 	OC_User_Backend::OC_USER_BACKEND_GET_DISPLAYNAME => 'getDisplayName',
	//  *	    OC_User_Backend::OC_USER_BACKEND_SET_DISPLAYNAME => 'setDisplayName',
	//  */
	// public function updateDisplayName($uid, $displayName) {
	// 			// Update if not the same
	// 	if ( $displayName !== OC_User::getDisplayName($uid) )  {
	// 		OC_User::setDisplayName( $uid, $displayName );
	// 	}
	// }

		/**
	 * Email is set in preferences (WTF ?)
	 */
	public function updateMail($uid, $email) {
		$existingEmail = $this->config->getUserValue($uid, 'settings', 'email');
		if ( $email !== $existingEmail ) {
			$this->config->setUserValue($uid, 'settings', 'email', $email);
		}
		$this->emitter->emitPostChangeMail($uid, $email, $existingEmail);
	}

	/**
	*
	* @param String uid
	* @param Array attr,names array
	*/
	public function updateGroup($user, $groupsArray) {

		$defaultGroups 	= $this->backend->getDefaultGroups();
		$uid = $user->getUID();

		if (empty($groupsArray) && !empty($defaultGroups)) {
            $groupsArray = $defaultGroups;
        }

        $naming = $this->groupNamingService;

        $groupNames = $this->getGroupNames($groupsArray, $naming);


		$rawOldGroupIds = $this->groupManager->getUserGroupIds( $user );
        $oldGroupNames = $this->getOldGroupNames($rawOldGroupIds, $naming);


    	$toAddGrps = $this->getGroupNamesToAdd($groupNames, $oldGroupNames);
    	$toRemGrps = $this->getGroupNamesToRemove($groupNames, $oldGroupNames);

    	$this->addToGroup($user, $toAddGrps);
    	$this->removeFromGroup($user, $toRemGrps);

	}


	function getGroupNamesToAdd($groupNames, $oldGroupNames) {
    	$toAddGrps = array();
   		$toAddGrps = array_diff($groupNames, $oldGroupNames);
   		return $toAddGrps;
	}

	function getGroupNamesToRemove($groupNames, $oldGroupNames) {
    	$toAddGrps = array();
   		$toAddGrps = array_diff($oldGroupNames, $groupNames);
   		return $toAddGrps;
	}


	function getGroupNames($groupsArray, $naming) {
		$groupNames = array();
		if ( is_array($groupsArray)) {
			foreach ($groupsArray as $kind => $array) {
	        	if ( $naming->isManaged($kind) ) {
	        		foreach ($array as $value) {
	        			$groupNames[] = $naming->getName($kind,$value);
	        		}
        	   	}
        	}
        }
        return $groupNames;
	}

	function getOldGroupNames($rawOldGroupIds, $naming) {
        $oldGroupNames = array();
        foreach ($rawOldGroupIds as $value) {
        	if ( $naming->isValid($value)) {
        		$oldGroupNames[] = $value;
        	}
        }
        return $oldGroupNames;
	}

	/**
	* @param OC\User\User user
	* @param Array group names
	*/
	public function addToGroup($user, $gIds) {
		foreach ($gIds as $groupId) {
			$group = $this->groupManager->get($groupId);
			if ( $group === null) {
				$group = $this->groupManager->createGroup($groupId);
			}
			if ( $group ) {
				$group->addUser($user);
			} else {
				throw new \Exception("Creation of '$groupId' has failed", 1);
			}
		}
	}

	public function removeFromGroup($uid, $gIds) {
		foreach ($gIds as $groupId) {
			$group = $this->groupManager->get($groupId);
			if ( $group != null) {
				$group->removeUser($uid);
			}

		}
	}

	public function groupExists($group) {
		return OC_Group::groupExists($group);
	}

	public function checkUserPassword($uid) {
		$this->backend->startLoginCycle($uid);
		return $this->backend->checkPassword($uid, '');
	}

	public function login($uid) {
		$userSession = \OC::$server->getUserSession();
		$userSession->login($uid, '');
		$userSession->createSessionToken(\OC::$server->getRequest(), $userSession->getUser()->getUID(), $uid, $password);
		$this->backend->endLoginCycle($uid);
		return $response;
	}


	public function isLoggedIn() {
		return \OCP\User::isLoggedIn();
	}


		/**
	 *
	 * @return String a random password
	 */
	function newRandomPassword() {
		$valid_chars
		= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789&#!@%*?';
		$length = 20;
		$ind_max_valid_chars = strlen($valid_chars) - 1;

		$random_string = '';
		for ($i = 0; $i < $length; $i++) {
			$random_pick = mt_rand(0, $ind_max_valid_chars);
			$random_char = $valid_chars[$random_pick];
			$random_string .= $random_char;
		}

		return $random_string;
	}

}
