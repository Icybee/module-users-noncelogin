<?php

/*
 * This file is part of the ICanBoogie package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie;

use Icybee\Modules\Users\User;

chdir(__DIR__);

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

$app = boot();
//$app->session = new FakeSession;
$app->modules->install();

#
# Create a user account
#

$user = User::from([

	User::EMAIL => 'olivier.laviale@gmail.com',
	User::USERNAME => 'olvlvl',
	User::TIMEZONE => 'Europe/Paris'

]);

$user->save();

#
# Fake mailer's mail().
#

global $mailer_options;

$app->prototype['mail'] = function(Application $app, array $options) use(&$mailer_options) {

	$mailer_options = $options;

};
