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
use ICanBoogie\HTTP\Request;
use ICanBoogie\Routing\Controller;

use Brickrouge\Alert;

class NonceLoginController extends Controller
{
	use ValidateToken;

	protected $request;

	public function __invoke(Request $request)
	{
		$this->request = $request;

		$errors = new Errors;
		$this->validate($errors);

		var_dump(\ICanBoogie\Debug::fetch_messages(\ICanBoogie\LogLevel::ERROR));

		if ($errors->count())
		{
			return new Alert($errors);
		}

		$form = new NonceLoginForm([

			NonceLoginForm::TICKET => $this->ticket

		]);

		return $form;
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