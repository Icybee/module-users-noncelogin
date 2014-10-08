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

class NonceLoginRequestTest extends \PHPUnit_Framework_TestCase
{
	public function test_invalid_email_address()
	{
		$request = Request::from([

			'request_params' => [ 'email' => 'invalid_email_address' ],
			'is_post' => true

		]);

		$operation = new NonceLoginRequestOperation;

		try
		{
			$response = $operation($request);

			$this->fail('The Failure exception should have been thrown.');
		}
		catch (\ICanBoogie\Operation\Failure $e)
		{
			$response = $e->operation->response;

			$this->assertNull($response->rc);
			$this->assertNull($response->message);
			$this->assertNotEmpty($response->errors['email']);
			$this->assertInstanceOf('ICanBoogie\I18n\FormattedString', $response->errors['email']);
			$this->assertTrue($response->is_client_error);
		}
	}

	public function test_request()
	{
		global $core;

		$mailer_options = null;

		$core->mail = function($options) use(&$mailer_options) {

			$mailer_options = $options;

		};

		$request = Request::from([

			'request_params' => [ 'email' => 'olivier.laviale@gmail.com' ],
			'is_post' => true

		]);

		$operation = new NonceLoginRequestOperation;
		$response = $operation($request);

		$this->assertInstanceOf('ICanBoogie\Operation\Response', $response);
		$this->assertTrue($response->rc);
		$this->assertInstanceOf('ICanBoogie\I18n\FormattedString', $response->message);
		$this->assertEquals('success', $response->message->format);

		// this proves that the event hook "on_nonce_login_request" was invoked
		$this->assertNotNull($mailer_options);
		$this->assertArrayHasKey('to', $mailer_options);
		$this->assertEquals($operation->user->email, $mailer_options['to']);
		$this->assertArrayHasKey('body', $mailer_options);
		$this->assertTrue(strpos($mailer_options['body'], $core->routes['api:nonce-login']->format($operation->ticket)->absolute_url) !== false);
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

			$this->fail('The Failure exception should have been thrown.');
		}
		catch (\Exception $e)
		{
			$this->assertInstanceOf('ICanBoogie\Operation\Failure', $e);

			$previous = $e->previous;
			$this->assertInstanceOf(__NAMESPACE__ . '\TicketAlreadySent', $previous);
			$this->assertInstanceOf(__NAMESPACE__ . '\Ticket', $previous->ticket);
		}
	}

	/**
	 * @depends test_request
	 */
	public function test_multiple_request_dispatch()
	{
		global $core;

		$request = Request::from([

			'uri' => $core->routes['api:nonce-login-request'],
			'request_params' => [ 'email' => 'olivier.laviale@gmail.com' ],
			'is_post' => true

		]);

		/* @var $response \ICanBoogie\Operation\Response */

		$response = $request();
		$this->assertFalse($response->is_successful);
		$this->assertEquals(403, $response->status);
		$this->assertStringStartsWith("A message has already been sent", $response->message);
	}
}