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

use ICanBoogie\ActiveRecord;
use ICanBoogie\Errors;
use ICanBoogie\HTTP\Request;

/**
 * The "nonce-login" operation is used to login a user using a one time, time limited pass created
 * by the "nonce-request" operation.
 *
 * @property-read Ticket $ticket The nonce ticket.
 * @property-read \Icybee\Modules\Users\User $user The user associated with the ticket.
 * @property-read string $email The email of the user.
 * @property-read string $password The new password of the user.
 */
class NonceLoginOperation extends \ICanBoogie\Operation
{
	use ValidateToken;

	private $ticket;

	protected function get_ticket()
	{
		return $this->ticket;
	}

	private $email;

	protected function get_email()
	{
		return $email;
	}

	private $password;

	protected function get_password()
	{
		return $password;
	}

	protected function get_user()
	{
		return $this->ticket ? $this->ticket->user : null;
	}

	protected function get_controls()
	{
		return [

			self::CONTROL_FORM => true

		] + parent::get_controls();
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		$request = $this->request;

		# token

		$token = $request['token'];

		$this->validate_token($token, $errors, $this->ticket);

		# email

		$this->email = $email = $request['email'];

		$uid = ActiveRecord\get_model('users')
		->select('uid')
		->filter_by_email($email)
		->rc;

		if (!$uid || ($this->ticket && $uid != $this->ticket->user->uid))
		{
			$errors['email'] = $errors->format("Invalid email address %email", [

				'email' => $email

			]);
		}

		# password

		$this->password = $password = $request['password'];

		if ($password != $request['password-verify'])
		{
			$errors['password'] = $errors->format("Passwords don't match");
		}

		return $errors;
	}

	protected function process()
	{
		$ticket = $this->ticket;
		$user = $ticket->user;
		$user->password = $this->password;
		$user->save();

		$login_request = Request::from([

			'is_post' => true,
			'uri' => $this->app->routes['api:login'],
			'request_params' => [

				'username' => $this->email,
				'email' => $this->email,
				'password' => $this->password

			]

		]);

		/* @var $response \ICanBoogie\Operation\Response */

		$response = $login_request();

		if (!$response->is_successful)
		{
			throw new \Exception("Unable to login");
		}

		$ticket->delete();

		$this->response->location = $response->location ?: '/';
		$this->response->message = $this->format("Your password has been updated and you are now logged in.");

		return true;
	}
}
