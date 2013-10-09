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

use Icybee\Modules\Users\User;

global $core;

$vendor_dir = realpath(__DIR__ . '/../vendor');

require $vendor_dir . '/autoload.php';

/**
 * An instance of this class is used to fake the Session class that is used to handle sessions.
 * Instead of starting a session the class just does nothing.
 */
class FakeSession extends \ICanBoogie\Session
{
	public function __construct()
	{
	}

	public function regenerate_id()
	{
	}

	public function regenerate_token()
	{
	}
}

$core = new \ICanBoogie\Core(array(

	'config paths' => array
	(
		__DIR__
	),

	'modules paths' => array
	(
		$vendor_dir . '/icanboogie-modules',
		realpath(__DIR__ . '/../')
	),

	'connections' => array
	(
		'primary' => array
		(
			'dsn' => 'sqlite::memory:'
		)
	)
));

$core->site = (object) array
(
	'url' => 'http://testing.localhost',
	'title' => 'testing',
	'path' => ''
);

$core->session = new FakeSession;

$core();

$core->models['users']->install();
$core->models['users.noncelogin']->install();

$user = User::from(array(

	'email' => 'olivier.laviale@gmail.com',
	'username' => 'olvlvl'

));

$user->save();