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
use ICanBoogie\ActiveRecord\RecordNotFound;
use ICanBoogie\DateTime;
use ICanBoogie\ErrorCollection;

/**
 * A trait to validate nonce tokens and retrieve their associated ticket.
 */
trait ValidateToken
{
	/**
	 * @param string $token
	 * @param ErrorCollection $errors
	 * @param Ticket|null $ticket
	 *
	 * @return bool
	 */
	protected function validate_token($token, ErrorCollection $errors, Ticket &$ticket = null)
	{
		if (!$token)
		{
			$errors->add('token', "The param %param is required.", [ 'param' => 'token' ]);

			return false;
		}

		$ticket = ActiveRecord\get_model('users.noncelogin')->filter_by_token($token)->one;

		if (!$ticket)
		{
			$errors->add('token', "Invalid token, the ticket might have expired or already been used.");

			return false;
		}

		if ($ticket->expire_at < DateTime::now())
		{
			$errors->add('token', "This ticket has expired at :date.", [

				':date' => $ticket->expire_at->local

			]);

			return false;
		}

		try
		{
			$ticket->user;
		}
		catch (RecordNotFound $e)
		{
			$errors->add('uid', "The user associated with this ticket no longer exists.");

			return false;
		}

		return true;
	}
}
