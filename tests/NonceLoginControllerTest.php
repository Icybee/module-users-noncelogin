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

class NonceLoginControllerTest extends \PHPUnit_Framework_TestCase
{
	public function test_valid_token()
	{
		global $core;

		$ticket = Ticket::from([

			'uid' => 1,
			'ip' => $core->request->ip

		]);

		$ticket->save();

		$route = $core->routes['nonce-login'];

		$request = Request::from([

			'uri' => $route->format($ticket),
			'is_get' => true

		]);

		$response = $request();

		$this->assertInstanceOf('ICanBoogie\HTTP\Response', $response);
		$this->assertInstanceOf(__NAMESPACE__ . '\NonceLoginForm', $response->body);

		$ticket->delete();
	}

	public function test_invalid_token()
	{
		global $core;

		$ticket = Ticket::from([

			'uid' => 1,
			'ip' => $core->request->ip

		]);

		$ticket->save();

		$route = $core->routes['nonce-login'];

		$request = Request::from([

			'uri' => $route->format([ 'token' => str_rot13($ticket->token) ]),
			'is_get' => true

		]);

		$response = $request();

		$this->assertInstanceOf('ICanBoogie\HTTP\RedirectResponse', $response);
		$this->assertEquals((string) $core->routes['nonce-login-request'], (string) $response->location);
	}
}