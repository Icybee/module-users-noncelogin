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

/**
 * The "nonce-login" operation is used to login a user using a one time, time limited pass created
 * by the "nonce-request" operation.
 *
 * @property-read Ticket $ticket The nonce login ticket.
 */
class NonceLoginOperation extends \ICanBoogie\Operation
{
	private $ticket;

	static protected function t($format, array $args=array(), array $options=array())
	{
		return new FormattedString($format, $args, $options + array('scope' => 'users_noncelogin'));
	}

	protected function volatile_get_ticket()
	{
		return $this->ticket;
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		global $core;

		$request = $this->request;
		$token = $request['token'];

		if (!$token)
		{
			$errors['token'] = $this->t("The nonce login Token is required.");

			return false;
		}

		$this->ticket = $ticket = $core->models['users.noncelogin']->filter_by_token($token)->one;

		if (!$ticket)
		{
			$errors['token'] = $this->t("Unknown token.");

			return false;
		}

		if ($ticket->expire_at < DateTime::now())
		{
			$errors['expire_at'] = $this->t("This nonce login ticket has expired at :date.", array(':date' => $ticket->expire_at->local->as_db));

			return false;
		}

		if ($ticket->ip != $request->ip)
		{
			$errors['ip'] = $this->t("The IP address doesn't match the one of the initial request.");

			return false;
		}

		try
		{
			$ticket->user;
		}
		catch (RecordNotFound $e)
		{
			$errors['uid'] = $this->t("The user associated with this nonce login no longer exists.");

			return false;
		}

		return true;
	}

	protected function process()
	{
		$ticket = $this->ticket;
		$user = $ticket->user;

		$ticket->delete();

		$user->login();

		$this->response->location = $user->url('profile');
		$this->response->message = $this->t("You are now logged in, please enter your password.");

		return true;
	}
}