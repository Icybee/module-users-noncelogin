<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Users\NonceLogin;

use function ICanBoogie\app;
use ICanBoogie\DateTime;
use Icybee\Modules\Users\User;

class RecordTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var TicketModel
	 */
	static private $model;

	static public function setupBeforeClass()
	{
		self::$model = app()->models['users.noncelogin'];
		self::$model->truncate();
	}

	public function test_expire_at()
	{
		$t = new Ticket;
		$d = $t->expire_at;
		$this->assertInstanceOf(DateTime::class, $d);
		$this->assertTrue($d->is_empty);
		$this->assertEquals('UTC', $d->zone->name);
		$this->assertEquals('0000-00-00 00:00:00', $d->as_db);

		$t->expire_at = '2013-03-07 18:30:45';
		/* @var $d DateTime */
		$d = $t->expire_at;
		$this->assertInstanceOf(DateTime::class, $d);
		$this->assertFalse($d->is_empty);
		$this->assertEquals('UTC', $d->zone->name);
		$this->assertEquals('2013-03-07 18:30:45', $d->as_db);

		$t->expire_at = new DateTime('2013-03-07 18:30:45', 'utc');
		$d = $t->expire_at;
		$this->assertInstanceOf('ICanBoogie\DateTime', $d);
		$this->assertFalse($d->is_empty);
		$this->assertEquals('UTC', $d->zone->name);
		$this->assertEquals('2013-03-07 18:30:45', $d->as_db);

		$t->expire_at = null;
		$this->assertInstanceOf('ICanBoogie\DateTime', $d);

		$t->expire_at = DateTime::now();
		$properties = $t->__sleep();
		$this->assertArrayHasKey('expire_at', $properties);
		$array = $t->to_array();
		$this->assertArrayHasKey('expire_at', $array);

		# automatic expire_at

		$t = Ticket::from([

			'uid' => 1,
			'ip' => '::1'

		]);

		$t->save();

		$this->assertGreaterThanOrEqual(Ticket::FRESH_PERIOD, $t->expire_at->timestamp - DateTime::now()->timestamp);

		$t->delete();
	}

	public function test_token()
	{
		$ticket = Ticket::from([

			'uid' => 1,
			'ip' => '::1'

		]);

		$this->assertEmpty($ticket->token);
		$ticket->save();
		$this->assertNotEmpty($ticket->token);
		$ticket->delete();
	}

	public function test_update()
	{
		$t = Ticket::from([

			'token' => self::$model->generate_token(),
			'uid' => 1,
			'expire_at' => '+1 hour',
			'ip' => '::1'

		]);

		$this->assertNotEmpty($t->save());

		$this->assertEquals(1, self::$model->count);

		$t->ip = '192.168.0.1';
		$this->assertNotEmpty($t->save());

		$this->assertEquals(1, self::$model->count);

		/* @var $record Ticket */
		$record = self::$model->one;
		$this->assertEquals($t->ip, $record->ip);

		$t->delete();
	}

	public function test_belong_to_user()
	{
		$t = Ticket::from([

			'token' => self::$model->generate_token(),
			'uid' => 1,
			'expire_at' => '+1 hour',
			'ip' => '::1'

		]);

		$user = $t->user;
		$this->assertInstanceOf(User::class, $user);
		$this->assertEquals('olivier.laviale@gmail.com', $user->email);
	}
}
