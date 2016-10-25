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
// VERSION AVEC JQUERY
\OCP\Util::addScript('user_servervars2', 'usv2');
\OCP\Util::addStyle('user_servervars2', 'servervars');
?>
<div id="usv2settings" class="section">
	<h2><?php p($l->t('Authentication and identification according server\'s variables'));?></h2>
	<pre class="usv2error" id="usv2settingsError"></pre>
	<form name="usv2Form" id="usv2FormID">
	<div id="usv2tabs">
		<ul>
			<li><a href="#usv2tabs-1"><?php p($l->t("On Users's Connection"));?></a></li>
			<li><a href="#usv2tabs-2"><?php p($l->t("URL"));?></a></li>
			<li><a href="#usv2tabs-3"><?php p($l->t("Tokens"));?></a></li>
			<li><a href="#usv2tabs-4"><?php p($l->t("GroupNaming"));?></a></li>
		</ul>
		<fieldset id="usv2tabs-1">
			<div class="block"><label><?php p($l->t('Auto Create User'))?></label><input type="checkbox" name="auto_create_user" value="1" <?php if (isset($_['auto_create_user']) and $_['auto_create_user']!=='false') { print_unescaped('checked="checked"'); }?> ></div>
			<div class="block"><label><?php p($l->t('Update User Data'))?></label><input type="checkbox" name="update_user_data" value="1" <?php if(isset($_['update_user_data']) and $_['update_user_data']!=='false') { print_unescaped('checked="checked"'); }?> ></div>
			<div class="block"><label><?php p($l->t('Update User Groups'))?></label><input type="checkbox" name="update_groups" value="1" <?php if(isset($_['update_groups']) and $_['update_groups']!=='false') { print_unescaped('checked="checked"'); }?> ></div>
			<div class="block"><label><?php p($l->t('Stop If Empty'))?></label><input type="checkbox" name="stop_if_empty" value="1" <?php if(isset($_['stop_if_empty']) and $_['stop_if_empty']!=='false') { print_unescaped('checked="checked"'); }?> ></div>
		</fieldset>
		<fieldset id="usv2tabs-2">
			<div class="block"><label><?php p($l->t('Single Sign On Url'))?></label><input type="text" name="sso_url" value="<?php p($_['sso_url'])?>"></div>
			<div class="block"><label><?php p($l->t('Single Log Out Url'))?></label><input type="text" name="slo_url" value="<?php p($_['slo_url'])?>" ></div>
			<div class="block"><label><?php p($l->t('Login Button Label'))?></label><input type="text" name="button_name" value="<?php p($_['button_name'])?>" ></div>
		</fieldset>
		<fieldset id="usv2tabs-3">
			<div class="block">
				<label><?php p($l->t('Tokens Class'))?></label>
				<input type="text" name="tokens_class" value="<?php p($_['tokens_class'])?>" >
			</div>
			<div class="block"><label><?php p($l->t('Tokens Conf'))?></label>
				<input type="text" name="tokens_conf" value="<?php p($_['tokens_conf'])?>" >
				<pre id="show_tokens_conf"><?php if (!empty($_['tokens_conf_data'])) { p($_['tokens_conf_data']); } ?></pre>
			</div>
		</fieldset>
		<fieldset id="usv2tabs-4">
			<div class="block"><label><?php p($l->t('GroupNaming Class'))?></label><input type="text" name="group_naming_class" value="<?php p($_['group_naming_class'])?>" ></div>
			<div class="block">
				<label><?php p($l->t('GroupNaming Conf'))?></label>
				<input type="text" name="group_naming_conf" value="<?php p($_['group_naming_conf'])?>" >
				<pre id="show_group_naming_conf"><?php p($_['group_naming_conf_data'])?></pre>
			</div>
		</fieldset>
		</div>
	</form>
	<div id="usv2settingsEcho" class="section"></div>
</div>
