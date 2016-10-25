<?php
/**
 * ownCloud - app.php
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

$app = new ConfigApp();
$c = $app->getContainer();
$appName = 'user_servervars2';
if ( ! \OCP\App::isEnabled( $appName) ) {
	return;
}



//To put a template into admin menu
\OCP\App::registerAdmin('user_servervars2', 'settings/admin');
\OCP\App::registerPersonal('user_servervars2', 'settings/user');
$login = array(
	'href'  => $app->getAppConfig()->getValue('user_servervars2','sso_url'),
	'name'  => $app->getAppConfig()->getValue('user_servervars2','button_name','Use Your ID Provider')
);
\OC_App::registerLogIn($login);

$app->getUserManager()->registerBackend( $c->query('UserBackend'));
//$app->getGroupManager()->addBackend( new \OC_Group_Database() );

$c->query('ServerVarsHooks')->register( $app->getUserSession());
// $authStatus = $c->isLoggedIn();

// - trigger authentication - 
// http://localhost/core/index.php?XDEBUG_SESSION_START=sublime.xdebug&app=usv2&debug=1

//-- TRIGGERS --
$interceptor = $c->query('Interceptor');
$interceptor->run();

