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
		$request = Request::from(array('request_params' => array('email' => 'invalid_email_address')));
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

		$core->mailer = function($options) use(&$mailer_options) {

			$mailer_options = $options;

		};

		$request = Request::from(array('request_params' => array('email' => 'olivier.laviale@gmail.com')));
		$operation = new NonceLoginRequestOperation;
		$response = $operation($request);

		$this->assertInstanceOf('ICanBoogie\Operation\Response', $response);
		$this->assertTrue($response->rc);
		$this->assertInstanceOf('ICanBoogie\I18n\FormattedString', $response->message);
		$this->assertEquals('success', $response->message->format);

		// this proves that the event hook "on_nonce_login_request" was invoked
		$this->assertNotNull($mailer_options);
		$this->assertArrayHasKey('destination', $mailer_options);
		$this->assertEquals($operation->user->email, $mailer_options['destination']);
		$this->assertArrayHasKey('message', $mailer_options);
		$this->assertTrue(strpos($mailer_options['message'], $core->site->url . $core->routes['api:nonce-login']->format($operation->ticket)) !== false);
	}

	/**
	 * A new nonce request can only be granted if the previous one has expired.
	 *
	 * @depends test_request
	 * @expectedException ICanBoogie\PermissionRequired
	 */
	public function test_multiple_request()
	{
		$request = Request::from(array('request_params' => array('email' => 'olivier.laviale@gmail.com')));
		$operation = new NonceLoginRequestOperation;
		$response = $operation($request);
	}
}