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

use ICanBoogie\Errors;
use ICanBoogie\HTTP\Request;

use Brickrouge\Alert;
use ICanBoogie\HTTP\RedirectResponse;

/**
 * @property-read Ticket $ticket
 */
class NonceLoginController extends \ICanBoogie\Routing\Controller
{
	use ValidateToken;

	protected $request;

	public function __invoke(Request $request)
	{
		$this->request = $request;

		$errors = new Errors;
		$this->validate($errors);

		if ($errors->count())
		{
			foreach ($errors as $message)
			{
				\ICanBoogie\log_error($message);
			}

			return new RedirectResponse(\ICanBoogie\Core::get()->routes['nonce-login-request']);
		}

		return new NonceLoginForm([

			NonceLoginForm::TICKET => $this->ticket

		]);
	}

	private $ticket;

	protected function get_ticket()
	{
		return $this->ticket;
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		$this->validate_token($this->request['token'], $errors, $this->ticket);

		return $errors;
	}
}
