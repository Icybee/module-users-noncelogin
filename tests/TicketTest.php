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

use ICanBoogie\DateTime;

class RecordTest extends \PHPUnit_Framework_TestCase
{
	static private $model;

	static public function setupBeforeClass()
	{
		global $core;

		self::$model = $core->models['users.noncelogin'];
		self::$model->truncate();
	}

	public function test_expire_at()
	{
		$t = new Ticket;
		$d = $t->expire_at;
		$this->assertInstanceOf('ICanBoogie\DateTime', $d);
		$this->assertTrue($d->is_empty);
		$this->assertEquals('UTC', $d->zone->name);
		$this->assertEquals('0000-00-00 00:00:00', $d->as_db);

		$t->expire_at = '2013-03-07 18:30:45';
		$d = $t->expire_at;
		$this->assertInstanceOf('ICanBoogie\DateTime', $d);
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
	}

	public function test_update()
	{
		$t = Ticket::from(array(

			'token' => self::$model->generate_token(),
			'uid' => 1,
			'expire_at' => '+1 hour',
			'ip' => '::1'

		));

		$rc = $t->save();

		$this->assertEquals(1, self::$model->count);

		$t->ip = '192.168.0.1';
		$rc = $t->save();

		$this->assertEquals(1, self::$model->count);

		$record = self::$model->one;
		$this->assertEquals($t->ip, $record->ip);
	}

	public function test_belong_to_user()
	{
		$t = Ticket::from(array(

			'token' => self::$model->generate_token(),
			'uid' => 1,
			'expire_at' => '+1 hour',
			'ip' => '::1'

		));

		$user = $t->user;
		$this->assertInstanceOf('Icybee\Modules\Users\User', $user);
		$this->assertEquals('olivier.laviale@gmail.com', $user->email);
	}
}