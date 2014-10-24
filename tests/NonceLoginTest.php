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
	static private $request_attributes;

	static public function setupBeforeClass()
	{
		global $core;

		self::$model = $model = $core->models['users.noncelogin'];
		$model->truncate();

		# route

		self::$route = $core->routes['api:nonce-login'];

		# create nonce login ticket

		$ticket = Ticket::from([

			'uid' => 1,
			'ip' => $core->request->ip

		]);

		$ticket->save();

		self::$ticket = $ticket;

		self::$request_attributes = [

			'is_xhr' => true,
			'is_post' => true,
			'uri' => self::$route,
			'request_params' => [

				'token' => $ticket->token,
				'email' => "olivier.laviale@gmail.com",
				'password' => "P4SSW0RD",
				'password-verify' => "P4SSW0RD"

			]
		];
	}

	public function test_empty()
	{
		$request_attributes = self::$request_attributes;

		unset($request_attributes['request_params']);

		$request = Request::from($request_attributes);

		#
		# instance
		#

		$operation = new NonceLoginOperation;

		try
		{
			$response = $operation($request);

			$this->fail("Expected Failure exception.");
		}
		catch (\Exception $e)
		{
			$this->assertInstanceOf('ICanBoogie\Operation\Failure', $e);

			$errors = $e->operation->response->errors;

			$this->assertNotNull($errors['token']);
			$this->assertNotNull($errors['email']);
			$this->assertNotNull($errors['password']);
			$this->assertNotNull($errors['password-verify']);
		}

		#
		# dispatch
		#

		$response = $request();

		$this->assertFalse($response->is_successful);

		$errors = $response->errors;

		$this->assertNotNull($errors['token']);
		$this->assertNotNull($errors['email']);
		$this->assertNotNull($errors['password']);
		$this->assertNotNull($errors['password-verify']);
	}

	public function test_invalid_token()
	{
		$request_attributes = self::$request_attributes;
		$request_attributes['request_params']['token'] = str_rot13(self::$ticket->token);

		$request = Request::from($request_attributes);

		#
		# instance
		#

		$operation = new NonceLoginOperation;

		try
		{
			$response = $operation($request);

			$this->fail("Expected Failure exception.");
		}
		catch (\Exception $e)
		{
			$this->assertInstanceOf('ICanBoogie\Operation\Failure', $e);

			$errors = $e->operation->response->errors;

			$this->assertNotNull($errors['token']);
			$this->assertNull($errors['email']);
			$this->assertNull($errors['password']);
			$this->assertNull($errors['password-verify']);
		}

		#
		# dispatch
		#

		$response = $request();

		$this->assertFalse($response->is_successful);

		$errors = $response->errors;

		$this->assertNotNull($errors['token']);
		$this->assertNull($errors['email']);
		$this->assertNull($errors['password']);
		$this->assertNull($errors['password-verify']);
	}

	public function test_invalid_email()
	{
		$request_attributes = self::$request_attributes;
		$request_attributes['request_params']['email'] = str_rot13($request_attributes['request_params']['email']);

		$request = Request::from($request_attributes);

		#
		# instance
		#

		$operation = new NonceLoginOperation;

		try
		{
			$response = $operation($request);

			$this->fail("Expected Failure exception.");
		}
		catch (\Exception $e)
		{
			$this->assertInstanceOf('ICanBoogie\Operation\Failure', $e);

			$errors = $e->operation->response->errors;

			$this->assertNull($errors['token']);
			$this->assertNotNull($errors['email']);
			$this->assertNull($errors['password']);
			$this->assertNull($errors['password-verify']);
		}

		#
		# dispatch
		#

		$response = $request();

		$this->assertFalse($response->is_successful);

		$errors = $response->errors;

		$this->assertNull($errors['token']);
		$this->assertNotNull($errors['email']);
		$this->assertNull($errors['password']);
		$this->assertNull($errors['password-verify']);
	}

	public function test_invalid_passwords()
	{
		$request_attributes = self::$request_attributes;
		$request_attributes['request_params']['password'] = str_rot13($request_attributes['request_params']['password']);

		$request = Request::from($request_attributes);

		#
		# instance
		#

		$operation = new NonceLoginOperation;

		try
		{
			$response = $operation($request);

			$this->fail("Expected Failure exception.");
		}
		catch (\Exception $e)
		{
			$this->assertInstanceOf('ICanBoogie\Operation\Failure', $e);

			$errors = $e->operation->response->errors;

			$this->assertNull($errors['token']);
			$this->assertNull($errors['email']);
			$this->assertNotNull($errors['password']);
			$this->assertNull($errors['password-verify']);
		}

		#
		# dispatch
		#

		$response = $request();

		$this->assertFalse($response->is_successful);

		$errors = $response->errors;

		$this->assertNull($errors['token']);
		$this->assertNull($errors['email']);
		$this->assertNotNull($errors['password']);
		$this->assertNull($errors['password-verify']);
	}

	public function test_request()
	{
		global $core;

		$request = Request::from(self::$request_attributes);
		$response = $request();

		$this->assertTrue($response->is_successful);
		$this->assertNotEmpty($response['redirect_to']);
		$this->assertNotNull($core->user);
		$this->assertEquals(1, $core->user_id);

		// the ticket for the user must be destroyed.
		$ticket = self::$ticket;
		$ticket = $core->models['users.noncelogin']->filter_by_uid($ticket->uid)->one;
		$this->assertEmpty($ticket);
	}

	public function test_expired()
	{
		$ticket = self::$ticket;
		$ticket->expire_at = '-10 days';
		$ticket->save();

		$request = Request::from(self::$request_attributes);

		#
		# instance
		#

		$operation = new NonceLoginOperation;

		try
		{
			$response = $operation($request);

			$this->fail("Expected Failure exception.");
		}
		catch (\Exception $e)
		{
			$this->assertInstanceOf('ICanBoogie\Operation\Failure', $e);

			$errors = $e->operation->response->errors;

			$this->assertNotNull($errors['token']);
			$this->assertNull($errors['email']);
			$this->assertNull($errors['password']);
			$this->assertNull($errors['password-verify']);
		}

		#
		# dispatch
		#

		$response = $request();

		$this->assertFalse($response->is_successful);

		$errors = $response->errors;

		$this->assertNotNull($errors['token']);
		$this->assertNull($errors['email']);
		$this->assertNull($errors['password']);
		$this->assertNull($errors['password-verify']);
	}
}
