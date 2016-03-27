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

use ICanBoogie\Binding\Routing\ForwardUndefinedPropertiesToApplication;
use ICanBoogie\ErrorCollection;
use ICanBoogie\HTTP\Request;

use ICanBoogie\HTTP\RedirectResponse;
use ICanBoogie\Routing\Controller;

/**
 * @property-read Ticket $ticket
 */
class NonceLoginController extends Controller
{
	use ValidateToken;
	use ForwardUndefinedPropertiesToApplication;

	protected $request;

	public function action(Request $request)
	{
		$this->request = $request;

		$errors = new ErrorCollection;
		$this->validate($errors);

		if ($errors->count())
		{
			foreach ($errors as $message)
			{
				\ICanBoogie\log_error($message);
			}

			return new RedirectResponse($this->routes['nonce-login-request']);
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

	/**
	 * @inheritdoc
	 */
	protected function validate(ErrorCollection $errors)
	{
		$this->validate_token($this->request['token'], $errors, $this->ticket);

		return $errors;
	}
}
