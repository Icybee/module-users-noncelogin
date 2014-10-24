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
use ICanBoogie\Errors;

/**
 * A trait to validate nonce tokens and retrieve their associated ticket.
 */
trait ValidateToken
{
	protected function validate_token($token, Errors $errors, Ticket &$ticket=null)
	{
		if (!$token)
		{
			$errors['token'] = $errors->format("The param %param is required.", [ 'param' => 'token' ]);

			return false;
		}

		$ticket = ActiveRecord\get_model('users.noncelogin')->filter_by_token($token)->one;

		if (!$ticket)
		{
			$errors['token'] = $errors->format("Invalid token, the ticket might have expired or already been used.");

			return false;
		}

		if ($ticket->expire_at < DateTime::now())
		{
			$errors['token'] = $errors->format("This ticket has expired at :date.", [

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
			$errors['uid'] = $errors->format("The user associated with this ticket no longer exists.");

			return false;
		}

		return true;
	}
}