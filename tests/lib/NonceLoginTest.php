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
use ICanBoogie\HTTP\Request;
use ICanBoogie\Operation\Failure;
use Icybee\Modules\Users\NonceLogin\Operation\NonceLoginOperation;

class NonceLoginTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var TicketModel
	 */
	static private $model;
	static private $route;

	/**
	 * @var Ticket
	 */
	static private $ticket;
	static private $request_attributes;

	static public function setupBeforeClass()
	{
		$app = \ICanBoogie\app();

		/* @var $model TicketModel */

		self::$model = $model = $app->models['users.noncelogin'];
		$model->truncate();

		# route

		self::$route = $app->routes['api:nonce-login'];

		# create nonce login ticket

		$ticket = Ticket::from([

			'uid' => 1,
			'ip' => $app->request->ip

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
			$operation($request);

			$this->fail("Expected Failure");
		}
		catch (Failure $e)
		{
			$errors = $e->operation->response->errors;

			$this->assertNotNull($errors['token']);
			$this->assertNotNull($errors['email']);
			$this->assertNotNull($errors['password']);
			$this->assertNotNull($errors['password-verify']);
		}
		catch (\Exception $e)
		{
			$this->fail("Expected Failure");
		}

		#
		# dispatch
		#

		$response = $request();

		$this->assertFalse($response->status->is_successful);

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
			$operation($request);

			$this->fail("Expected Failure");
		}
		catch (Failure $e)
		{
			$errors = $e->operation->response->errors;

			$this->assertNotEmpty($errors['token']);
			$this->assertEmpty($errors['email']);
			$this->assertEmpty($errors['password']);
			$this->assertEmpty($errors['password-verify']);
		}
		catch (\Exception $e)
		{
			$this->fail("Expected Failure");
		}

		#
		# dispatch
		#

		$response = $request();

		$this->assertFalse($response->status->is_successful);

		$errors = $response->errors;

		$this->assertNotEmpty($errors['token']);
		$this->assertEmpty($errors['email']);
		$this->assertEmpty($errors['password']);
		$this->assertEmpty($errors['password-verify']);
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
			$operation($request);

			$this->fail("Expected Failure");
		}
		catch (Failure $e)
		{
			$errors = $e->operation->response->errors;

			$this->assertEmpty($errors['token']);
			$this->assertNotEmpty($errors['email']);
			$this->assertEmpty($errors['password']);
			$this->assertEmpty($errors['password-verify']);
		}
		catch (\Exception $e)
		{
			$this->fail("Expected Failure");
		}

		#
		# dispatch
		#

		$response = $request();

		$this->assertFalse($response->status->is_successful);

		$errors = $response->errors;

		$this->assertEmpty($errors['token']);
		$this->assertNotEmpty($errors['email']);
		$this->assertEmpty($errors['password']);
		$this->assertEmpty($errors['password-verify']);
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
			$operation($request);

			$this->fail("Expected Failure");
		}
		catch (Failure $e)
		{
			$errors = $e->operation->response->errors;

			$this->assertEmpty($errors['token']);
			$this->assertEmpty($errors['email']);
			$this->assertNotEmpty($errors['password']);
			$this->assertEmpty($errors['password-verify']);
		}
		catch (\Exception $e)
		{
			$this->fail("Expected Failure");
		}

		#
		# dispatch
		#

		$response = $request();

		$this->assertFalse($response->status->is_successful);

		$errors = $response->errors;

		$this->assertEmpty($errors['token']);
		$this->assertEmpty($errors['email']);
		$this->assertNotEmpty($errors['password']);
		$this->assertEmpty($errors['password-verify']);
	}

	public function test_request()
	{
		$app = app();

		$request = Request::from(self::$request_attributes);
		$response = $request();

		$this->assertTrue($response->status->is_successful);
		$this->assertNotEmpty($response['redirect_to']);
		$this->assertNotEmpty($app->user);
		$this->assertEquals(1, $app->user_id);

		// the ticket for the user must be destroyed.
		$ticket = self::$ticket;
		$ticket = $app->models['users.noncelogin']->filter_by_uid($ticket->uid)->one;
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
			$operation($request);

			$this->fail("Expected Failure");
		}
		catch (Failure $e)
		{
			$errors = $e->operation->response->errors;

			$this->assertNotEmpty($errors['token']);
			$this->assertEmpty($errors['email']);
			$this->assertEmpty($errors['password']);
			$this->assertEmpty($errors['password-verify']);
		}
		catch (\Exception $e)
		{
			$this->fail("Expected Failure");
		}

		#
		# dispatch
		#

		$response = $request();

		$this->assertFalse($response->status->is_successful);

		$errors = $response->errors;

		$this->assertNotEmpty($errors['token']);
		$this->assertEmpty($errors['email']);
		$this->assertEmpty($errors['password']);
		$this->assertEmpty($errors['password-verify']);
	}
}
