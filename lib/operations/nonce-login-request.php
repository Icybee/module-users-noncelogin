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

use ICanBoogie\DateTime;
use ICanBoogie\HTTP\Request;

/**
 * Provides a nonce login.
 *
 * @property-read \Icybee\Modules\Users\User $user The user for which a ticket should be created.
 * Alias for {@link $record}.
 * @property-read Ticket $ticket The ticket created by the operation.
 */
class NonceLoginRequestOperation extends \ICanBoogie\Operation
{
	protected function get_controls()
	{
		return [

			self::CONTROL_METHOD => Request::METHOD_POST

		] + parent::get_controls();
	}

	/**
	 * @todo-20131009: remove this when Operation is cleverer.
	 */
	public function __construct($request=null)
	{
		global $core;

		parent::__construct($request);

		$this->module = $core->modules['users.noncelogin'];
	}

	/**
	 * Returns the record assocaiated with the email address specified by the `email` param.
	 *
	 * @return \Icybee\Modules\Users\User|null
	 */
	protected function lazy_get_record()
	{
		global $core;

		$email = $this->request['email'];

		if (!$email)
		{
			return;
		}

		$model = $core->models['users'];
		$uid = $model->select('uid')->filter_by_email($email)->rc;

		if (!$uid)
		{
			return;
		}

		return $model[$uid];
	}

	/**
	 * Returns the user for which a ticket should be created.
	 *
	 * @return \Icybee\Modules\Users\User
	 */
	protected function get_user()
	{
		return $this->record;
	}

	private $ticket;

	/**
	 * Returns the ticket created by the operation.
	 *
	 * @return Ticket
	 */
	protected function get_ticket()
	{
		return $this->ticket;
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		global $core;

		$email = $this->request['email'];

		if (!$email)
		{
			$errors['email'] = $errors->format('The field %field is required!', array('%field' => 'Votre adresse E-Mail'));

			return false;
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$errors['email'] = $errors->format("Invalid email address: %email.", array('%email' => $email));

			return false;
		}

		$user = $this->record;

		if (!$user)
		{
			$errors['email'] = $errors->format("Unknown email address.");

			return false;
		}

		if ($user->language)
		{
			$core->locale = $user->language;
		}

		$expire_at = null;
		$ticket = $this->module->model->filter_by_uid($user->uid)->one;

		if ($ticket)
		{
			$expire_at = $ticket->expire_at;
		}

		if ($expire_at && (time() + Module::FRESH_PERIOD - $expire_at->timestamp < Module::COOLOFF_DELAY))
		{
			throw new TicketAlreadySent
			(
				$ticket, $errors->format("nonce_login_request.operation.already_sent", [

					':time' => DateTime::from('@' . ($expire_at->timestamp - Module::FRESH_PERIOD + Module::COOLOFF_DELAY), 'utc')->local->format('H:i')

				]),

				403
			);
		}

		return true;
	}

	/**
	 * Creates a nonce login ticket.
	 *
	 * If a previous ticket for the user exists it will be deleted.
	 */
	protected function process()
	{
		global $core;

		$user = $this->record;
		$model = $this->module->model;

		# delete previous ticket (if any)

		$model->filter_by_uid($user->uid)->delete();

		# create new ticket

		$ticket = Ticket::from(array(

			'uid' => $user->uid,
			'token' => $model->generate_token(),
			'expire_at' => '+' . Module::FRESH_PERIOD . ' seconds',
			'ip' => $this->request->ip

		));

		$ticket->save();

		$this->ticket = $ticket;
		$this->response->message = $this->response->errors->format('success', [

			'%email' => $user->email

		], [

			'scope' => \ICanBoogie\normalize($user->constructor, '_') . '.nonce_login_request.operation'

		]);

		return true;
	}
}

/**
 * Exception thrown in attempt to request a ticket before the end of the cooldown period.
 */
class TicketAlreadySent extends \Exception
{
	use \ICanBoogie\GetterTrait;

	private $ticket;

	protected function get_ticket()
	{
		return $this->ticket;
	}

	public function __construct(Ticket $ticket, $message, $code=500, \Exception $previous=null)
	{
		$this->ticket = $ticket;

		parent::__construct($message, $code, $previous);
	}
}