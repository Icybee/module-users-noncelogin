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

use ICanBoogie\ActiveRecord\RecordNotFound;
use ICanBoogie\DateTime;
use ICanBoogie\I18n\FormattedString;
use ICanBoogie\PermissionRequired;
use ICanBoogie\Errors;
use ICanBoogie\ActiveRecord;

/**
 * The "nonce-login" operation is used to login a user using a one time, time limited pass created
 * by the "nonce-request" operation.
 */
class NonceLoginOperation extends \ICanBoogie\Operation
{
	use ValidateToken;

	private $ticket;
	private $email;
	private $password;

	protected function get_ticket()
	{
		return $this->ticket;
	}

	protected function get_controls()
	{
		return [

			self::CONTROL_FORM => true

		] + parent::get_controls();
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		global $core;

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

		if (!$uid || $uid != $this->ticket->user->uid)
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
		global $core;

		throw new \Exception("disabled");

		$ticket = $this->ticket;
		$user = $ticket->user;

		$ticket->delete();

		$user->login();

		$this->response->location = $user->url('profile');
		$this->response->message = new FormattedString("You are now logged in, please enter your password.");

		return true;
	}
}

trait ValidateToken
{
	protected function validate_token($token, Errors $errors, Ticket &$ticket=null)
	{
		if (!$token)
		{
			$errors['token'] = $errors->format("The nonce login Token is required.");

			return false;
		}

		$ticket = ActiveRecord\get_model('users.noncelogin')->filter_by_token($token)->one;

		if (!$ticket)
		{
			$errors['token'] = $errors->format("Unknown token.");

			return false;
		}

		if ($ticket->expire_at < DateTime::now())
		{
			$errors['expire_at'] = $errors->format("This nonce login ticket has expired at :date.", [

				':date' => $ticket->expire_at->local->as_db

			]);

			return false;
		}

		try
		{
			$ticket->user;
		}
		catch (RecordNotFound $e)
		{
			$errors['uid'] = $errors->format("The user associated with this nonce login no longer exists.");

			return false;
		}

		return true;
	}
}