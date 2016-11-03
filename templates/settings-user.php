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
\OCP\Util::addScript('core', 'lostpassword');
\OCP\Util::addStyle('user_servervars2', 'user');
?>
<div class="section">
	<h2><?php p($l->t('Useful Informations For Connection With Login / local My CoRe password'));?></h2>
	<a name="user_servervars2"></a>
	<div>
		<label><?php p($l->t('User ID'));?>:</label><span class="user_id"><?php p($_['uid']);?></span>
		<input id="user" type="hidden" value="<?php p($_['uid']);?>">
		<p><em><?php p($l->t('This user ID will be requested to connect with Login / local My CoRe password'));?></em></p>
		<p><em><?php p($l->t("It can't be modified"));?></em></p>
	</div>
	<div>
		<label><?php p($l->t('Local My CoRe password')); ?>:</label>
		<button id="lost-password">
			<?php p($l->t('Send Email To Reset My Password')); ?>
		</button>

		<p><em><?php p($l->t("If you don't know your local My CoRe password, ask for an email to reset password"));?></em></p>
	</div>
</div>
