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
use ICanBoogie\Error;
use ICanBoogie\HTTP\Request;
use ICanBoogie\Operation;
use ICanBoogie\Operation\Failure;

use Icybee\Modules\Users\NonceLogin\Operation\NonceLoginRequestOperation;

class NonceLoginRequestTest extends \PHPUnit_Framework_TestCase
{
	static public function setupBeforeClass()
	{
		app()->models['users.noncelogin']->truncate();
	}

	public function test_invalid_email_address()
	{
		$request = Request::from([

			'request_params' => [ 'email' => 'invalid_email_address' ],
			'is_post' => true

		]);

		$operation = new NonceLoginRequestOperation;

		try
		{
			$operation($request);

			$this->fail("Expected Failure");
		}
		catch (Failure $e)
		{
			$response = $e->operation->response;

			$this->assertNull($response->rc);
			$this->assertNull($response->message);
			$this->assertNotEmpty($response->errors['email']);
			$this->assertContainsOnlyInstancesOf(Error::class, $response->errors['email']);
			$this->assertTrue($response->status->is_client_error);

			return;
		}

		$this->fail("Expected Failure");
	}

	public function test_request()
	{
		global $mailer_options;

		$mailer_options = null;

		$request = Request::from([

			'request_params' => [ 'email' => 'olivier.laviale@gmail.com' ],
			'is_post' => true

		]);

		$operation = new NonceLoginRequestOperation;
		$response = $operation($request);

		$this->assertInstanceOf(Operation\Response::class, $response);
		$this->assertTrue($response->rc);
		$this->assertInstanceOf('ICanBoogie\I18n\FormattedString', $response->message);
		$this->assertEquals('success', $response->message->format);

		// this proves that the event hook "on_nonce_login_request" was invoked
		$this->assertNotNull($mailer_options);
		$this->assertArrayHasKey('to', $mailer_options);
		$this->assertEquals($operation->user->email, $mailer_options['to']);
		$this->assertArrayHasKey('body', $mailer_options);
		$this->assertTrue(strpos($mailer_options['body'], app()->routes['nonce-login']->format($operation->ticket)->absolute_url) !== false);
	}

	/**
	 * A new nonce request can only be granted if the previous one has expired.
	 *
	 * @depends test_request
	 */
	public function test_multiple_request()
	{
		$request = Request::from([

			'request_params' => [ 'email' => 'olivier.laviale@gmail.com' ],
			'is_post' => true

		]);

		$operation = new NonceLoginRequestOperation;

		try
		{
			$operation($request);

			$this->fail("Expected Failure");
		}
		catch (Failure $e)
		{
			/* @var $previous TicketAlreadySent */

			$previous = $e->previous;
			$this->assertInstanceOf(TicketAlreadySent::class, $previous);
			$this->assertInstanceOf(Ticket::class, $previous->ticket);

			return;
		}

		$this->fail("Expected Failure");
	}

	/**
	 * @depends test_request
	 */
	public function test_multiple_request_dispatch()
	{
		$request = Request::from([

			'uri' => app()->routes['api:nonce-login-request'],
			'request_params' => [ 'email' => 'olivier.laviale@gmail.com' ],
			'is_post' => true,
			'is_xhr' => true

		]);

		/* @var $response \ICanBoogie\Operation\Response */

		$response = $request();
		$this->assertFalse($response->status->is_successful);
		$this->assertEquals(403, $response->status->code);
		$this->assertStringStartsWith("A message has already been sent", $response->message);
	}
}