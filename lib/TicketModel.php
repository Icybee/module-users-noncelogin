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

class TicketModel extends \ICanBoogie\ActiveRecord\Model
{
	/**
	 * Generates a unique 40 characters long token.
	 *
	 * @return string
	 */
	public function generate_token()
	{
		for (;;)
		{
			if (function_exists('openssl_random_pseudo_bytes'))
			{
				$token = sha1(openssl_random_pseudo_bytes(256));
			}
			else
			{
				$token = \ICanBoogie\generate_token(40, \ICanBoogie\TOKEN_ALPHA . \ICanBoogie\TOKEN_NUMERIC);
			}

			if ($this->filter_by_token($token)->one)
			{
				continue;
			}

			return $token;
		}
	}
}
