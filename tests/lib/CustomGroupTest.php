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
namespace OCA\User_Servervars2\Lib;
// use OCA\User_Servervars2\Lib\CustomConfig;

class CustomConfigTest extends \Test\TestCase {

	var $conf;

	public function setUp() {
		parent::setUp();

		$this->conf = new CustomConfig();
	}


	public function testLoad() {
		//__given__
		$txt = '{"list": {"sublist": [ "item1", "item2", "item3"] }, "map": { "key0": "value0"}}';
		//__when__
		$ret = $this->conf->loadFromJSON($txt);
		//__then__
		$this->assertEquals(JSON_ERROR_NONE, $ret);
		$this->assertFalse(is_null($this->conf->data), json_last_error_msg());
	}

	public function testSet() {
		//__given__
		$txt = '{"list": {"sublist": [ "item1", "item2", "item3"] }, "map": { "key0": "value0"}}';
		$ret = $this->conf->loadFromJSON($txt);
		//__when__
		$this->conf->setValue('list.sublist.0', 'new');
		$this->conf->setValue('list.map.key0', 'new');
		//__then__
		$this->assertEquals('new', $this->conf->data['list']['sublist']['0']);
		$this->assertEquals('new', $this->conf->getValue('list.sublist.0'), print_r($this->conf->getValue('list.sublist.0'), true));
		$this->assertEquals('new', $this->conf->data['list']['map']['key0']);
	}
}
