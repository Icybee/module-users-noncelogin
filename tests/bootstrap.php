<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Users\NonceLogin;

use ICanBoogie\Core;

use Icybee\Modules\Users\User;

$_SERVER['DOCUMENT_ROOT'] = __DIR__;
$_SERVER['HTTP_HOST'] = 'icanboogie.org';

require __DIR__ . '/../vendor/autoload.php';

/**
 * An instance of this class is used to fake the Session class that is used to handle sessions.
 * Instead of starting a session the class just does nothing.
 */
class FakeSession extends \ICanBoogie\Session
{
	public function __construct()
	{
	}

	public function regenerate_id($delete_old_session=false)
	{
	}
}

/* @var $app Core|\ICanBoogie\Module\CoreBindings|\ICanBoogie\Binding\Mailer\CoreBindings */

$app = new Core(\ICanBoogie\array_merge_recursive(\ICanBoogie\get_autoconfig(), [

	'config-path' => [ __DIR__ . DIRECTORY_SEPARATOR . 'config' => 10 ],
	'module-path' => [ dirname(__DIR__) ]

]));

$app->session = new FakeSession;
$app->boot();
$app->modules->install();

#
# Create a user account
#

$user = User::from([

	'email' => 'olivier.laviale@gmail.com',
	'username' => 'olvlvl'

]);

$user->save();

#
# Fake mailer's mail().
#

global $mailer_options;

$app->mail = function($options) use(&$mailer_options) {

	$mailer_options = $options;

};
