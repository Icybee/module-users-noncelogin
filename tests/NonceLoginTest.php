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

use ICanBoogie\HTTP\Request;

class NonceLoginTest extends \PHPUnit_Framework_TestCase
{
	static private $model;
	static private $route;
	static private $ticket;

	static public function setupBeforeClass()
	{
		global $core;

		self::$model = $model = $core->models['users.noncelogin'];
		$model->truncate();

		# route

		self::$route = $core->routes['api:nonce-login'];

		# create nonce login ticket

		$ticket = new Ticket;
		$ticket->token = $model->generate_token();
		$ticket->uid = 1;
		$ticket->ip = $core->request->ip;
		$ticket->expire_at = '+1 hour';
		$ticket->save();

		self::$ticket = $ticket;
	}

	public function test_invalid_token()
	{
		$ticket = clone self::$ticket;
		$ticket->token = str_rot13($ticket->token);

		$request = Request::from(self::$route->format($ticket));

		try
		{
			$response = $request();

			$this->fail('The Failure exception should have been thrown.');
		}
		catch (\ICanBoogie\Operation\Failure $e)
		{
			$response = $e->operation->response;

			$this->assertFalse($response->is_successful);
			$this->assertNotNull($response->errors['token']);
		}
	}

	public function test_invalid_uid()
	{
		global $core;

		$ticket = new Ticket;
		$ticket->token = self::$model->generate_token();
		$ticket->uid = 999;
		$ticket->ip = $core->request->ip;
		$ticket->expire_at = '+1 hour';
		$ticket->save();

		try
		{
			$request = Request::from(self::$route->format($ticket));
			$response = $request();

			$this->fail('The Failure exception should have been thrown.');
		}
		catch (\ICanBoogie\Operation\Failure $e)
		{
			$response = $e->operation->response;

			$this->assertFalse($response->is_successful);
			$this->assertNotNull($response->errors['uid']);
		}
	}

	public function test_expired()
	{
		$ticket = self::$ticket;
		$ticket->expire_at = '-10 days';
		$ticket->save();

		try
		{
			$request = Request::from(self::$route->format($ticket));
			$response = $request();

			$this->fail('The Failure exception should have been thrown.');
		}
		catch (\ICanBoogie\Operation\Failure $e)
		{
			$response = $e->operation->response;

			$this->assertFalse($response->is_successful);
			$this->assertNotNull($response->errors['expire_at']);
		}
	}

	public function test_request()
	{
		global $core;

		$ticket = self::$ticket;
		$ticket->expire_at = '+1 hour';
		$ticket->save();

		$request = Request::from(self::$route->format(self::$ticket));
		$response = $request();

		$this->assertTrue($response->is_successful);
		$this->assertEquals('/admin/profile', $response->location);
		$this->assertNotNull($core->user);
		$this->assertEquals(1, $core->user_id);

		// the ticket for the user must be destroyed.
		$ticket = $core->models['users.noncelogin']->filter_by_uid($ticket->uid)->one;
		$this->assertFalse($ticket);
	}
}