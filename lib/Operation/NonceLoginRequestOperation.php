<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Users\NonceLogin\Operation;

use ICanBoogie\DateTime;
use ICanBoogie\ErrorCollection;
use ICanBoogie\HTTP\Request;
use ICanBoogie\Operation;

use Icybee\Modules\Users\NonceLogin\Module;
use Icybee\Modules\Users\NonceLogin\Ticket;
use Icybee\Modules\Users\NonceLogin\TicketAlreadySent;
use Icybee\Modules\Users\NonceLogin\TicketModel;
use Icybee\Modules\Users\User;

/**
 * Provides a nonce login.
 *
 * @property-read User $user The user for which a ticket should be created.
 * Alias for {@link $record}.
 * @property-read User $record
 * @property-read Ticket $ticket The ticket created by the operation.
 */
class NonceLoginRequestOperation extends Operation
{
	protected function get_controls()
	{
		return [

			self::CONTROL_METHOD => Request::METHOD_POST

		] + parent::get_controls();
	}

	/**
	 * @todo-20131009: remove this when Operation is cleverer.
	 *
	 * @inheritdoc
	 */
	public function __construct($request = null)
	{
		parent::__construct($request);

		$this->module = $this->app->modules['users.noncelogin'];
	}

	/**
	 * Returns the record associated with the email address specified by the `email` param.
	 *
	 * @return User|null
	 */
	protected function lazy_get_record()
	{
		$email = $this->request['email'];

		if (!$email)
		{
			return null;
		}

		$model = $this->app->models['users'];
		$uid = $model->select('uid')->filter_by_email($email)->rc;

		if (!$uid)
		{
			return null;
		}

		return $model[$uid];
	}

	/**
	 * Returns the user for which a ticket should be created.
	 *
	 * @return User
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

	/**
	 * @inheritdoc
	 */
	protected function validate(ErrorCollection $errors)
	{
		$email = $this->request['email'];

		if (!$email)
		{
			$errors->add('email', "The field %field is required!", [ '%field' => 'Votre adresse E-Mail' ]);

			return false;
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL))
		{
			$errors->add('email', "Invalid email address: %email.", ['%email' => $email ]);

			return false;
		}

		$user = $this->record;

		if (!$user)
		{
			$errors->add('email', "Unknown email address.");

			return false;
		}

		if ($user->language)
		{
			$this->app->locale = $user->language;
		}

		/* @var $expire_at \ICanBoogie\DateTime */

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
				$ticket, $this->format("nonce_login_request.operation.already_sent", [

					':time' => DateTime::from('@' . ($expire_at->timestamp - Module::FRESH_PERIOD + Module::COOLOFF_DELAY), 'utc')->local->format('H:i')

				]),

				403
			);
		}

		return $errors;
	}

	/**
	 * Creates a nonce login ticket.
	 *
	 * If a previous ticket for the user exists it will be deleted.
	 */
	protected function process()
	{
		/* @var $model TicketModel */

		$user = $this->record;
		$model = $this->module->model;

		# delete previous ticket (if any)

		$model->filter_by_uid($user->uid)->delete();

		# create new ticket

		$ticket = Ticket::from([

			'uid' => $user->uid,
			'token' => $model->generate_token(),
			'expire_at' => '+' . Module::FRESH_PERIOD . ' seconds',
			'ip' => $this->request->ip

		]);

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
